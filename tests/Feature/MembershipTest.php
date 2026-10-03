<?php

use App\Enums\Role;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\User;
use Livewire\Volt\Volt;

test('user can submit join request', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();

    Volt::test('organizations.index')
        ->actingAs($user)
        ->set('selectedOrgId', $org->id)
        ->call('submitJoinRequest');

    expect(MembershipRequest::where('user_id', $user->id)->where('organization_id', $org->id)->exists())->toBeTrue();
});

test('user cannot submit duplicate join request', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create();

    MembershipRequest::create([
        'user_id' => $user->id,
        'organization_id' => $org->id,
        'status' => 'pending',
    ]);

    $response = Volt::test('organizations.index')
        ->actingAs($user)
        ->set('selectedOrgId', $org->id)
        ->call('submitJoinRequest');

    $response->assertHasNoErrors();
    expect(session('error'))->toBe('Anda sudah memiliki permintaan bergabung yang pending.');
    expect(MembershipRequest::where('user_id', $user->id)->where('organization_id', $org->id)->count())->toBe(1);
});

test('admin can approve join request', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $req = MembershipRequest::create([
        'user_id' => $user->id,
        'organization_id' => $org->id,
        'status' => 'pending',
    ]);

    Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('approveRequest', $req->id);

    expect($req->fresh()->status)->toBe('approved');
    expect($user->isMemberOf($org))->toBeTrue();
});

test('admin can reject join request', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $req = MembershipRequest::create([
        'user_id' => $user->id,
        'organization_id' => $org->id,
        'status' => 'pending',
    ]);

    Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('rejectRequest', $req->id);

    expect($req->fresh()->status)->toBe('rejected');
    expect($user->isMemberOf($org))->toBeFalse();
});

test('admin can change member role', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $org = Organization::factory()->create();

    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $org->users()->attach($member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('changeRole', $member->id, 'staff');

    expect($member->roleIn($org))->toBe(Role::STAFF);
});

test('last admin cannot be demoted or removed', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $response = Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('changeRole', $admin->id, 'member');

    expect(session('error'))->toBe('Tidak bisa mengubah role Admin terakhir.');

    $response2 = Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('removeMember', $admin->id);

    expect(session('error'))->toBe('Tidak bisa menghapus Admin terakhir.');
});

test('member with active borrowing cannot be removed', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $org = Organization::factory()->create();

    $org->users()->attach($admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
    $org->users()->attach($member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

    $asset = Asset::factory()->create(['organization_id' => $org->id]);
    Borrowing::factory()->create([
        'organization_id' => $org->id,
        'user_id' => $member->id,
        'asset_id' => $asset->id,
        'status' => 'borrowed',
    ]);

    $response = Volt::test('manage.members.index', ['organization' => $org])
        ->actingAs($admin)
        ->call('removeMember', $member->id);

    expect(session('error'))->toBe('Anggota masih memiliki peminjaman aktif.');
    expect($member->isMemberOf($org))->toBeTrue();
});
