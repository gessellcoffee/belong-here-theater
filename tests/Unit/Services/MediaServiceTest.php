<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Location;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $mediaService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->mediaService = new MediaService;
    }

    public function test_can_upload_media_to_user()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $media = $this->mediaService->uploadMedia($user, $file, 'avatars');

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('avatars', $media->collection_name);
        $this->assertEquals($user->id, $media->mediable_id);
        $this->assertEquals(User::class, $media->mediable_type);
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_can_upload_media_to_company()
    {
        $company = Company::factory()->create();
        $file = UploadedFile::fake()->image('logo.png');

        $media = $this->mediaService->uploadMedia($company, $file, 'logos');

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('logos', $media->collection_name);
        $this->assertEquals($company->id, $media->mediable_id);
        $this->assertEquals(Company::class, $media->mediable_type);
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_can_upload_media_to_location()
    {
        $location = Location::factory()->create();
        $file = UploadedFile::fake()->image('location.jpg');

        $media = $this->mediaService->uploadMedia($location, $file, 'photos');

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('photos', $media->collection_name);
        $this->assertEquals($location->id, $media->mediable_id);
        $this->assertEquals(Location::class, $media->mediable_type);
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_can_delete_media()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        $media = $this->mediaService->uploadMedia($user, $file);

        $filePath = $media->file_path;
        $result = $this->mediaService->deleteMedia($media);

        $this->assertTrue($result);
        $this->assertSoftDeleted('media', ['id' => $media->id]);
        // File should still exist because it's soft deleted
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_can_clear_media_collection()
    {
        $user = User::factory()->create();
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');

        $media1 = $this->mediaService->uploadMedia($user, $file1, 'avatars');
        $media2 = $this->mediaService->uploadMedia($user, $file2, 'avatars');

        $this->mediaService->clearMediaCollection($user, 'avatars');

        $this->assertSoftDeleted('media', ['id' => $media1->id]);
        $this->assertSoftDeleted('media', ['id' => $media2->id]);
    }

    public function test_throws_exception_for_invalid_model()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Model does not use HasMedia trait');

        $invalidModel = new class extends \Illuminate\Database\Eloquent\Model {};
        $file = UploadedFile::fake()->image('test.jpg');

        $this->mediaService->uploadMedia($invalidModel, $file);
    }
}
