<?php

namespace App\Actions;

use App\Enums\BorrowingStatus;
use App\Models\Asset;
use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\DamageReport;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Carbon\CarbonImmutable;

class BorrowAsset extends WorkspaceAction
{
    public function request(User $actor, Organization $org, int $assetId, array $data): Borrowing
    {
        return $this->transact($actor, $org, 'view', function ($org) use ($actor, $assetId, $data) {
            $asset = Asset::where('organization_id', $org->id)->lockForUpdate()->findOrFail($assetId);
            $start = CarbonImmutable::parse($data['borrow_date'], 'Asia/Jakarta')->startOfDay();
            $due = CarbonImmutable::parse($data['due_date'], 'Asia/Jakarta')->startOfDay();
            $this->check($asset->status === 'available', 'Aset tidak tersedia.');
            $this->check($start->gte(CarbonImmutable::today('Asia/Jakarta')) && $due->gt($start) && $start->diffInDays($due) <= $org->max_borrow_days, 'Tanggal atau durasi pinjaman tidak valid.');
            $this->check(mb_strlen(trim($data['reason'])) >= 10, 'Alasan minimal 10 karakter.');
            $this->check(! $org->damage_charge_policy || ($data['acknowledged'] ?? false), 'Setujui kebijakan ruang sebelum mengajukan.');
            $this->check(! Borrowing::where('asset_id', $assetId)->where('user_id', $actor->id)->whereIn('status', ['pending', 'borrowed', 'overdue'])->exists(), 'Sudah ada pengajuan aktif.');
            $b = Borrowing::create(['organization_id' => $org->id, 'asset_id' => $asset->id, 'user_id' => $actor->id, 'status' => BorrowingStatus::Pending->value, 'borrow_date' => $start->toDateString(), 'due_date' => $due->toDateString(), 'reason' => trim($data['reason']), 'damage_policy_snapshot' => $org->damage_charge_policy, 'damage_policy_acknowledged_at' => $org->damage_charge_policy ? now() : null]);
            $this->log($actor, $org, $asset, 'borrow_requested', $b->id);
            $this->audit($actor, $org, 'borrowing.requested', $b);

            return $b;
        });
    }

    public function decide(User $actor, Organization $org, int $id, string $decision, string $reason = ''): void
    {
        $this->transact($actor, $org, $decision === 'cancel' ? 'view' : 'operate', function ($org) use ($actor, $id, $decision, $reason) {
            $found = Borrowing::where('organization_id', $org->id)->findOrFail($id);
            $asset = Asset::whereKey($found->asset_id)->lockForUpdate()->firstOrFail();
            $b = Borrowing::whereKey($id)->lockForUpdate()->firstOrFail();
            $this->check($b->status === 'pending', 'Pengajuan sudah diproses.');
            if ($decision === 'cancel') {
                $this->check($b->user_id === $actor->id, 'Hanya peminjam dapat membatalkan.');
                $b->update(['status' => 'cancelled', 'cancelled_by' => $actor->id, 'cancelled_at' => now(), 'cancellation_reason' => 'Dibatalkan peminjam']);
            } elseif ($decision === 'approve') {
                $this->check($asset->status === 'available', 'Aset tidak tersedia.');
                $this->check($b->borrow_date->toDateString() === CarbonImmutable::today('Asia/Jakarta')->toDateString(), 'Persetujuan hanya pada tanggal mulai.');
                $this->check(Membership::where('organization_id', $org->id)->where('user_id', $b->user_id)->where('status', 'active')->exists(), 'Peminjam bukan anggota aktif.');
                $this->check($b->damage_policy_snapshot === $org->damage_charge_policy, 'Kebijakan berubah. Batalkan dan ajukan ulang dengan persetujuan terbaru.');
                $this->check($b->borrow_date->diffInDays($b->due_date) <= $org->max_borrow_days, 'Durasi melebihi kebijakan terbaru.');
                $b->update(['status' => 'borrowed', 'approved_by' => $actor->id, 'approved_at' => now(), 'max_borrow_days_snapshot' => $org->max_borrow_days, 'late_fine_per_day_snapshot' => $org->late_fine_per_day]);
                $asset->update(['status' => 'borrowed']);
            } else {
                $this->check(mb_strlen(trim($reason)) >= 3, 'Alasan penolakan wajib.');
                $b->update(['status' => 'rejected', 'rejected_by' => $actor->id, 'rejected_at' => now(), 'rejection_reason' => $reason]);
            }
            $this->log($actor, $org, $asset, 'borrow_'.$decision, $b->id);
            $this->audit($actor, $org, 'borrowing.'.$decision, $b);
        });
    }

