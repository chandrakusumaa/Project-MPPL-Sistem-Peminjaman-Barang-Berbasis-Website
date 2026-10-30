<?php

use App\Actions\BorrowAsset;
use App\Actions\ManageWorkspace;
use App\Actions\SettleCharge;
use App\Livewire\Home;
use App\Livewire\PersonalCharges;
use App\Livewire\PersonalDashboard;
use App\Models\Asset;
use App\Models\BorrowerCharge;
use App\Models\ChargePayment;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    $this->seed();
    $this->admin = User::where('email', 'admin@example.test')->firstOrFail();
    $this->member = User::where('email', 'member@example.test')->firstOrFail();
    $this->staff = User::where('email', 'staff@example.test')->firstOrFail();
    $this->other = User::where('email', 'other@example.test')->firstOrFail();
    $this->org = Organization::firstOrFail();
});

function personalRequest($test, User $user, string $name)
{
    $asset = Asset::create(['organization_id' => $test->org->id, 'asset_category_id' => $test->org->assets()->firstOrFail()->asset_category_id, 'name' => $name, 'code' => (string) Str::ulid(), 'location' => 'Ruang uji', 'status' => 'available', 'physical_condition' => 'good']);

    return app(BorrowAsset::class)->request($user, $test->org, $asset->id, ['borrow_date' => today('Asia/Jakarta')->toDateString(), 'due_date' => today('Asia/Jakarta')->addDays(2)->toDateString(), 'reason' => 'Peminjaman untuk pengujian akun', 'acknowledged' => true]);
}

function personalCharge($test, int $amount, string $reason, ?User $borrower = null, string $status = 'issued'): BorrowerCharge
{
    $borrower ??= $test->member;
    $borrowing = personalRequest($test, $borrower, $reason);
    app(BorrowAsset::class)->decide($test->admin, $test->org, $borrowing->id, 'approve');

    return BorrowerCharge::create([
        'organization_id' => $test->org->id, 'borrowing_id' => $borrowing->id, 'asset_id' => $borrowing->asset_id,
        'borrower_id' => $borrower->id, 'charge_type' => 'late_fee', 'amount' => $amount, 'status' => $status,
        'reason' => $reason, 'policy_basis' => 'Tarif keterlambatan untuk fixture pengujian', 'issued_at' => now()->subMinute(),
        'cancelled_by' => $status === 'cancelled' ? $test->admin->id : null,
        'cancelled_at' => $status === 'cancelled' ? now() : null,
        'cancellation_reason' => $status === 'cancelled' ? 'Dibatalkan untuk pengujian' : null,
        'idempotency_key' => (string) Str::uuid(),
    ]);
}

function personalPayment($test, BorrowerCharge $charge, int $amount, string $status = 'confirmed'): ChargePayment
{
    $key = (string) Str::uuid();
    $settle = app(SettleCharge::class);
    $settle->change($test->staff, $test->org, $charge->id, 'payment', ['amount' => $amount, 'method' => 'cash', 'notes' => 'Diterima pengelola untuk '.$charge->reason, 'paid_at' => now(), 'idempotency_key' => $key]);
    $payment = ChargePayment::where('idempotency_key', $key)->firstOrFail();
    if (in_array($status, ['confirmed', 'voided'])) {
        $settle->change($test->admin, $test->org, $charge->id, 'confirm', ['payment_id' => $payment->id]);
    }
    if ($status === 'voided') {
        $settle->change($test->admin, $test->org, $charge->id, 'void', ['payment_id' => $payment->id, 'reason' => 'Koreksi pembayaran uji']);
    }

    return $payment->fresh();
}

