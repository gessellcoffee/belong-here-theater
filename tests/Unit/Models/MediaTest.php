<?php

namespace Tests\Unit\Models;

use App\Models\Media;
use App\Models\User;
use App\Models\Company;
use App\Models\Locations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_media_belongs_to_user()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $media = $user->addMedia($file, 'avatars');
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($user->id, $media->mediable_id);
        $this->assertEquals(User::class, $media->mediable_type);
        $this->assertInstanceOf(User::class, $media->mediable);
    }
    
    public function test_media_belongs_to_company()
    {
        $company = Company::factory()->create();
        $file = UploadedFile::fake()->image('logo.png');
        
        $media = $company->addMedia($file, 'logos');
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($company->id, $media->mediable_id);
        $this->assertEquals(Company::class, $media->mediable_type);
        $this->assertInstanceOf(Company::class, $media->mediable);
    }
    
    public function test_media_belongs_to_location()
    {
        $location = Locations::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');
        
        $media = $location->addMedia($file, 'photos');
        
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($location->id, $media->mediable_id);
        $this->assertEquals(Locations::class, $media->mediable_type);
        $this->assertInstanceOf(Locations::class, $media->mediable);
    }
    
    public function test_get_url_attribute()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $media = $user->addMedia($file);
        
        $expectedUrl = Storage::disk('public')->url($media->file_path);
        $this->assertEquals($expectedUrl, $media->url);
    }
    
    public function test_get_media_by_collection()
    {
        $user = User::factory()->create();
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $file3 = UploadedFile::fake()->image('document.pdf');
        
        $user->addMedia($file1, 'avatars');
        $user->addMedia($file2, 'avatars');
        $user->addMedia($file3, 'documents');
        
        $avatars = $user->getMedia('avatars');
        $documents = $user->getMedia('documents');
        $allMedia = $user->getMedia();
        
        $this->assertCount(2, $avatars);
        $this->assertCount(1, $documents);
        $this->assertCount(3, $allMedia);
    }
    
    public function test_delete_media_soft_deletes()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $media = $user->addMedia($file);
        $filePath = $media->file_path;
        
        $media->delete();
        
        $this->assertSoftDeleted('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($filePath);
    }
}