    public function inspect(User $actor, Organization $org, int $id, array $data): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id, $data) {
            $found = Borrowing::where('organization_id', $org->id)->findOrFail($id);
            $asset = Asset::whereKey($found->asset_id)->lockForUpdate()->firstOrFail();
            $b = Borrowing::whereKey($id)->lockForUpdate()->firstOrFail();
            $this->check(in_array($b->status, ['borrowed', 'overdue']), 'Inspeksi telah selesai atau pinjaman tidak aktif.');
            $condition = $data['condition'];
            $this->check(in_array($condition, ['good', 'minor_damage', 'major_damage', 'lost']), 'Kondisi tidak valid.');
            $this->check($condition === 'good' || mb_strlen(trim($data['notes'] ?? '')) >= 3, 'Catatan inspeksi wajib.');
            $returned = CarbonImmutable::parse($data['returned_at'] ?? now(), 'UTC');
            $this->check($returned->lte(now()) && $returned->gte($b->approved_at), 'Waktu pengembalian tidak valid.');
            $days = max(0, (int) CarbonImmutable::parse($b->due_date->toDateString(), 'Asia/Jakarta')->diffInDays($returned->setTimezone('Asia/Jakarta')->startOfDay(), false));
            $calculated = $days * (int) $b->late_fine_per_day_snapshot;
            $fine = isset($data['fine_amount']) ? (int) $data['fine_amount'] : $calculated;
            $this->check($fine >= 0 && $fine <= 99999999999999, 'Denda tidak valid.');
            $this->check($fine === $calculated || mb_strlen(trim($data['fine_override_reason'] ?? '')) >= 3, 'Alasan override wajib.');
            $b->update(['status' => 'returned', 'returned_at' => $returned, 'processed_by' => $actor->id, 'return_processed_at' => now(), 'return_condition' => $condition, 'return_notes' => $data['notes'] ?? null, 'late_days' => $days, 'calculated_fine_amount' => $calculated, 'fine_amount' => $fine, 'fine_override_reason' => $data['fine_override_reason'] ?? null]);
            $asset->update(['status' => match ($condition) {
                'major_damage' => 'maintenance','lost' => 'lost',default => 'available'
            }, 'physical_condition' => $condition === 'lost' ? 'unknown' : $condition]);
            if (in_array($condition, ['minor_damage', 'major_damage'])) {
                DamageReport::create(['organization_id' => $org->id, 'asset_id' => $asset->id, 'borrowing_id' => $b->id, 'source' => 'return_inspection', 'reported_by' => $actor->id, 'description' => $data['notes'], 'severity' => $condition === 'major_damage' ? 'major' : 'minor', 'status' => $condition === 'major_damage' ? 'in_maintenance' : 'noted', 'handled_by' => $actor->id, 'assessed_at' => now(), 'assessment_notes' => $data['notes'], 'maintenance_started_at' => $condition === 'major_damage' ? now() : null, 'resolution_notes' => $condition === 'minor_damage' ? $data['notes'] : null]);
            }
            if ($fine > 0) {
                BorrowerCharge::create(['organization_id' => $org->id, 'borrowing_id' => $b->id, 'asset_id' => $asset->id, 'borrower_id' => $b->user_id, 'charge_type' => 'late_fee', 'amount' => $fine, 'status' => 'issued', 'reason' => 'Keterlambatan '.$days.' hari', 'policy_basis' => 'Tarif snapshot Rp'.$b->late_fine_per_day_snapshot, 'issued_at' => now(), 'idempotency_key' => 'borrowing:'.$b->id.':late_fee']);
            }
            $this->log($actor, $org, $asset, 'returned', $b->id);
            $this->audit($actor, $org, 'borrowing.inspected', $b);
        });
    }
}
