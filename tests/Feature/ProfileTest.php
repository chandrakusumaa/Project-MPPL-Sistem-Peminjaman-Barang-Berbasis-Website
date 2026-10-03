<?php

use App\Models\User;
use App\Models\Organization;
use App\Enums\Role;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use App\Models\MembershipRequest;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password')
    ]);
});

test('profile page can be rendered', function () {
    $this->actingAs($this->user);
    $response = $this->get('/settings/profile');
    $response->assertStatus(200);
});

test('user can update personal information', function () {
    $this->actingAs($this->user);

    Volt::test('settings.profile')
        ->set('name', 'Jane Doe')
        ->set('notify_membership', false)
        ->call('updateProfileInformation');

    $this->user->refresh();

    expect($this->user->name)->toBe('Jane Doe');
    expect($this->user->notification_preferences['membership'])->toBeFalse();
});

test('user can request email change and pending email is set', function () {
    $this->actingAs($this->user);

    Volt::test('settings.profile')
        ->set('email', 'jane@example.com')
        ->call('updateProfileInformation');

    $this->user->refresh();

    expect($this->user->email)->toBe('john@example.com'); // Unchanged
    expect($this->user->pending_email)->toBe('jane@example.com'); // Pending set
});

test('user can verify pending email with valid signature', function () {
    $this->user->pending_email = 'jane@example.com';
    $this->user->save();
    
    $this->actingAs($this->user);

    $url = URL::temporarySignedRoute(
        'profile.verify-pending-email',
        now()->addMinutes(60),
        ['user' => $this->user->id, 'email' => 'jane@example.com']
    );

    $response = $this->get($url);
    
    $this->user->refresh();
    expect($this->user->email)->toBe('jane@example.com');
    expect($this->user->pending_email)->toBeNull();
    $response->assertRedirect('/settings/profile');
});

test('user cannot verify pending email with invalid signature', function () {
    $this->user->pending_email = 'jane@example.com';
    $this->user->save();
    
    $this->actingAs($this->user);

    // Provide wrong email to invalidate signature
    $url = URL::temporarySignedRoute(
        'profile.verify-pending-email',
        now()->addMinutes(60),
        ['user' => $this->user->id, 'email' => 'wrong@example.com']
    );

    $response = $this->get($url);
    
    $this->user->refresh();
    expect($this->user->email)->toBe('john@example.com');
    expect($this->user->pending_email)->toBe('jane@example.com');
});

test('user cannot delete account if they are the sole admin of an organization', function () {
    $org = Organization::factory()->create();
    $org->users()->attach($this->user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $this->actingAs($this->user);

    Volt::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    $this->assertDatabaseHas('users', ['id' => $this->user->id]);
});

test('user can delete account if they are not the sole admin', function () {
    $org = Organization::factory()->create();
    $org->users()->attach($this->user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    
    // Add another admin
    $anotherAdmin = User::factory()->create();
    $org->users()->attach($anotherAdmin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    
    // Create a pending membership request
    MembershipRequest::factory()->create([
        'organization_id' => $org->id,
        'user_id' => $this->user->id,
        'status' => 'pending'
    ]);

    $this->actingAs($this->user);

    Volt::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors();

    // User should be soft deleted
    $this->assertSoftDeleted('users', ['id' => $this->user->id]);
    
    // Membership should be detached
    expect($org->users()->where('user_id', $this->user->id)->count())->toBe(0);
    
    // Request should be cancelled
    $this->assertDatabaseHas('membership_requests', [
        'user_id' => $this->user->id,
        'status' => 'cancelled'
    ]);
});
