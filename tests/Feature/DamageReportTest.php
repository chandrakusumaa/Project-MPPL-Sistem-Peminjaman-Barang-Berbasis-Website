<?php

use App\Actions\Asset\AssessDamageReport;
use App\Actions\Asset\FinishMaintenance;
use App\Actions\Asset\MarkAssetFound;
use App\Actions\Asset\ReportDamage;
use App\Actions\Asset\StartMaintenance;
use App\Enums\AssetStatus;
use App\Enums\DamageReportStatus;
use App\Enums\DamageSeverity;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\DamageReport;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->member = User::factory()->create();
    $this->nonMember = User::factory()->create();

    $this->organization = Organization::factory()->create([
        'created_by' => $this->admin->id,
    ]);

    $this->organization->users()->attach($this->admin->id, ['role' => 'admin']);
    $this->organization->users()->attach($this->member->id, ['role' => 'member']);

    $this->category = AssetCategory::factory()->create([
        'organization_id' => $this->organization->id,
        'name' => 'Kategori Uji',
    ]);

    $this->asset = Asset::factory()->create([
        'organization_id' => $this->organization->id,
        'asset_category_id' => $this->category->id,
        'code' => 'AST-001',
        'name' => 'Aset Uji',
        'description' => 'Aset untuk pengujian',
        'specifications' => 'Spek',
        'location' => 'Gudang',
        'status' => AssetStatus::AVAILABLE,
    ]);
});

test('member can report damage', function () {
    $action = new ReportDamage;

    $report = $action->execute($this->asset, $this->member, [
        'description' => 'Layar retak',
    ]);

    expect($report->status)->toBe(DamageReportStatus::OPEN)
        ->and($report->description)->toBe('Layar retak')
        ->and($report->reported_by)->toBe($this->member->id);

    $this->assertDatabaseHas('damage_reports', [
        'id' => $report->id,
        'status' => 'open',
    ]);

    $this->assertDatabaseHas('asset_logs', [
        'asset_id' => $this->asset->id,
        'event' => 'damage_reported',
    ]);
});

test('admin can assess damage report', function () {
    $report = DamageReport::create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'description' => 'Layar retak',
        'status' => DamageReportStatus::OPEN,
    ]);

    $action = new AssessDamageReport;
    $action->execute($report, $this->admin, [
        'severity' => DamageSeverity::MINOR,
        'resolution_notes' => 'Akan diperiksa',
    ]);

    $report->refresh();

    expect($report->severity)->toBe(DamageSeverity::MINOR)
        ->and($report->resolution_notes)->toBe('Akan diperiksa')
        ->and($report->handled_by)->toBe($this->admin->id);
});

test('admin can start maintenance and it updates asset status', function () {
    $report = DamageReport::create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'description' => 'Layar retak',
        'status' => DamageReportStatus::OPEN,
    ]);

    $action = new StartMaintenance;
    $action->execute($report, $this->admin, [
        'severity' => DamageSeverity::MAJOR,
        'resolution_notes' => 'Kirim ke bengkel',
    ]);

    $report->refresh();
    $this->asset->refresh();

    expect($report->status)->toBe(DamageReportStatus::IN_MAINTENANCE)
        ->and($this->asset->status)->toBe(AssetStatus::MAINTENANCE);

    $this->assertDatabaseHas('asset_logs', [
        'asset_id' => $this->asset->id,
        'event' => 'maintenance_started',
    ]);
});

test('maintenance cannot start if asset is borrowed', function () {
    $this->asset->update(['status' => AssetStatus::BORROWED]);

    $report = DamageReport::create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'description' => 'Layar retak',
        'status' => DamageReportStatus::OPEN,
    ]);

    $action = new StartMaintenance;

    expect(fn () => $action->execute($report, $this->admin, []))
        ->toThrow(Exception::class, 'Aset sedang dipinjam');
});

test('admin can finish maintenance', function () {
    $this->asset->update(['status' => AssetStatus::MAINTENANCE]);

    $report = DamageReport::create([
        'organization_id' => $this->organization->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->member->id,
        'description' => 'Layar retak',
        'status' => DamageReportStatus::IN_MAINTENANCE,
    ]);

    $action = new FinishMaintenance;
    $action->execute($report, $this->admin, [
        'resolution_notes' => 'Selesai diperbaiki',
    ]);

    $report->refresh();
    $this->asset->refresh();

    expect($report->status)->toBe(DamageReportStatus::RESOLVED)
        ->and($this->asset->status)->toBe(AssetStatus::AVAILABLE);

    $this->assertDatabaseHas('asset_logs', [
        'asset_id' => $this->asset->id,
        'event' => 'maintenance_finished',
    ]);
});

test('admin can mark asset found', function () {
    $this->asset->update(['status' => AssetStatus::LOST]);

    $action = new MarkAssetFound;
    $action->execute($this->asset, $this->admin, [
        'notes' => 'Ditemukan di gudang',
    ]);

    $this->asset->refresh();

    expect($this->asset->status)->toBe(AssetStatus::AVAILABLE);

    $this->assertDatabaseHas('asset_logs', [
        'asset_id' => $this->asset->id,
        'event' => 'marked_found',
    ]);
});
