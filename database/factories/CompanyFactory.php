<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'logo' => null,
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'extension' => $this->faker->randomNumber(4),
            'email' => $this->faker->companyEmail(),
            'vision' => $this->faker->paragraph(),
            'mission' => $this->faker->paragraph(),
            'values' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'locations_id' => Location::factory(),
        ];
    }
}
