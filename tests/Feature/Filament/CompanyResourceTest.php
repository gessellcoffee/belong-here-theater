<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use App\Models\Locations;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyResourceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function can_render_index_page()
    {
        Livewire::test(CompanyResource\Pages\ListCompanies::class)
            ->assertSuccessful();
    }

    /** @test */
    public function can_render_create_page()
    {
        Livewire::test(CompanyResource\Pages\CreateCompany::class)
            ->assertSuccessful();
    }

    /** @test */
    public function can_create_company()
    {
        $user = User::factory()->create();
        $location = Locations::create([
            'name' => 'Test Location',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'Test Country',
        ]);

        Livewire::test(CompanyResource\Pages\CreateCompany::class)
            ->fillForm([
                'name' => 'Test Company',
                'user_id' => $user->id,
                'location_id' => $location->id,
                'website' => 'example.com',
                'phone' => '123-456-7890',
                'email' => 'info@example.com',
                'description' => 'Test company description',
                'vision' => 'Test vision',
                'mission' => 'Test mission',
                'values' => 'Test values',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'user_id' => $user->id,
            'location_id' => $location->id,
            'website' => 'example.com',
            'phone' => '123-456-7890',
            'email' => 'info@example.com',
            'description' => 'Test company description',
            'vision' => 'Test vision',
            'mission' => 'Test mission',
            'values' => 'Test values',
        ]);
    }

    /** @test */
    public function can_render_edit_page()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'user_id' => User::factory()->create()->id,
            'location_id' => Locations::create(['name' => 'Test Location'])->id,
        ]);

        Livewire::test(CompanyResource\Pages\EditCompany::class, [
            'record' => $company->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function can_update_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'user_id' => User::factory()->create()->id,
            'location_id' => Locations::create(['name' => 'Test Location'])->id,
        ]);

        $newUser = User::factory()->create();
        $newLocation = Location::create(['name' => 'New Location']);

        Livewire::test(CompanyResource\Pages\EditCompany::class, [
            'record' => $company->id,
        ])
            ->fillForm([
                'name' => 'Updated Company',
                'user_id' => $newUser->id,
                'location_id' => $newLocation->id,
                'website' => 'updated.com',
                'phone' => '987-654-3210',
                'email' => 'updated@example.com',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Company',
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
        $company = Company::create([
            'name' => 'Test Company',
            'user_id' => User::factory()->create()->id,
            'location_id' => Locations::create(['name' => 'Test Location'])->id,
        ]);

        Livewire::test(CompanyResource\Pages\ViewCompany::class, [
            'record' => $company->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function can_archive_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'user_id' => User::factory()->create()->id,
            'location_id' => Locations::create(['name' => 'Test Location'])->id,
        ]);

        Livewire::test(CompanyResource\Pages\EditCompany::class, [
            'record' => $company->id,
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('companies', [
            'id' => $company->id,
        ]);
    }

    /** @test */
    public function can_restore_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'user_id' => User::factory()->create()->id,
            'location_id' => Locations::create(['name' => 'Test Location'])->id,
        ]);
        $company->delete();

        Livewire::test(CompanyResource\Pages\EditCompany::class, [
            'record' => $company->id,
        ])
            ->callAction('restore');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function can_force_delete_company()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'user_id' => User::factory()->create()->id,
            'location_id' => Locations::create(['name' => 'Test Location'])->id,
        ]);
        $company->delete();

        Livewire::test(CompanyResource\Pages\EditCompany::class, [
            'record' => $company->id,
        ])
            ->callAction('forceDelete');

        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
        ]);
    }

    /** @test */
    public function can_create_company_with_optional_location()
    {
        $user = User::factory()->create();

        Livewire::test(CompanyResource\Pages\CreateCompany::class)
            ->fillForm([
                'name' => 'No Location Company',
                'user_id' => $user->id,
                'website' => 'nolocation.com',
                'phone' => '123-456-7890',
                'email' => 'info@nolocation.com',
                'description' => 'Company without location',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', [
            'name' => 'No Location Company',
            'user_id' => $user->id,
            'website' => 'nolocation.com',
        ]);
    }
}
