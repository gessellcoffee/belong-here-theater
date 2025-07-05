<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_render_user_index_page()
    {
        $this->get(UserResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_render_create_user_form()
    {
        $this->get(UserResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_create_user()
    {
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNull($user->email_verified_at, 'Email should not be verified by default');
    }
    
    /** @test */
    public function it_can_create_user_with_verified_email()
    {
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => 'Verified User',
                'email' => 'verified@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'verify_email' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Verified User',
            'email' => 'verified@example.com',
        ]);
        
        $user = User::where('email', 'verified@example.com')->first();
        $this->assertNotNull($user->email_verified_at, 'Email should be verified when verify_email is checked');
    }

    /** @test */
    public function it_can_render_edit_user_form()
    {
        $user = User::factory()->create();

        $this->get(UserResource::getUrl('edit', ['record' => $user]))
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_update_user()
    {
        $user = User::factory()->create();

        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->getKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function it_can_archive_user()
    {
        $user = User::factory()->create();

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->callTableAction('delete', $user);

        $this->assertSoftDeleted($user);
    }

    /** @test */
    public function it_can_restore_archived_user()
    {
        $user = User::factory()->create(['deleted_at' => now()]);

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->callTableAction('restore', $user);

        $this->assertNotSoftDeleted($user);
    }

    /** @test */
    public function it_can_verify_user_email_from_list_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertNull($user->email_verified_at);

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->callTableAction('verifyEmail', $user);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
    
    /** @test */
    public function it_can_verify_user_email_from_edit_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertNull($user->email_verified_at);

        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->getKey(),
        ])
            ->callAction('verifyEmail');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    /** @test */
    public function it_can_unverify_user_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($user->email_verified_at);

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->callTableAction('unverifyEmail', $user);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    /** @test */
    public function email_verified_status_is_displayed_as_read_only()
    {
        $user = User::factory()->create();

        $response = $this->get(UserResource::getUrl('edit', ['record' => $user]));
        
        // Ensure the form doesn't contain an input for email_verified_at
        $response->assertDontSee('name="email_verified_at"');
        
        // But does show the verification status placeholder
        $response->assertSee('Email Verification Status');
        
        // Check for the verification status text
        if ($user->email_verified_at) {
            $response->assertSee('Verified on');
        } else {
            $response->assertSee('Not Verified');
        }
    }
}
