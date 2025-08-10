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
            'mediable_id' => User::factory(),
            'mediable_type' => User::class,
            'file_name' => $fileName,
            'file_path' => 'media/users/1/'.Str::slug($fileName).'_'.time().'.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'file_size' => $this->faker->numberBetween(1000, 5000000),
            'collection_name' => $this->faker->randomElement(['avatars', 'documents', 'photos']),
            'custom_properties' => ['alt' => $this->faker->sentence()],
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