it('separates the personal dashboard, discovery, and landing for signed-in users', function () {
    $this->actingAs($this->admin)->get('/dashboard')->assertOk()->assertSeeLivewire(PersonalDashboard::class)->assertSee('Peminjaman terbaru')->assertDontSee('wire:submit="create"', false);
    $this->get('/organizations')->assertOk()->assertSeeLivewire(Home::class)->assertSee('Cari Organization')->assertDontSee('Peminjaman terbaru');
    $this->get('/')->assertOk()->assertSee('Barang tertata.')->assertDontSee('id="account-sidebar"', false);
    $this->get('/my-charges')->assertOk()->assertSeeLivewire(PersonalCharges::class);
});

it('shows only the accounts own borrowing activity and active organizations', function () {
    $followed = app(ManageWorkspace::class)->create($this->other, ['name' => 'Organization yang diikuti', 'category' => 'komunitas']);
    Membership::create(['organization_id' => $followed->id, 'user_id' => $this->admin->id, 'role' => 'member', 'status' => 'active', 'joined_at' => now()]);
    app(ManageWorkspace::class)->create($this->other, ['name' => 'Organization rahasia', 'category' => 'lainnya']);
    personalRequest($this, $this->admin, 'Kamera menunggu');
    $active = personalRequest($this, $this->admin, 'Laptop berjalan');
    app(BorrowAsset::class)->decide($this->admin, $this->org, $active->id, 'approve');
    $returned = personalRequest($this, $this->admin, 'Tripod kembali');
    app(BorrowAsset::class)->decide($this->admin, $this->org, $returned->id, 'approve');
    app(BorrowAsset::class)->inspect($this->staff, $this->org, $returned->id, ['condition' => 'good']);
    personalRequest($this, $this->member, 'Aset peminjam lain');

    $component = Livewire::actingAs($this->admin)->test(PersonalDashboard::class)
        ->assertViewHas('summary', ['organizations' => 2, 'borrowings' => 3, 'active' => 1, 'pending' => 1])
        ->assertSee('Dibuat olehmu')->assertSee('Diikuti')->assertSee('Organization yang diikuti')
        ->assertSee('Kamera menunggu')->assertSee('Laptop berjalan')->assertSee('Tripod kembali')
        ->assertDontSee('Organization rahasia')->assertDontSee('Aset peminjam lain');
    $component->set('status', 'pending')->assertViewHas('borrowings', fn ($rows) => $rows->count() === 1 && $rows->first()->status === 'pending');
    expect(Membership::where('user_id', $this->admin->id)->where('organization_id', $followed->id)->value('role'))->toBe('member');
});

it('provides an empty personal dashboard and protects account routes before verification', function () {
    Livewire::actingAs($this->other)->test(PersonalDashboard::class)
        ->assertViewHas('summary', ['organizations' => 0, 'borrowings' => 0, 'active' => 0, 'pending' => 0])
        ->assertSee('Belum mengikuti Organization')->assertSee('Belum ada peminjaman');
    $user = User::factory()->unverified()->create();
    foreach (['/dashboard', '/my-charges'] as $path) {
        $this->actingAs($user)->get($path)->assertRedirect(route('verification.notice'));
    }
});

it('uses a collapsible organization sidebar and keeps notifications beside the profile', function () {
    $this->admin->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['title' => 'Notifikasi milikku']]);
    $this->other->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['title' => 'Notifikasi rahasia akun lain']]);
    $response = $this->actingAs($this->admin)->get('/dashboard')->assertOk()->assertSee('Notifikasi milikku')->assertDontSee('Notifikasi rahasia akun lain');
    preg_match('/<aside\b.*?<\/aside>/s', $response->getContent(), $sidebar);
    expect($sidebar[0])->toContain('oi-org-menu', 'Organization', '/my-charges')->not->toContain('/notifications');
    $response->assertSeeInOrder(['Navigasi utama', 'Beranda', 'Jelajahi Organization', 'Dashboard', 'aria-label="Notifikasi"', 'aria-label="Profil Raka"'], false);
});

