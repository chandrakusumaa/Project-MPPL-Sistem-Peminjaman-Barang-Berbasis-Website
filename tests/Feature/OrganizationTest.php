<?php

use App\Enums\Role;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Organization;
use App\Models\User;
use Livewire\Volt\Volt;

test('authenticated user can create organization and becomes admin', function () {
    $user = User::factory()->create();

    $response = Volt::test('organizations.create')
        ->actingAs($user)
        ->set('name', 'My New Org')
        ->set('category', 'Tech')
        ->set('description', 'Test desc')
        ->call('save');

    $response->assertRedirect(route('organizations.index', ['tab' => 'my']));

    $org = Organization::where('name', 'My New Org')->first();
    expect($org)->not->toBeNull();
    expect($org->max_borrow_days)->toBe(7);

    $role = $user->roleIn($org);
    expect($role)->toBe(Role::ADMIN);
});

test('admin can archive organization', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    Volt::test('manage.settings.index', ['organization' => $org])
        ->actingAs($user)
        ->call('archive');

    expect($org->fresh()->archived_at)->not->toBeNull();
});

test('admin can delete organization if no active borrowings', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    Volt::test('manage.settings.index', ['organization' => $org])
        ->actingAs($user)
        ->set('deleteConfirm', $org->name)
        ->call('delete');

    expect(Organization::find($org->id))->toBeNull(); // Soft deleted
});

test('admin cannot delete organization if active borrowings exist', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $asset = Asset::factory()->create(['organization_id' => $org->id]);
    Borrowing::factory()->create([
        'organization_id' => $org->id,
        'user_id' => $user->id,
        'asset_id' => $asset->id,
        'status' => 'borrowed',
    ]);

    $response = Volt::test('manage.settings.index', ['organization' => $org])
        ->actingAs($user)
        ->set('deleteConfirm', $org->name)
        ->call('delete');

    $response->assertHasErrors(['deleteConfirm']);
    expect(Organization::find($org->id))->not->toBeNull();
});

test('staff and members get 403 on settings page', function () {
    $admin = User::factory()->create();
    $staff = User::factory()->create();
    $member = User::factory()->create();
    $org = Organization::factory()->create();

    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $org->users()->attach($staff->id, ['role' => Role::STAFF->value, 'joined_at' => now()]);
    $org->users()->attach($member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    $this->actingAs($admin)->get(route('manage.settings.index', $org->slug))->assertStatus(200);
    $this->actingAs($staff)->get(route('manage.settings.index', $org->slug))->assertStatus(403);
    $this->actingAs($member)->get(route('manage.settings.index', $org->slug))->assertStatus(403);
});
