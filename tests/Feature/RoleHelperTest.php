<?php

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;

it('creates models and sets up relationships', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['created_by' => $user->id]);

    $org->users()->attach($user->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    expect($org->creator->id)->toBe($user->id);
    expect($org->users->first()->id)->toBe($user->id);
    expect($user->organizations->first()->id)->toBe($org->id);
});

it('tests role per organization helpers', function () {
    $adminUser = User::factory()->create();
    $memberUser = User::factory()->create();

    $orgA = Organization::factory()->create(['created_by' => $adminUser->id]);
    $orgB = Organization::factory()->create(['created_by' => $memberUser->id]);

    $orgA->users()->attach($adminUser->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $orgA->users()->attach($memberUser->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    $orgB->users()->attach($memberUser->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $orgB->users()->attach($adminUser->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    // AdminUser is Admin in OrgA but Member in OrgB
    expect($adminUser->isAdminOf($orgA))->toBeTrue();
    expect($adminUser->isMemberOf($orgB))->toBeTrue();
    expect($adminUser->isAdminOf($orgB))->toBeFalse();

    // MemberUser is Admin in OrgB but Member in OrgA
    expect($memberUser->isAdminOf($orgB))->toBeTrue();
    expect($memberUser->isMemberOf($orgA))->toBeTrue();
    expect($memberUser->isAdminOf($orgA))->toBeFalse();
});
