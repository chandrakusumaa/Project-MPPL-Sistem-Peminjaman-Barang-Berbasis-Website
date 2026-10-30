<?php

use App\Actions\ManageWorkspace;
use App\Livewire\ExploreOrganizations;
use App\Livewire\Home;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    $this->seed();
    $this->other = User::where('email', 'other@example.test')->firstOrFail();
    $this->admin = User::where('email', 'admin@example.test')->firstOrFail();
    $this->org = Organization::firstOrFail();
});

it('keeps navbar exploration public and read-only for guests and signed-in accounts', function () {
    foreach ([null, $this->admin] as $user) {
        if ($user) {
            $this->actingAs($user);
        }
        $this->get(route('organizations'))->assertOk()->assertSeeLivewire(ExploreOrganizations::class)
            ->assertSee('Peralatan Raka')->assertSee('Lihat Organization')
            ->assertDontSee('Organization Saya')->assertDontSee('Buat Organization')->assertDontSee('Ajukan bergabung')
            ->assertDontSee('oi-listing-tabs', false)->assertDontSee('id="account-sidebar"', false);
    }
});

it('protects the account organization page and displays only memberships by default', function () {
    $this->get(route('personal.organizations'))->assertRedirect(route('login'));
    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified)->get(route('personal.organizations'))->assertRedirect(route('verification.notice'));
    $this->actingAs($this->other)->get(route('personal.organizations'))->assertOk()->assertSeeLivewire(Home::class)
        ->assertSee('id="account-sidebar"', false)->assertSee('Belum ada Organization yang sesuai')
        ->assertDontSee('Peralatan Raka')->assertDontSee('oi-account-note', false)->assertDontSee('Satu akun, banyak Organization.');
    $this->get(route('personal.organizations', ['listing' => 'all']))->assertOk()->assertSee('Peralatan Raka')->assertSee('id="account-sidebar"', false);
});

it('switches account exploration in place and joins directly as a member', function () {
    $component = Livewire::actingAs($this->other)->test(Home::class)
        ->assertSet('listing', 'mine')->assertViewHas('organizations', fn ($rows) => $rows->isEmpty())
        ->call('showListing', 'all')->assertSet('listing', 'all')
        ->assertViewHas('organizations', fn ($rows) => $rows->pluck('id')->all() === [$this->org->id])
        ->assertSee('Gabung')->assertNoRedirect();
    $component->call('join', $this->org->id)->assertSee('Berhasil bergabung.');
    expect(Membership::where('user_id', $this->other->id)->value('role'))->toBe('member');
    $component->call('showListing', 'mine')->assertViewHas('organizations', fn ($rows) => $rows->count() === 1)->assertSee('Buka Organization')->assertNoRedirect();
});

it('resets account pagination when changing tabs and keeps discovery filters scoped', function () {
    for ($index = 0; $index < 12; $index++) {
        app(ManageWorkspace::class)->create($this->admin, ['name' => 'Organization kampus '.$index, 'category' => 'kampus']);
    }
    Livewire::actingAs($this->other)->test(Home::class)->call('showListing', 'all')->call('setPage', 2)
        ->assertViewHas('organizations', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 1)
        ->call('showListing', 'mine')->assertViewHas('organizations', fn ($rows) => $rows->currentPage() === 1 && $rows->isEmpty());
    Livewire::test(ExploreOrganizations::class)->set('filterCategory', 'komunitas')
        ->assertViewHas('organizations', fn ($rows) => $rows->pluck('id')->all() === [$this->org->id])
        ->set('search', 'Tidak ditemukan')->assertViewHas('organizations', fn ($rows) => $rows->isEmpty());
});

it('preserves old links to the account list and creation form', function () {
    $this->actingAs($this->other)->get('/organizations?listing=mine')->assertRedirect(route('personal.organizations'));
    $this->get('/organizations?listing=mine&create=1')->assertRedirect(route('personal.organizations', ['create' => 1]));
});
