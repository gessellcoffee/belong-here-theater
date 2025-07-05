# Media Upload System

This document describes the media upload system implemented for the Theater Website application. The system allows for uploading media files (images, documents, etc.) to Users, Companies, and Locations using a unified approach.

## Architecture

The media upload system uses a polymorphic relationship to associate media files with different models in the application. This approach allows for a single media table to handle uploads for multiple entity types, making the system more scalable and maintainable.

### Key Components

1. **Media Model**: Central model that stores all media information
2. **HasMedia Trait**: Reusable trait that can be added to any model that needs media capabilities
3. **MediaService**: Service class that handles media operations
4. **MediaController**: API controller for media operations

## Database Structure

The `media` table has the following structure:

```
- id: Primary key
- mediable_id: ID of the related model
- mediable_type: Class name of the related model
- file_name: Original file name
- file_path: Path where the file is stored
- mime_type: MIME type of the file
- disk: Storage disk (default: 'public')
- file_size: Size of the file in bytes
- collection_name: Optional grouping for media (e.g., 'avatars', 'documents')
- custom_properties: JSON field for additional metadata
- created_at, updated_at, deleted_at: Timestamps with soft delete
```

## Usage

### Adding the HasMedia Trait

To make a model capable of having media, add the `HasMedia` trait:

```php
use App\Traits\HasMedia;

class YourModel extends Model
{
    use HasMedia;
    
    // Rest of your model code
}
```

### Uploading Media

#### Using the Model Methods

```php
// Upload media to a user
$user = User::find(1);
$media = $user->addMedia($request->file('avatar'), 'avatars');

// Upload media to a company
$company = Company::find(1);
$media = $company->addMedia($request->file('logo'), 'logos');

// Upload media to a location
$location = Locations::find(1);
$media = $location->addMedia($request->file('photo'), 'photos');
```

#### Using the MediaService

```php
$mediaService = app(App\Services\MediaService::class);

// Upload media
$media = $mediaService->uploadMedia($model, $request->file('file'), 'collection_name');

// Delete media
$mediaService->deleteMedia($media);

// Clear a collection
$mediaService->clearMediaCollection($model, 'collection_name');
```

### Retrieving Media

```php
// Get all media for a model
$allMedia = $user->media;

// Get media from a specific collection
$avatars = $user->getMedia('avatars');

// Access the URL of a media item
$mediaUrl = $media->url;
```

## API Endpoints

The system provides the following API endpoints:

- `POST /api/media/upload`: Upload a new media file
  - Parameters:
    - `file`: The file to upload
    - `model_type`: Type of model ('user', 'company', 'location')
    - `model_id`: ID of the model
    - `collection_name`: (Optional) Collection name

- `GET /api/media`: Get media for a model
  - Parameters:
    - `model_type`: Type of model ('user', 'company', 'location')
    - `model_id`: ID of the model
    - `collection_name`: (Optional) Filter by collection name

- `DELETE /api/media/{media}`: Delete a media item

## Security

The media system implements permission checks to ensure users can only:
- Upload media to their own profile
- Upload media to companies they are associated with
- Upload media to locations they have access to through their companies
- Delete media they have permission to manage

## Testing

The system includes comprehensive tests:
- Unit tests for the Media model and MediaService
- Feature tests for the MediaController API endpoints
