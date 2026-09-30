<?php

use App\Actions\Organization\CreateOrganizationAction;
use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'organization.member'])
        ->get('/test-org/{organization:slug}', function (Organization $organization) {
            return response()->json(['id' => $organization->id]);
        });

    Route::middleware(['web', 'auth', 'organization.member', 'organization.role:admin,staff'])
        ->get('/test-org/{organization:slug}/manage', function (Organization $organization) {
            return response()->json(['manage' => true]);
        });

    Route::middleware(['web', 'auth', 'organization.member', 'organization.role:admin'])
        ->get('/test-org/{organization:slug}/admin-only', function (Organization $organization) {
            return response()->json(['admin' => true]);
        });
});

test('organization member can access member route', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();

    $action = new CreateOrganizationAction;
    $org = $action->execute($creator, [
        'name' => 'Organisasi Satu',
        'description' => 'Deskripsi',
        'category' => 'Umum',
    ]);

    $org->users()->attach($member->id, [
        'role' => Role::MEMBER->value,
        'joined_at' => now(),
    ]);

    $response = $this->actingAs($member)->get("/test-org/{$org->slug}");
    $response->assertOk();
    $response->assertJson(['id' => $org->id]);
});

test('non-member cannot access organization route (IDOR prevention)', function () {
    $creator1 = User::factory()->create();
    $creator2 = User::factory()->create();

    $action = new CreateOrganizationAction;
    $org1 = $action->execute($creator1, [
        'name' => 'Organisasi Alfa',
        'description' => 'Deskripsi Alfa',
        'category' => 'Umum',
    ]);

    $org2 = $action->execute($creator2, [
        'name' => 'Organisasi Beta',
        'description' => 'Deskripsi Beta',
        'category' => 'Umum',
    ]);

    // Creator 1 is not member of Org 2
    $response = $this->actingAs($creator1)->get("/test-org/{$org2->slug}");
    $response->assertForbidden();
});

test('staff and admin can access manage route, but regular member cannot', function () {
    $admin = User::factory()->create();
    $staff = User::factory()->create();
    $member = User::factory()->create();

    $action = new CreateOrganizationAction;
    $org = $action->execute($admin, [
        'name' => 'Organisasi Gamma',
        'description' => 'Deskripsi Gamma',
        'category' => 'Umum',
    ]);

    $org->users()->attach($staff->id, [
        'role' => Role::STAFF->value,
        'joined_at' => now(),
    ]);

    $org->users()->attach($member->id, [
        'role' => Role::MEMBER->value,
        'joined_at' => now(),
    ]);

    // Admin can access manage
    $this->actingAs($admin)->get("/test-org/{$org->slug}/manage")->assertOk();

    // Staff can access manage
    $this->actingAs($staff)->get("/test-org/{$org->slug}/manage")->assertOk();

    // Regular member is rejected with 403
    $this->actingAs($member)->get("/test-org/{$org->slug}/manage")->assertForbidden();
});

test('only admin can access admin-only route', function () {
    $admin = User::factory()->create();
    $staff = User::factory()->create();

    $action = new CreateOrganizationAction;
    $org = $action->execute($admin, [
        'name' => 'Organisasi Delta',
        'description' => 'Deskripsi Delta',
        'category' => 'Umum',
    ]);

    $org->users()->attach($staff->id, [
        'role' => Role::STAFF->value,
        'joined_at' => now(),
    ]);

    $this->actingAs($admin)->get("/test-org/{$org->slug}/admin-only")->assertOk();
    $this->actingAs($staff)->get("/test-org/{$org->slug}/admin-only")->assertForbidden();
});
