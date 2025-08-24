<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->word().'.jpg';

        return [
            'name' => $fileName,
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'collection_name' => $this->faker->randomElement(['avatars', 'documents', 'photos']),
            'custom_properties' => ['alt' => $this->faker->sentence()],
            'model_type' => "App\Models\User",  
            'model_id' => User::factory()->create()->id,
            'size' => 1024,
            'manipulations' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'uuid' => $this->faker->uuid(),
            'conversions_disk' => null,
            'order_column' => null,
        ];
    }

    /**
     * Configure the model factory to use a specific mediable model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forModel($model)
    {
        $modelClass = get_class($model);
        $modelType = class_basename($modelClass);
        $path = 'media/'.Str::plural(Str::snake($modelType)).'/'.$model->id;

        return $this->state(function (array $attributes) use ($model, $modelClass, $path) {
            return [
                'mediable_id' => $model->id,
                'mediable_type' => $modelClass,
                'file_path' => $path.'/'.Str::slug($attributes['file_name']).'_'.time().'.jpg',
            ];
        });
    }
}
