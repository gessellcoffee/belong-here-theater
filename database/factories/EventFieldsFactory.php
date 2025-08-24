<?php

namespace Database\Factories;

use App\Models\EventFields;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventFields>
 */
class EventFieldsFactory extends Factory
{
    protected $model = EventFields::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'type' => fake()->randomElement(['text', 'number', 'date', 'time', 'datetime', 'select', 'checkbox', 'radio', 'file', 'image', 'video', 'audio', 'url', 'email', 'password', 'textarea', 'rich_text', 'user', 'location', 'company', 'event', 'event_type', 'event_field_value']),
            'event_type_id' => EventType::factory()->create()->id,
            'label' => fake()->word(),

        ];
    }
}