it('calculates personal balances using confirmed payments and credits without exposing other borrowers', function () {
    $partial = personalCharge($this, 150000, 'Denda parsial pribadi');
    personalPayment($this, $partial, 50000);
    personalPayment($this, $partial, 60000, 'pending');
    personalPayment($this, $partial, 10000, 'voided');
    app(SettleCharge::class)->change($this->admin, $this->org, $partial->id, 'credit', ['amount' => 20000, 'reason' => 'Pembebasan parsial', 'idempotency_key' => 'personal-credit']);
    $paid = personalCharge($this, 80000, 'Denda sudah lunas');
    personalPayment($this, $paid, 80000);
    $waived = personalCharge($this, 30000, 'Denda dibebaskan');
    app(SettleCharge::class)->change($this->admin, $this->org, $waived->id, 'credit', ['amount' => 30000, 'reason' => 'Pembebasan penuh', 'idempotency_key' => 'personal-waived']);
    personalCharge($this, 400000, 'Denda dibatalkan', status: 'cancelled');
    personalCharge($this, 900000, 'Denda draft', status: 'draft');
    $foreign = personalCharge($this, 700000, 'Rahasia peminjam lain', $this->admin);
    // This payment belongs to a different borrower; it must not appear in this account.
    ChargePayment::create(['charge_id' => $foreign->id, 'organization_id' => $this->org->id, 'borrower_id' => $this->admin->id, 'amount' => 10000, 'method' => 'cash', 'paid_at' => now(), 'notes' => 'Pembayaran rahasia', 'status' => 'pending', 'recorded_by' => $this->staff->id, 'idempotency_key' => 'foreign-payment']);

    $component = Livewire::actingAs($this->member)->test(PersonalCharges::class)
        ->assertViewHas('summary', fn ($summary) => (int) $summary->total === 260000 && (int) $summary->paid === 130000 && (int) $summary->credited === 50000 && (int) $summary->outstanding === 80000)
        ->assertViewHas('pendingPayments', fn ($amount) => (int) $amount === 60000)
        ->assertSee('Rp80.000')->assertSee('Sebagian dibayar')->assertSee('Lunas')->assertSee('Dibebaskan')->assertSee('Koreksi pembayaran uji')
        ->assertDontSee('Rahasia peminjam lain')->assertDontSee('Pembayaran rahasia');
    $component->set('status', 'paid')->assertViewHas('charges', fn ($rows) => $rows->pluck('id')->all() === [$paid->id]);
    $component->set('status', 'outstanding')->assertViewHas('charges', fn ($rows) => $rows->pluck('id')->all() === [$partial->id]);
    $component->set('paymentStatus', 'pending')->assertViewHas('payments', fn ($rows) => $rows->count() === 1 && $rows->first()->status === 'pending');
    $component->set('type', 'damage')->assertViewHas('charges', fn ($rows) => $rows->isEmpty());
});

it('paginates charges and payments independently and resets only the filtered list', function () {
    $charge = personalCharge($this, 500000, 'Denda dengan banyak pembayaran');
    for ($i = 0; $i < 11; $i++) {
        personalPayment($this, $charge, 1000, 'pending');
    }
    Livewire::actingAs($this->member)->test(PersonalCharges::class)
        ->call('setPage', 2, 'paymentsPage')
        ->assertViewHas('payments', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 1)
        ->set('status', 'outstanding')
        ->assertViewHas('payments', fn ($rows) => $rows->currentPage() === 2)
        ->set('paymentStatus', 'pending')
        ->assertViewHas('payments', fn ($rows) => $rows->currentPage() === 1 && $rows->count() === 10);
});

it('keeps validation and membership action feedback visible during Livewire updates', function () {
    Livewire::actingAs($this->other)->test(Home::class)->set('creating', true)->call('create')
        ->assertHasErrors(['name' => 'required'])->assertSee(__('validation.required', ['attribute' => 'name']));
    Livewire::actingAs($this->other)->test(Home::class)->call('join', $this->org->id)
        ->assertSee('Pengajuan bergabung dikirim.')->call('cancelJoin', $this->org->id)->assertSee('Pengajuan dibatalkan.');
});
