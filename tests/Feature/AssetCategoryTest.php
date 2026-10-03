<?php

use App\Enums\Role;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use App\Models\User;
use Livewire\Volt\Volt;

test('admin can create category', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    Volt::test('manage.categories.index', ['organization' => $org])
        ->actingAs($admin)
        ->set('name', 'Electronics')
        ->call('save');

    expect(AssetCategory::where('organization_id', $org->id)->where('name', 'Electronics')->exists())->toBeTrue();
});

test('member cannot create category', function () {
    $member = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    $this->actingAs($member)
        ->get(route('manage.categories.index', $org->slug))
        ->assertStatus(403);
});

test('cannot delete category with assets', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tools']);
    Asset::factory()->create(['organization_id' => $org->id, 'asset_category_id' => $category->id]);

    $response = Volt::test('manage.categories.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('delete', $category->id);

    expect(session('error'))->toBe('Kategori tidak dapat dihapus karena masih digunakan oleh aset.');
    expect(AssetCategory::find($category->id))->not->toBeNull();
});

test('can delete unused category', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tools']);

    Volt::test('manage.categories.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('delete', $category->id);

    expect(AssetCategory::find($category->id))->toBeNull();
});
