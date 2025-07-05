<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('admin can view user management page', function () {
    $admin = User::factory()->create();

    actingAs($admin)
        ->get('/admin/users')
        ->assertSuccessful();
});

test('admin can create a new user', function () {
    $admin = User::factory()->create();
    
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ];
    
    actingAs($admin)
        ->post('/admin/users', $userData)
        ->assertRedirect();
    
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('admin can edit a user', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);
    
    $updatedData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];
    
    actingAs($admin)
        ->patch("/admin/users/{$user->id}", $updatedData)
        ->assertRedirect();
    
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

test('admin can archive a user', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    
    actingAs($admin)
        ->delete("/admin/users/{$user->id}")
        ->assertRedirect();
    
    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('admin can restore an archived user', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    
    // First archive the user
    $user->delete();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
    
    // Then restore
    actingAs($admin)
        ->patch("/admin/users/{$user->id}/restore")
        ->assertRedirect();
    
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});

test('admin can permanently delete a user', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    
    // First archive the user
    $user->delete();
    
    // Then force delete
    actingAs($admin)
        ->delete("/admin/users/{$user->id}/force")
        ->assertRedirect();
    
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});
