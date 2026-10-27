<?php

use App\Actions\BorrowAsset;
use App\Actions\SettleCharge;
use App\Models\Asset;
use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\ChargePayment;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Process\Process;

beforeEach(function () {
    Notification::fake();
    $this->seed();
    $this->admin = User::where('email', 'admin@example.test')->firstOrFail();
    $this->staff = User::where('email', 'staff@example.test')->firstOrFail();
    $this->member = User::where('email', 'member@example.test')->firstOrFail();
    $this->org = Organization::firstOrFail();
    $this->asset = Asset::firstOrFail();
    $b = app(BorrowAsset::class)->request($this->member, $this->org, $this->asset->id, ['borrow_date' => now('Asia/Jakarta')->toDateString(), 'due_date' => now('Asia/Jakarta')->addDay()->toDateString(), 'reason' => 'Dokumentasi kegiatan bersama', 'acknowledged' => true]);
    app(BorrowAsset::class)->decide($this->admin, $this->org, $b->id, 'approve');
    $this->charge = BorrowerCharge::create(['organization_id' => $this->org->id, 'borrowing_id' => $b->id, 'asset_id' => $b->asset_id, 'borrower_id' => $b->user_id, 'charge_type' => 'late_fee', 'amount' => 150000, 'status' => 'issued', 'reason' => 'Pengujian race', 'policy_basis' => 'Fixture', 'issued_at' => now()->subMinute(), 'idempotency_key' => 'race-charge']);
    foreach ([1, 2] as $n) {
        app(SettleCharge::class)->change($this->staff, $this->org, $this->charge->id, 'payment', ['amount' => 100000, 'method' => 'cash', 'notes' => 'Diterima', 'paid_at' => now(), 'idempotency_key' => 'p'.$n]);
    }
    $this->payments = $this->charge->payments()->orderBy('id')->get();
});
function runFinanceRace($t, array $operations): array
{
    $barrier = tempnam(sys_get_temp_dir(), 'inventory-race-');
    unlink($barrier);
    $processes = [];
    foreach ($operations as [$action,$target,$amount]) {
        $p = new Process([PHP_BINARY, base_path('tests/Support/financial-worker.php'), (string) $t->admin->id, (string) $t->org->id, (string) $t->charge->id, $action, (string) $target, (string) $amount, $barrier], base_path(), ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => 'orginventory-db', 'DB_DATABASE' => 'orginventory_test', 'DB_USERNAME' => 'inventory', 'DB_PASSWORD' => 'local-development-only', 'QUEUE_CONNECTION' => 'database', 'MAIL_MAILER' => 'log']);
        $p->setTimeout(30);
        $p->start();
        $processes[] = $p;
    }
    touch($barrier);
    $results = [];
    foreach ($processes as $p) {
        $p->wait();
        $results[] = $p->getExitCode();
        if (! in_array($p->getExitCode(), [0, 2])) {
            throw new RuntimeException($p->getErrorOutput().$p->getOutput());
        }
    }unlink($barrier);
    sort($results);

    return $results;
}
it('prevents overpayment under two real simultaneous MySQL confirmations', function () {
    expect(runFinanceRace($this, [['confirm', $this->payments[0]->id, 0], ['confirm', $this->payments[1]->id, 0]]))->toBe([0, 2]);
    expect($this->charge->balance)->toBe(50000);
    expect($this->charge->payments()->where('status', 'confirmed')->count())->toBe(1);
});
it('serializes a credit racing against a payment confirmation', function () {
    expect(runFinanceRace($this, [['confirm', $this->payments[0]->id, 0], ['credit', 0, 100000]]))->toBe([0, 2]);
    expect($this->charge->balance)->toBe(50000);
});
it('makes two concurrent void attempts preserve one payment history', function () {
    app(SettleCharge::class)->change($this->admin, $this->org, $this->charge->id, 'confirm', ['payment_id' => $this->payments[0]->id]);
    expect(runFinanceRace($this, [['void', $this->payments[0]->id, 0], ['void', $this->payments[0]->id, 0]]))->toBe([0, 0]);
    expect($this->charge->balance)->toBe(150000)->and(ChargePayment::count())->toBe(2);
});
it('keeps an open charge from being orphaned by concurrent archive and issuance', function () {
    $this->charge->update(['status' => 'draft', 'issued_at' => null]);
    expect(runFinanceRace($this, [['issue', 0, 0], ['archive', 0, 0]]))->toBe([0, 2]);
    expect($this->org->fresh()->archived_at)->toBeNull()->and($this->charge->fresh()->status)->toBe('issued');
});
it('allows only one of two simultaneous borrowing approvals', function () {
    $original = Borrowing::firstOrFail();
    app(BorrowAsset::class)->inspect($this->admin, $this->org, $original->id, ['condition' => 'good']);
    $requests = [];
    foreach ([$this->member, $this->staff] as $u) {
        $requests[] = app(BorrowAsset::class)->request($u, $this->org, $this->asset->id, ['borrow_date' => now('Asia/Jakarta')->toDateString(), 'due_date' => now('Asia/Jakarta')->addDay()->toDateString(), 'reason' => 'Permintaan peminjaman paralel', 'acknowledged' => true]);
    }
    expect(runFinanceRace($this, [['approve', $requests[0]->id, 0], ['approve', $requests[1]->id, 0]]))->toBe([0, 2]);
    expect(Borrowing::where('asset_id', $this->asset->id)->whereIn('status', ['borrowed', 'overdue'])->count())->toBe(1);
});
