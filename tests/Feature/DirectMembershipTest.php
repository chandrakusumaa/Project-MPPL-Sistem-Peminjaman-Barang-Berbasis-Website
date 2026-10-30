<?php

use App\Actions\BorrowAsset;
use App\Actions\DeleteAccount;
use App\Actions\ManageMembers;
use App\Actions\ManageWorkspace;
use App\Livewire\Home;
use App\Livewire\OrganizationMenu;
use App\Livewire\Workspace;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    $this->seed();
    foreach (['admin', 'staff', 'member', 'other'] as $role) {
        $this->$role = User::where('email', $role.'@example.test')->firstOrFail();
        RateLimiter::clear('organization-join:'.$this->$role->id);
    }
    $this->org = Organization::firstOrFail();
    $this->asset = Asset::firstOrFail();
});

it('never creates membership from a public page visit or a guest join attempt', function () {
    $before = Membership::count();
    $this->get(route('organization.public', $this->org))->assertOk();
    $this->post(route('organization.join', $this->org), ['role' => 'admin'])->assertRedirect(route('login'));
    Livewire::test(Home::class)->call('join', $this->org->id)->assertRedirect(route('login'));
    expect(Membership::count())->toBe($before);
});

it('requires email verification even when calling the action directly', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user)->post(route('organization.join', $this->org))->assertRedirect(route('verification.notice'));
    expect(fn () => app(ManageWorkspace::class)->join($user, $this->org))->toThrow(AuthorizationException::class);
    expect(Membership::where('user_id', $user->id)->exists())->toBeFalse();
});

it('ignores forged join roles and immediately grants only member access', function (string $role) {
    $this->actingAs($this->other)->get(route('organization.public', $this->org))->assertOk()->assertSee('Gabung');
    expect(Membership::where('user_id', $this->other->id)->exists())->toBeFalse();
    $this->post(route('organization.join', $this->org), ['role' => $role, 'status' => 'revoked', 'user_id' => $this->admin->id])
        ->assertRedirect(route('workspace', $this->org));
    $membership = Membership::where('user_id', $this->other->id)->firstOrFail();
    expect($membership->role)->toBe('member')->and($membership->status)->toBe('active')
        ->and(Gate::forUser($this->other)->allows('view', $this->org))->toBeTrue()
        ->and(Gate::forUser($this->other)->allows('operate', $this->org))->toBeFalse();
    $this->get(route('workspace', $this->org))->assertOk()->assertSee('Katalog aset');
})->with(['admin', 'staff']);

it('keeps active role and every membership timestamp unchanged on repeated joins', function (string $role) {
    $user = $this->$role;
    $before = Membership::where('user_id', $user->id)->firstOrFail()->getAttributes();
    $action = app(ManageWorkspace::class);
    $first = $action->join($user, $this->org);
    $this->travel(5)->minutes();
    $second = $action->join($user, $this->org);
    expect($second->getAttributes())->toBe($before)->and($first->id)->toBe($second->id)
        ->and(Membership::where('user_id', $user->id)->where('organization_id', $this->org->id)->count())->toBe(1)
        ->and(AuditLog::where('action', 'membership.joined')->count())->toBe(0);
    $this->actingAs($user)->post(route('organization.join', $this->org), ['role' => 'member'])
        ->assertRedirect(route('workspace', $this->org))
        ->assertSessionHas('status', 'Keanggotaanmu sudah aktif. Buka Organization untuk melanjutkan.');
    expect($second->fresh()->getAttributes())->toBe($before);
})->with(['admin', 'staff', 'member']);

it('does not reactivate a revoked membership through HTTP, Livewire, or the action', function () {
    $member = Membership::where('user_id', $this->member->id)->firstOrFail();
    app(ManageMembers::class)->change($this->admin, $this->org, $member->id, 'revoked');
    $before = $member->fresh()->getAttributes();
    $this->actingAs($this->member)->post(route('organization.join', $this->org))->assertForbidden();
    expect(fn () => app(ManageWorkspace::class)->join($this->member, $this->org))->toThrow(AuthorizationException::class);
    Livewire::actingAs($this->member)->test(Home::class)->call('showListing', 'all')->assertSee('Akses dicabut')
        ->call('join', $this->org->id)->assertHasErrors('action');
    expect($member->fresh()->getAttributes())->toBe($before);
});

it('rejects archived organizations using fresh state under the lock', function () {
    app(ManageMembers::class)->archive($this->admin, $this->org);
    $this->actingAs($this->other)->post(route('organization.join', $this->org))->assertForbidden();
    expect(fn () => app(ManageWorkspace::class)->join($this->other, $this->org))->toThrow(AuthorizationException::class);
    expect(Membership::where('user_id', $this->other->id)->exists())->toBeFalse();
});

