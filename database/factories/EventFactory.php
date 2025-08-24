<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Entity;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fake()->slug(),
            'image' => fake()->imageUrl(),
            'description' => fake()->text(),
            'event_type_id' => EventType::factory()->create()->id,
            'entity_id' => Entity::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'date' => fake()->dateTimeBetween('-1 week', '+1 week'),
        ];
    }
}
