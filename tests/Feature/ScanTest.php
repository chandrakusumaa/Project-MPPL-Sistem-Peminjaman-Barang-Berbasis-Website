<?php

use App\Enums\Role;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use App\Models\User;
use Livewire\Volt\Volt;

test('scan invalid QR URL shows error', function () {
    $user = User::factory()->create();

    Volt::test('scan.index')
        ->actingAs($user)
        ->set('manualCode', 'https://example.com/invalid')
        ->call('processManual')
        ->assertSee('QR / Aset Invalid');
});

test('scan valid QR non-member shows join prompt', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tech']);
    $asset = Asset::factory()->create(['organization_id' => $org->id, 'asset_category_id' => $category->id]);

    $url = route('organization.asset.show', [$org->slug, $asset->code]);

    Volt::test('scan.index')
        ->actingAs($user)
        ->set('manualCode', $url)
        ->call('processManual')
        ->assertSee('Anda harus bergabung ke organisasi');
});

test('scan valid QR member redirects to detail', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);
    $category = AssetCategory::create(['organization_id' => $org->id, 'name' => 'Tech']);
    $asset = Asset::factory()->create(['organization_id' => $org->id, 'asset_category_id' => $category->id]);

    $url = route('organization.asset.show', [$org->slug, $asset->code]);

    Volt::test('scan.index')
        ->actingAs($user)
        ->set('manualCode', $url)
        ->call('processManual')
        ->assertRedirect($url);
});