it('rejects deleted organizations and users even with stale model instances', function () {
    app(ManageMembers::class)->delete($this->admin, $this->org, $this->org->name);
    $this->actingAs($this->other)->post(route('organization.join', $this->org))->assertNotFound();
    expect(fn () => app(ManageWorkspace::class)->join($this->other, $this->org))->toThrow(ModelNotFoundException::class);
    $another = app(ManageWorkspace::class)->create($this->admin, ['name' => 'Organization aktif', 'category' => 'komunitas']);
    app(DeleteAccount::class)->execute($this->other);
    expect(fn () => app(ManageWorkspace::class)->join($this->other, $another))->toThrow(ModelNotFoundException::class);
    expect(Membership::where('user_id', $this->other->id)->exists())->toBeFalse();
});

it('blocks member and staff self promotion through direct Livewire and action calls', function (string $role) {
    $user = $this->$role;
    $membership = Membership::where('user_id', $user->id)->firstOrFail();
    Livewire::actingAs($user)->test(Workspace::class, ['organization' => $this->org])
        ->call('member', $membership->id, 'admin')->assertForbidden();
    expect(fn () => app(ManageMembers::class)->change($user, $this->org, $membership->id, 'staff'))->toThrow(AuthorizationException::class);
    expect($membership->fresh()->role)->toBe($role);
})->with(['member', 'staff']);

it('allows only a local admin to promote a newly joined member and audits both events', function () {
    $member = app(ManageWorkspace::class)->join($this->other, $this->org);
    Livewire::actingAs($this->admin)->test(Workspace::class, ['organization' => $this->org])
        ->call('member', $member->id, 'staff')->assertHasNoErrors();
    expect($member->fresh()->role)->toBe('staff')->and($member->fresh()->status)->toBe('active');
    $joined = AuditLog::where('action', 'membership.joined')->firstOrFail();
    $changed = AuditLog::where('action', 'membership.changed')->firstOrFail();
    expect(json_decode($joined->new_values, true))->toMatchArray(['user_id' => $this->other->id, 'role' => 'member', 'status' => 'active'])
        ->and(json_decode($changed->old_values, true)['role'])->toBe('member')
        ->and(json_decode($changed->new_values, true)['role'])->toBe('staff');
});

it('isolates role changes across organizations and preserves the last active admin', function () {
    $foreign = app(ManageWorkspace::class)->create($this->other, ['name' => 'Organization lain', 'category' => 'komunitas']);
    $localMember = Membership::where('user_id', $this->member->id)->firstOrFail();
    expect(fn () => app(ManageMembers::class)->change($this->other, $this->org, $localMember->id, 'staff'))->toThrow(AuthorizationException::class);
    $foreignMember = app(ManageWorkspace::class)->join($this->member, $foreign);
    expect(fn () => app(ManageMembers::class)->change($this->admin, $this->org, $foreignMember->id, 'staff'))->toThrow(ModelNotFoundException::class);
    $owner = Membership::where('user_id', $this->admin->id)->firstOrFail();
    expect(fn () => app(ManageMembers::class)->change($this->admin, $this->org, $owner->id, 'member'))->toThrow(ValidationException::class);
    expect($owner->fresh()->role)->toBe('admin')->and($foreignMember->fresh()->role)->toBe('member');
});

it('notifies only admins on a new join and does not repeat audit or notifications on retries', function () {
    $action = app(ManageWorkspace::class);
    $action->join($this->other, $this->org);
    $action->join($this->other, $this->org);
    expect(DB::table('notifications')->where('organization_id', $this->org->id)->pluck('notifiable_id')->all())->toBe([$this->admin->id])
        ->and(AuditLog::where('action', 'membership.joined')->count())->toBe(1);
    Notification::assertNothingSentTo($this->staff);
    Notification::assertNothingSentTo($this->member);
    Notification::assertNothingSentTo($this->other);
});

it('refreshes the current accounts organization navigation after a successful join', function () {
    $menu = Livewire::actingAs($this->other)->test(OrganizationMenu::class)
        ->assertViewHas('organizations', fn ($rows) => $rows->isEmpty());
    Livewire::actingAs($this->other)->test(Home::class)->call('showListing', 'all')->call('join', $this->org->id)
        ->assertDispatched('organization-membership-updated')->assertSee('Buka Organization');
    $menu->dispatch('organization-membership-updated')
        ->assertViewHas('organizations', fn ($rows) => $rows->pluck('id')->all() === [$this->org->id]);
});

