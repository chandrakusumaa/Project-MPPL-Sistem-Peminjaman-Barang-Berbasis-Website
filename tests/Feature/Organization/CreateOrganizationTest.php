<?php

use App\Actions\Organization\CreateOrganizationAction;
use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;

test('action creates organization and sets creator as admin', function () {
    $user = User::factory()->create();

    $action = new CreateOrganizationAction;
    $organization = $action->execute($user, [
        'name' => 'Laboratorium Rekayasa Perangkat Lunak',
        'description' => 'Laboratorium untuk riset rekayasa perangkat lunak.',
        'category' => 'Teknologi',
        'max_borrow_days' => 14,
        'late_fine_per_day' => 2000,
    ]);

    expect($organization)->toBeInstanceOf(Organization::class)
        ->and($organization->name)->toBe('Laboratorium Rekayasa Perangkat Lunak')
        ->and($organization->slug)->toBe('laboratorium-rekayasa-perangkat-lunak')
        ->and($organization->created_by)->toBe($user->id)
        ->and($organization->max_borrow_days)->toBe(14)
        ->and($organization->late_fine_per_day)->toBe(2000);

    expect($user->isMemberOf($organization))->toBeTrue()
        ->and($user->isAdminOf($organization))->toBeTrue()
        ->and($user->roleIn($organization))->toBe(Role::ADMIN);

    $this->assertDatabaseHas('organizations', [
        'id' => $organization->id,
        'name' => 'Laboratorium Rekayasa Perangkat Lunak',
    ]);

    $this->assertDatabaseHas('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'role' => Role::ADMIN->value,
    ]);
});

test('duplicate organization names generate unique slugs', function () {
    $user = User::factory()->create();
    $action = new CreateOrganizationAction;

    $org1 = $action->execute($user, [
        'name' => 'Club Robotika',
        'description' => 'Deskripsi pertama',
        'category' => 'Robotika',
    ]);

    $org2 = $action->execute($user, [
        'name' => 'Club Robotika',
        'description' => 'Deskripsi kedua',
        'category' => 'Robotika',
    ]);

    expect($org1->slug)->toBe('club-robotika')
        ->and($org2->slug)->toBe('club-robotika-1');
});

test('organization and user support soft deletes', function () {
    $user = User::factory()->create();
    $action = new CreateOrganizationAction;

    $org = $action->execute($user, [
        'name' => 'Komunitas Data Science',
        'description' => 'Deskripsi',
        'category' => 'Data',
    ]);

    $org->delete();
    expect($org->trashed())->toBeTrue();
    $this->assertSoftDeleted('organizations', ['id' => $org->id]);

    $user->delete();
    expect($user->trashed())->toBeTrue();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});
