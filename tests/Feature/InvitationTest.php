<?php

use App\Enums\Role;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Livewire\Volt\Volt;

test('admin can invite user', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->set('inviteEmail', 'test@example.com')
        ->set('inviteRole', 'staff')
        ->call('invite');

    expect(OrganizationInvitation::where('email', 'test@example.com')->exists())->toBeTrue();
});

test('user can accept valid invitation', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'test@example.com']);

    $invitation = OrganizationInvitation::create([
        'organization_id' => $org->id,
        'email' => 'test@example.com',
        'role' => 'staff',
        'token' => 'test-token',
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
        'status' => 'pending',
    ]);

    Volt::test('invitations.accept', ['token' => 'test-token'])
        ->actingAs($invitee)
        ->call('accept');

    expect($invitation->fresh()->status)->toBe('accepted');
    expect($invitee->roleIn($org))->toBe(Role::STAFF);
});

test('invitation fails if wrong email', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create();
    $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);

    OrganizationInvitation::create([
        'organization_id' => $org->id,
        'email' => 'test@example.com',
        'role' => 'staff',
        'token' => 'test-token',
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
        'status' => 'pending',
    ]);

    Volt::test('invitations.accept', ['token' => 'test-token'])
        ->actingAs($wrongUser)
        ->assertSee('Email Tidak Sesuai');
});
