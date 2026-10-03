<?php

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use App\Models\User;
use Livewire\Volt\Volt;

test('admin can create asset', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tech']);

    Volt::test('manage.inventory.create', ['organization' => $org])
        ->actingAs($admin)
        ->set('name', 'Laptop Asus')
        ->set('asset_category_id', $category->id)
        ->set('description', 'A good laptop')
        ->set('specifications', '16GB RAM')
        ->set('location', 'Room 1')
        ->call('save')
        ->assertRedirect(route('manage.inventory.index', $org->slug));

    $asset = Asset::where('name', 'Laptop Asus')->first();
    expect($asset)->not->toBeNull();
    expect($asset->code)->not->toBeEmpty();
    expect($asset->status)->toBe(AssetStatus::AVAILABLE);
    expect($asset->logs()->count())->toBe(1); // created event log
});

test('cannot delete borrowed asset', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tech']);

    $asset = Asset::factory()->create([
        'organization_id' => $org->id,
        'asset_category_id' => $category->id,
        'status' => AssetStatus::BORROWED,
    ]);

    Volt::test('manage.inventory.show', ['organization' => $org, 'asset' => $asset])
        ->actingAs($admin)
        ->call('delete');

    expect(session('error'))->toBe('Aset tidak dapat dihapus karena sedang dipinjam.');
    expect(Asset::find($asset->id))->not->toBeNull();
});

test('member can see catalog but staff gets 403 on manage', function () {
    $member = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);
    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tech']);
    $asset = Asset::factory()->create(['organization_id' => $org->id, 'asset_category_id' => $category->id]);

    $this->actingAs($member)
        ->get(route('organization.catalog', $org->slug))
        ->assertStatus(200)
        ->assertSee($asset->name);

    $this->actingAs($member)
        ->get(route('organization.asset.show', [$org->slug, $asset->code]))
        ->assertStatus(200)
        ->assertSee($asset->name);

    $this->actingAs($member)
        ->get(route('manage.inventory.index', $org->slug))
        ->assertStatus(403);
});
