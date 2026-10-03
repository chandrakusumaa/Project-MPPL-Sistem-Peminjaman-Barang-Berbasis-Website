<?php

use App\Models\Organization;
use Livewire\Volt\Volt;

test('explore page is accessible to guest', function () {
    $response = $this->get('/explore');
    $response->assertStatus(200);
});

test('explore page shows unarchived organizations', function () {
    $activeOrg = Organization::factory()->create(['name' => 'Active Org']);
    $archivedOrg = Organization::factory()->create(['name' => 'Archived Org', 'archived_at' => now()]);

    Volt::test('explore.organization-list')
        ->assertSee('Active Org')
        ->assertDontSee('Archived Org');
});

test('explore page can be filtered by category', function () {
    Organization::factory()->create(['name' => 'Tech Org', 'category' => 'Technology']);
    Organization::factory()->create(['name' => 'Edu Org', 'category' => 'Education']);

    Volt::test('explore.organization-list')
        ->set('category', 'Technology')
        ->assertSee('Tech Org')
        ->assertDontSee('Edu Org');
});

test('explore page can be searched by name', function () {
    Organization::factory()->create(['name' => 'Tech Org']);
    Organization::factory()->create(['name' => 'Edu Org']);

    Volt::test('explore.organization-list')
        ->set('search', 'Tech')
        ->assertSee('Tech Org')
        ->assertDontSee('Edu Org');
});

test('organization profile is accessible to guest and shows apply button redirecting to login', function () {
    $org = Organization::factory()->create(['name' => 'Tech Org']);

    $response = $this->get('/explore/'.$org->slug);
    $response->assertStatus(200);

    Volt::test('explore.organization-profile', ['organization' => $org])
        ->assertSee('Tech Org')
        ->call('applyToJoin')
        ->assertRedirect(route('login'));
});

test('organization profile for archived org returns 404', function () {
    $org = Organization::factory()->create(['name' => 'Archived Org', 'archived_at' => now()]);

    $response = $this->get('/explore/'.$org->slug);
    $response->assertStatus(404);
});
