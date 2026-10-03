<?php

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'organization.member', 'organization.role:admin,staff'])
        ->get('/o/{organization:slug}/manage', function () {
            return 'Manage Area';
        });

    Route::middleware(['web', 'auth', 'organization.member'])
        ->get('/o/{organization:slug}/dashboard', function () {
            return 'Member Dashboard';
        });
});

it('allows admin and staff to access manage area', function () {
    $admin = User::factory()->create();
    $staff = User::factory()->create();
    $org = Organization::factory()->create(['created_by' => $admin->id]);

    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $org->users()->attach($staff->id, ['role' => Role::STAFF->value, 'joined_at' => now()]);

    $this->actingAs($admin)
        ->get("/o/{$org->slug}/manage")
        ->assertStatus(200);

    $this->actingAs($staff)
        ->get("/o/{$org->slug}/manage")
        ->assertStatus(200);
});

it('denies members from accessing manage area', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $org = Organization::factory()->create(['created_by' => $admin->id]);

    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $org->users()->attach($member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    $this->actingAs($member)
        ->get("/o/{$org->slug}/manage")
        ->assertStatus(403);
});

it('denies non-members from accessing member dashboard', function () {
    $admin = User::factory()->create();
    $nonMember = User::factory()->create();
    $org = Organization::factory()->create(['created_by' => $admin->id]);

    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $this->actingAs($nonMember)
        ->get("/o/{$org->slug}/dashboard")
        ->assertStatus(403);
});

it('prevents IDOR across organizations', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create(['created_by' => $user->id]);
    $orgB = Organization::factory()->create(); // User is not in OrgB

    $orgA->users()->attach($user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    // Accessing Org A is fine
    $this->actingAs($user)
        ->get("/o/{$orgA->slug}/manage")
        ->assertStatus(200);

    // Accessing Org B fails
    $this->actingAs($user)
        ->get("/o/{$orgB->slug}/manage")
        ->assertStatus(403);
});
