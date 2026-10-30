<?php

use App\Actions\ManageWorkspace;
use App\Livewire\Home;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

it('redirects unverified workspace creation to email verification without creating data', function () {
    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)->test(Home::class)
        ->set('name', 'Ruang Baru')
        ->call('create')
        ->assertRedirect(route('verification.notice'));

    expect(Organization::count())->toBe(0)
        ->and($user->fresh()->email_verified_at)->toBeNull();
});

it('keeps public exploration accessible but does not offer an unverified create form', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('organizations'))
        ->assertOk()
        ->assertSee('Jelajahi Organization')
        ->assertDontSee('Buat Organization')
        ->assertDontSee('wire:submit="create"', false);
    $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
});

it('redirects unauthenticated workspace creation to login without creating data', function () {
    Livewire::test(Home::class)->set('name', 'Ruang Tanpa Sesi')->call('create')
        ->assertRedirect(route('login'));

    expect(Organization::count())->toBe(0);
});

it('routes an unverified join to verification without creating membership', function () {
    $owner = User::factory()->create();
    $organization = app(ManageWorkspace::class)->create($owner, ['name' => 'Organization Publik', 'category' => 'lainnya']);
    $user = User::factory()->unverified()->create();
    Livewire::actingAs($user)->test(Home::class)->call('join', $organization->id)
        ->assertRedirect(route('verification.notice'));
    expect(Membership::where('user_id', $user->id)->count())->toBe(0);
});

it('lets a newly verified account create a room and become its admin without an existing role', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);
    $link = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
    $this->get($link)->assertRedirect(route('dashboard'));

    Livewire::actingAs($user->fresh())->test(Home::class)->set('name', 'Ruang Setelah Verifikasi')->call('create')->assertHasNoErrors();
    $organization = Organization::where('name', 'Ruang Setelah Verifikasi')->firstOrFail();
    expect(Membership::where('organization_id', $organization->id)->where('user_id', $user->id)->value('role'))->toBe('admin');
});
