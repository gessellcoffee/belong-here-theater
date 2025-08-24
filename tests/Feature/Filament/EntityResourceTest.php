<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\EntityResource;
use App\Models\Entity;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EntityResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function can_render_index_page()
    {
        Livewire::test(EntityResource\Pages\ListEntities::class)
            ->assertSuccessful();
    }

    /** @test */
    public function can_render_create_page()
    {
        Livewire::test(EntityResource\Pages\CreateEntity::class)
            ->assertSuccessful();
    }

    /** @test */
    public function can_create_entity()
    {
        $user = User::factory()->create();
        $location = Location::create([
            'name' => 'Test Location',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'Test Country',
        ]);

        Livewire::test(EntityResource\Pages\CreateEntity::class)
            ->fillForm([
                'name' => 'Test Entity',
                'user_id' => $user->id,
                'location_id' => $location->id,
                'website' => 'example.com',
                'phone' => '123-456-7890',
                'email' => 'info@example.com',
                'description' => 'Test entity description',
                'vision' => 'Test vision',
                'mission' => 'Test mission',
                'values' => 'Test values',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('entities', [
            'name' => 'Test Entity',
            'user_id' => $user->id,
            'location_id' => $location->id,
            'website' => 'example.com',
            'phone' => '123-456-7890',
            'email' => 'info@example.com',
            'description' => 'Test entity description',
            'vision' => 'Test vision',
            'mission' => 'Test mission',
            'values' => 'Test values',
        ]);
    }

    /** @test */
    public function can_render_edit_page()
    {
        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => User::factory()->create()->id,
            'location_id' => Location::create(['name' => 'Test Location'])->id,
        ]);

        Livewire::test(EntityResource\Pages\EditEntity::class, [
            'record' => $entity->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function can_update_entity()
    {
        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => User::factory()->create()->id,
            'location_id' => Location::create(['name' => 'Test Location'])->id,
        ]);

        $newUser = User::factory()->create();
        $newLocation = Location::create(['name' => 'New Location']);

        Livewire::test(EntityResource\Pages\EditEntity::class, [
            'record' => $entity->id,
        ])
            ->fillForm([
                'name' => 'Updated Entity',
                'user_id' => $newUser->id,
                'location_id' => $newLocation->id,
                'website' => 'updated.com',
                'phone' => '987-654-3210',
                'email' => 'updated@example.com',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'name' => 'Updated Entity',
            'user_id' => $newUser->id,
            'location_id' => $newLocation->id,
            'website' => 'updated.com',
            'phone' => '987-654-3210',
            'email' => 'updated@example.com',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function can_render_view_page()
    {
        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => User::factory()->create()->id,
            'location_id' => Location::create(['name' => 'Test Location'])->id,
        ]);

        Livewire::test(EntityResource\Pages\ViewEntity::class, [
            'record' => $entity->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function can_archive_entity()
    {
        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => User::factory()->create()->id,
            'location_id' => Location::create(['name' => 'Test Location'])->id,
        ]);

        Livewire::test(EntityResource\Pages\EditEntity::class, [
            'record' => $entity->id,
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('entities', [
            'id' => $entity->id,
        ]);
    }

    /** @test */
    public function can_restore_entity()
    {
        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => User::factory()->create()->id,
            'location_id' => Location::create(['name' => 'Test Location'])->id,
        ]);
        $entity->delete();

        Livewire::test(EntityResource\Pages\EditEntity::class, [
            'record' => $entity->id,
        ])
            ->callAction('restore');

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function can_force_delete_entity()
    {
        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => User::factory()->create()->id,
            'location_id' => Location::create(['name' => 'Test Location'])->id,
        ]);
        $entity->delete();

        Livewire::test(EntityResource\Pages\EditEntity::class, [
            'record' => $entity->id,
        ])
            ->callAction('forceDelete');

        $this->assertDatabaseMissing('entities', [
            'id' => $entity->id,
        ]);
    }

    /** @test */
    public function can_create_entity_with_optional_location()
    {
        $user = User::factory()->create();

        Livewire::test(EntityResource\Pages\CreateEntity::class)
            ->fillForm([
                'name' => 'No Location Entity',
                'user_id' => $user->id,
                'website' => 'nolocation.com',
                'phone' => '123-456-7890',
                'email' => 'info@nolocation.com',
                'description' => 'Entity without location',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('entities', [
            'name' => 'No Location Entity',
            'user_id' => $user->id,
            'website' => 'nolocation.com',
        ]);
    }
}
