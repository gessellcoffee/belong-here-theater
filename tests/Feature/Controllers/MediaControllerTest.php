<?php

namespace Tests\Feature\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_can_upload_media_to_own_profile()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/media/upload', [
                'file' => $file,
                'model_type' => 'user',
                'model_id' => $user->id,
                'collection_name' => 'avatars',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'media' => [
                    'id',
                    'file_name',
                    'url',
                    'mime_type',
                    'collection_name',
                ],
            ]);

        $this->assertDatabaseHas('media', [
            'mediable_id' => $user->id,
            'mediable_type' => User::class,
            'collection_name' => 'avatars',
        ]);
    }

    public function test_user_cannot_upload_media_to_another_user_profile()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user1)
            ->postJson('/api/media/upload', [
                'file' => $file,
                'model_type' => 'user',
                'model_id' => $user2->id,
                'collection_name' => 'avatars',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('media', [
            'mediable_id' => $user2->id,
            'mediable_type' => User::class,
        ]);
    }

    public function test_user_can_upload_media_to_own_company()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user->id);

        $file = UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($user)
            ->postJson('/api/media/upload', [
                'file' => $file,
                'model_type' => 'company',
                'model_id' => $company->id,
                'collection_name' => 'logos',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('media', [
            'mediable_id' => $company->id,
            'mediable_type' => Company::class,
            'collection_name' => 'logos',
        ]);
    }

    public function test_user_can_upload_media_to_location()
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();
        $company = Company::factory()->create(['location_id' => $location->id]);
        $company->users()->attach($user->id);

        $file = UploadedFile::fake()->image('location.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/media/upload', [
                'file' => $file,
                'model_type' => 'location',
                'model_id' => $location->id,
                'collection_name' => 'photos',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('media', [
            'mediable_id' => $location->id,
            'mediable_type' => Location::class,
            'collection_name' => 'photos',
        ]);
    }

    public function test_user_can_get_media_for_model()
    {
        $user = User::factory()->create();
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        $media1 = $user->addMedia($file1, 'avatars');
        $media2 = $user->addMedia($file2, 'avatars');

        $response = $this->actingAs($user)
            ->getJson('/api/media?model_type=user&model_id='.$user->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'media')
            ->assertJsonStructure([
                'media' => [
                    '*' => [
                        'id',
                        'file_name',
                        'url',
                        'mime_type',
                        'collection_name',
                        'created_at',
                    ],
                ],
            ]);
    }

    public function test_user_can_filter_media_by_collection()
    {
        $user = User::factory()->create();
        $file1 = UploadedFile::fake()->image('avatar.jpg');
        $file2 = UploadedFile::fake()->image('document.pdf');

        $user->addMedia($file1, 'avatars');
        $user->addMedia($file2, 'documents');

        $response = $this->actingAs($user)
            ->getJson('/api/media?model_type=user&model_id='.$user->id.'&collection_name=avatars');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'media');
    }

    public function test_user_can_delete_own_media()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        $media = $user->addMedia($file, 'avatars');

        $response = $this->actingAs($user)
            ->deleteJson('/api/media/'.$media->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    public function test_user_cannot_delete_another_users_media()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        $media = $user2->addMedia($file, 'avatars');

        $response = $this->actingAs($user1)
            ->deleteJson('/api/media/'.$media->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('media', ['id' => $media->id, 'deleted_at' => null]);
    }

    public function test_validation_errors_on_upload()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/media/upload', [
                'model_type' => 'user',
                'model_id' => $user->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_validation_errors_on_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/media');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['model_type', 'model_id']);
    }
}
