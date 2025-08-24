<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Location;
use App\Models\EventType;
use App\Models\EventFields;
use App\Models\Event;
use App\Models\EntityType;
use App\Models\Entity;
use App\Models\Media;

class InitialBuild extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();
        EntityType::factory()->count(10)->create();
        Location::factory()->count(10)->create();
        EventType::factory()->count(10)->create();
        EventFields::factory()->count(10)->create();
        Event::factory()->count(10)->create();
        Entity::factory()->count(10)->create();
        Media::factory()->count(10)->create();
    }
}