it('limits join requests without altering membership on repeated submissions', function () {
    $this->actingAs($this->other);
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->post(route('organization.join', $this->org))->assertRedirect(route('workspace', $this->org));
    }
    $this->post(route('organization.join', $this->org))->assertStatus(429)->assertHeader('Retry-After');
    expect(Membership::where('user_id', $this->other->id)->count())->toBe(1)
        ->and(AuditLog::where('action', 'membership.joined')->count())->toBe(1);
});

it('keeps borrowing request, cancellation, approval and rejection after joining', function () {
    app(ManageWorkspace::class)->join($this->other, $this->org);
    $action = app(BorrowAsset::class);
    $data = ['borrow_date' => today('Asia/Jakarta')->toDateString(), 'due_date' => today('Asia/Jakarta')->addDays(2)->toDateString(), 'reason' => 'Meminjam untuk kegiatan bersama', 'acknowledged' => true];
    $cancelled = $action->request($this->other, $this->org, $this->asset->id, $data);
    $action->decide($this->other, $this->org, $cancelled->id, 'cancel');
    $approved = $action->request($this->other, $this->org, $this->asset->id, $data);
    expect(fn () => $action->decide($this->other, $this->org, $approved->id, 'approve'))->toThrow(AuthorizationException::class);
    $action->decide($this->admin, $this->org, $approved->id, 'approve');
    $action->inspect($this->admin, $this->org, $approved->id, ['condition' => 'good']);
    $rejected = $action->request($this->other, $this->org, $this->asset->id, $data);
    $action->decide($this->admin, $this->org, $rejected->id, 'reject', 'Kebutuhan jadwal pengelola');
    expect($cancelled->fresh()->status)->toBe('cancelled')->and($approved->fresh()->status)->toBe('returned')
        ->and($rejected->fresh()->status)->toBe('rejected')->and(Membership::where('user_id', $this->other->id)->value('role'))->toBe('member');
});

it('still cancels pending borrowings when deleting an account or organization', function (string $target) {
    app(ManageWorkspace::class)->join($this->other, $this->org);
    $borrowing = app(BorrowAsset::class)->request($this->other, $this->org, $this->asset->id, ['borrow_date' => today('Asia/Jakarta')->toDateString(), 'due_date' => today('Asia/Jakarta')->addDay()->toDateString(), 'reason' => 'Peminjaman sebelum penghapusan', 'acknowledged' => true]);
    if ($target === 'account') {
        app(DeleteAccount::class)->execute($this->other);
        expect(User::withTrashed()->findOrFail($this->other->id)->trashed())->toBeTrue();
    } else {
        app(ManageMembers::class)->delete($this->admin, $this->org, $this->org->name);
        expect(Organization::withTrashed()->findOrFail($this->org->id)->trashed())->toBeTrue();
    }
    expect($borrowing->fresh()->status)->toBe('cancelled');
})->with(['account', 'organization']);

it('keeps open borrowing obligations protected when membership deletion flows change', function () {
    app(ManageWorkspace::class)->join($this->other, $this->org);
    $borrowing = app(BorrowAsset::class)->request($this->other, $this->org, $this->asset->id, ['borrow_date' => today('Asia/Jakarta')->toDateString(), 'due_date' => today('Asia/Jakarta')->addDay()->toDateString(), 'reason' => 'Peminjaman dengan kewajiban aktif', 'acknowledged' => true]);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $borrowing->id, 'approve');
    $member = Membership::where('user_id', $this->other->id)->firstOrFail();
    expect(fn () => app(ManageMembers::class)->change($this->admin, $this->org, $member->id, 'revoked'))->toThrow(ValidationException::class);
    expect(fn () => app(DeleteAccount::class)->execute($this->other))->toThrow(ValidationException::class);
    expect(fn () => app(ManageMembers::class)->delete($this->admin, $this->org, $this->org->name))->toThrow(ValidationException::class);
    expect($member->fresh()->status)->toBe('active')->and($borrowing->fresh()->status)->toBe('borrowed');
});

it('makes old invitation endpoints unavailable without granting any membership', function () {
    $before = Membership::orderBy('id')->get()->toJson();
    foreach ([null, $this->other] as $user) {
        if ($user) {
            $this->actingAs($user);
        }
        $this->get('/invitations/'.str_repeat('a', 64))->assertNotFound();
        $this->post('/invitations/'.str_repeat('a', 64), ['role' => 'admin'])->assertNotFound();
    }
    expect(Membership::orderBy('id')->get()->toJson())->toBe($before);
});
