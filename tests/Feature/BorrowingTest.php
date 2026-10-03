<?php

use App\Actions\Borrowing\ApproveBorrowing;
use App\Actions\Borrowing\RequestBorrowing;
use App\Enums\AssetStatus;
use App\Enums\BorrowingStatus;
use App\Enums\ReturnCondition;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Borrowing;
use App\Models\DamageReport;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->org = Organization::factory()->create(['max_borrow_days' => 7, 'late_fine_per_day' => 5000]);
    $this->member = User::factory()->create();
    $this->admin = User::factory()->create();

    $this->org->users()->attach($this->member->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);
    $this->org->users()->attach($this->admin->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);

    $this->category = AssetCategory::create(['organization_id' => $this->org->id, 'name' => 'Tech']);
    $this->asset = Asset::factory()->create(['organization_id' => $this->org->id, 'asset_category_id' => $this->category->id, 'status' => AssetStatus::AVAILABLE]);
});

test('member can request borrowing', function () {
    Volt::test('catalog.show', ['organization' => $this->org, 'asset' => $this->asset])
        ->actingAs($this->member)
        ->set('borrow_date', date('Y-m-d'))
        ->set('due_date', date('Y-m-d', strtotime('+3 days')))
        ->set('reason', 'Untuk kegiatan divisi')
        ->call('submitBorrowRequest')
        ->assertRedirect(route('my-borrowings.index', ['tab' => 'pending']));

    expect(Borrowing::where('user_id', $this->member->id)->where('status', BorrowingStatus::PENDING)->exists())->toBeTrue();
});

test('cannot request if duration exceeds max_borrow_days', function () {
    Volt::test('catalog.show', ['organization' => $this->org, 'asset' => $this->asset])
        ->actingAs($this->member)
        ->set('borrow_date', date('Y-m-d'))
        ->set('due_date', date('Y-m-d', strtotime('+10 days'))) // > 7
        ->set('reason', 'Untuk kegiatan panjang')
        ->call('submitBorrowRequest')
        ->assertHasErrors(['reason']);

    expect(Borrowing::count())->toBe(0);
});

test('admin can approve borrowing', function () {
    $borrowing = app(RequestBorrowing::class)->execute($this->org, $this->asset, $this->member, [
        'borrow_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+3 days')),
        'reason' => 'Keperluan tim',
    ]);

    Volt::test('manage.borrowings.show', ['organization' => $this->org, 'borrowing' => $borrowing])
        ->actingAs($this->admin)
        ->call('approve');

    $borrowing->refresh();
    $this->asset->refresh();

    expect($borrowing->status)->toBe(BorrowingStatus::BORROWED);
    expect($borrowing->approved_by)->toBe($this->admin->id);
    expect($this->asset->status)->toBe(AssetStatus::BORROWED);
});

test('admin can reject borrowing', function () {
    $borrowing = app(RequestBorrowing::class)->execute($this->org, $this->asset, $this->member, [
        'borrow_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+3 days')),
        'reason' => 'Keperluan tim',
    ]);

    Volt::test('manage.borrowings.show', ['organization' => $this->org, 'borrowing' => $borrowing])
        ->actingAs($this->admin)
        ->set('rejectionReason', 'Aset sedang akan diservis')
        ->call('reject');

    $borrowing->refresh();
    expect($borrowing->status)->toBe(BorrowingStatus::REJECTED);
    expect($borrowing->rejection_reason)->toBe('Aset sedang akan diservis');
    // Asset should still be AVAILABLE
    expect($this->asset->fresh()->status)->toBe(AssetStatus::AVAILABLE);
});

test('member can cancel pending request', function () {
    $borrowing = app(RequestBorrowing::class)->execute($this->org, $this->asset, $this->member, [
        'borrow_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+3 days')),
        'reason' => 'Keperluan tim',
    ]);

    Volt::test('my-borrowings.show', ['borrowing' => $borrowing])
        ->actingAs($this->member)
        ->call('cancelRequest');

    expect($borrowing->fresh()->status)->toBe(BorrowingStatus::CANCELLED);
});

test('process return with minor damage creates report and keeps asset available', function () {
    $borrowing = app(RequestBorrowing::class)->execute($this->org, $this->asset, $this->member, [
        'borrow_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+3 days')),
        'reason' => 'Keperluan tim',
    ]);

    app(ApproveBorrowing::class)->execute($borrowing, $this->admin);

    Volt::test('manage.borrowings.show', ['organization' => $this->org, 'borrowing' => $borrowing])
        ->actingAs($this->admin)
        ->set('returnCondition', ReturnCondition::MINOR_DAMAGE->value)
        ->set('returnNotes', 'Layar sedikit tergores')
        ->set('fineAmount', 0)
        ->call('processReturn');

    $borrowing->refresh();
    $this->asset->refresh();

    expect($borrowing->status)->toBe(BorrowingStatus::RETURNED);
    expect($this->asset->status)->toBe(AssetStatus::AVAILABLE);
    expect(DamageReport::where('asset_id', $this->asset->id)->count())->toBe(1);
});

test('overdue command marks past due borrowings', function () {
    $borrowing = app(RequestBorrowing::class)->execute($this->org, $this->asset, $this->member, [
        'borrow_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        'due_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
        'reason' => 'Keperluan tim',
    ]);

    app(ApproveBorrowing::class)->execute($borrowing, $this->admin);

    // Artificially change due_date backwards via DB to bypass any validations
    $borrowing->update(['due_date' => Carbon::yesterday()->subDay()]);

    Artisan::call('borrowings:mark-overdue');

    expect($borrowing->fresh()->status)->toBe(BorrowingStatus::OVERDUE);
});

test('fine is calculated correctly for overdue return', function () {
    $borrowing = app(RequestBorrowing::class)->execute($this->org, $this->asset, $this->member, [
        'borrow_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        'due_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
        'reason' => 'Keperluan tim',
    ]);

    app(ApproveBorrowing::class)->execute($borrowing, $this->admin);
    $borrowing->update(['due_date' => Carbon::today()->subDays(3)]);
    $borrowing->update(['status' => BorrowingStatus::OVERDUE]);

    Volt::test('manage.borrowings.show', ['organization' => $this->org, 'borrowing' => $borrowing])
        ->actingAs($this->admin)
        ->assertSet('fineAmount', 15000); // 3 days * 5000
});
