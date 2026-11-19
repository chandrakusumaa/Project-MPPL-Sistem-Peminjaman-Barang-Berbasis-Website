<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\ChargeCredit;
use App\Models\ChargePayment;
use App\Models\DamageReport;
use App\Models\Organization;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;

class SettleCharge extends WorkspaceAction
{
    /**
     * Terbitkan atau tetapkan biaya kerusakan langsung dari popup Laporan Kerusakan.
     */
    public function assignFromReport(User $actor, Organization $org, int $reportId, array $data): BorrowerCharge
    {
        return $this->transact($actor, $org, 'operate', function ($org) use ($actor, $reportId, $data) {
            $report = DamageReport::where('organization_id', $org->id)->lockForUpdate()->findOrFail($reportId);
            $asset = Asset::where('organization_id', $org->id)->lockForUpdate()->findOrFail($report->asset_id);

            $borrowingId = $report->borrowing_id ?: (int) ($data['borrowing_id'] ?? 0);
            if (! $borrowingId) {
                $latestBorrowing = Borrowing::where('organization_id', $org->id)
                    ->where('asset_id', $asset->id)
                    ->where('user_id', $report->reported_by)
                    ->latest('id')
                    ->first()
                    ?: Borrowing::where('organization_id', $org->id)
                        ->where('asset_id', $asset->id)
                        ->latest('id')
                        ->first();
                $borrowingId = (int) ($latestBorrowing?->id ?? 0);
            }
            $this->check($borrowingId > 0, 'Pilih peminjaman terkait terlebih dahulu untuk menetapkan biaya kerusakan.');

            $b = Borrowing::where('organization_id', $org->id)->where('asset_id', $asset->id)->lockForUpdate()->findOrFail($borrowingId);

            if (! $report->borrowing_id) {
                $report->update(['borrowing_id' => $b->id]);
            }

            $severity = in_array($data['severity'] ?? '', ['minor', 'major']) ? $data['severity'] : ($report->severity ?: 'minor');
            $notes = trim($data['reason'] ?? $report->description);
            $this->check(mb_strlen($notes) >= 10, 'Keterangan / alasan biaya kerusakan minimal 10 karakter.');

            if ($report->status === 'open') {
                $startMaintenance = ($data['start_maintenance'] ?? false) || ($severity === 'major' && $asset->status === 'available');
                $report->update([
                    'severity' => $severity,
                    'assessed_at' => now(),
                    'assessment_notes' => $notes,
                    'handled_by' => $actor->id,
                    'status' => $startMaintenance ? 'in_maintenance' : 'noted',
                    'maintenance_started_at' => $startMaintenance ? now() : null,
                    'resolution_notes' => $startMaintenance ? null : $notes,
                ]);
                $asset->update([
                    'status' => $startMaintenance ? 'maintenance' : $asset->status,
                    'physical_condition' => $asset->status === 'lost' ? 'unknown' : ($severity === 'major' ? 'major_damage' : 'minor_damage'),
                ]);
            } elseif ($report->severity === null) {
                $report->update([
                    'severity' => $severity,
                    'assessed_at' => now(),
                    'assessment_notes' => $notes,
                    'handled_by' => $actor->id,
                ]);
            }

            $chargeType = in_array($data['charge_type'] ?? 'damage', ['damage', 'loss']) ? $data['charge_type'] : 'damage';
            $amount = (int) ($data['amount'] ?? 0);
            $this->check($amount > 0, 'Nominal biaya kerusakan harus lebih dari Rp0.');

            $basis = trim($b->damage_policy_snapshot ?: ($org->damage_charge_policy ?: ($data['policy_basis'] ?? 'Penilaian laporan kerusakan aset')));
            $evidencePath = $data['evidence_path'] ?? $report->photo;

            $existingCharge = BorrowerCharge::where('organization_id', $org->id)
                ->where('borrowing_id', $b->id)
                ->whereIn('charge_type', ['damage', 'loss'])
                ->whereIn('status', ['draft', 'issued'])
                ->lockForUpdate()
                ->first();

            $canDirectlyIssue = Gate::forUser($actor)->allows('administer', $org)
                && $b->user_id !== $actor->id
                && (($b->damage_policy_snapshot && $b->damage_policy_acknowledged_at) || ! $org->damage_charge_policy);

            if ($existingCharge) {
                $this->check($existingCharge->status === 'draft', 'Tagihan untuk laporan ini sudah diterbitkan.');
                $existingCharge->update([
                    'damage_report_id' => $report->id,
                    'charge_type' => $chargeType,
                    'amount' => $amount,
                    'reason' => $notes,
                    'policy_basis' => $basis,
                    'evidence_path' => $evidencePath ?: $existingCharge->evidence_path,
                    'status' => $canDirectlyIssue ? 'issued' : 'draft',
                    'issued_by' => $canDirectlyIssue ? $actor->id : null,
                    'issued_at' => $canDirectlyIssue ? now() : null,
                ]);
                $this->audit($actor, $org, $canDirectlyIssue ? 'charge.issue' : 'charge.edit', $existingCharge);

                return $existingCharge;
            }

            $c = BorrowerCharge::create([
                'organization_id' => $org->id,
                'borrowing_id' => $b->id,
                'asset_id' => $b->asset_id,
                'borrower_id' => $b->user_id,
                'damage_report_id' => $report->id,
                'charge_type' => $chargeType,
                'amount' => $amount,
                'status' => $canDirectlyIssue ? 'issued' : 'draft',
                'reason' => $notes,
                'policy_basis' => $basis,
                'created_by' => $actor->id,
                'issued_by' => $canDirectlyIssue ? $actor->id : null,
                'issued_at' => $canDirectlyIssue ? now() : null,
                'evidence_path' => $evidencePath,
                'idempotency_key' => $data['idempotency_key'],
            ]);
            $this->audit($actor, $org, $canDirectlyIssue ? 'charge.issue' : 'charge.drafted', $c);

            return $c;
        });
    }

    public function draft(User $actor, Organization $org, int $borrowingId, array $data): BorrowerCharge
    {
        return $this->transact($actor, $org, 'operate', function ($org) use ($actor, $borrowingId, $data) {
            $b = Borrowing::where('organization_id', $org->id)->lockForUpdate()->findOrFail($borrowingId);
            $report = DamageReport::where('organization_id', $org->id)->where('borrowing_id', $b->id)->where('asset_id', $b->asset_id)->lockForUpdate()->findOrFail($data['damage_report_id']);
            $this->check(in_array($data['charge_type'], ['damage', 'loss']), 'Jenis tagihan tidak valid.');
            $existing = BorrowerCharge::where('organization_id', $org->id)->where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                $this->check($existing->borrowing_id === $b->id && (int) $existing->amount === (int) $data['amount'], 'Kunci idempotensi telah digunakan untuk input berbeda.');

                return $existing;
            }
            $this->check(! BorrowerCharge::where('borrowing_id', $b->id)->whereIn('charge_type', ['damage', 'loss'])->whereIn('status', ['draft', 'issued'])->exists(), 'Tagihan damage/loss aktif sudah ada.');
            $this->check((int) $data['amount'] > 0 && mb_strlen(trim($data['reason'])) >= 10, 'Nominal dan dasar tanggung jawab wajib valid.');
            $basis = $b->damage_policy_snapshot ?: trim($data['policy_basis'] ?? '');
            $this->check(mb_strlen($basis) > 0, 'Dasar kebijakan wajib. Transaksi lama memerlukan persetujuan peminjam sebelum penerbitan.');
            $this->check($report->severity !== null && ($report->photo || ($data['evidence_path'] ?? null)), 'Penilaian dan bukti privat diperlukan.');
            $c = BorrowerCharge::create(['organization_id' => $org->id, 'borrowing_id' => $b->id, 'asset_id' => $b->asset_id, 'borrower_id' => $b->user_id, 'damage_report_id' => $report->id, 'charge_type' => $data['charge_type'], 'amount' => (int) $data['amount'], 'status' => 'draft', 'reason' => $data['reason'], 'policy_basis' => $basis, 'created_by' => $actor->id, 'evidence_path' => $data['evidence_path'] ?? null, 'idempotency_key' => $data['idempotency_key']]);
            $this->audit($actor, $org, 'charge.drafted', $c);

            return $c;
        });
    }

    public function change(User $actor, Organization $org, int $id, string $action, array $data = []): void
    {
        $this->transact($actor, $org, $action === 'acknowledge' ? 'view' : (in_array($action, ['payment', 'edit']) ? 'operate' : 'administer'), function ($org) use ($actor, $id, $action, $data) {
            $c = BorrowerCharge::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            if ($action === 'acknowledge') {
                $this->check($c->borrower_id === $actor->id && $c->status === 'draft', 'Hanya peminjam dapat mengakui dasar kewajiban pada draft.');
                $c->update(['liability_acknowledged_at' => now()]);
                $this->audit($actor, $org, 'charge.liability_acknowledged', $c);

                return;
            }
            if (! in_array($action, ['payment', 'edit'])) {
                $this->check($c->borrower_id !== $actor->id, 'Admin tidak dapat menyetujui kewajiban finansial sendiri.');
            }
            if ($action === 'edit') {
                $this->check($c->status === 'draft', 'Hanya draft dapat diedit. Nominal issued tidak dapat diubah.');
                $this->check((int) $data['amount'] > 0 && mb_strlen(trim($data['reason'] ?? '')) >= 10, 'Nominal positif dan dasar nominal minimal 10 karakter wajib.');
                $c->update(['amount' => (int) $data['amount'], 'reason' => $data['reason'], 'liability_acknowledged_at' => null]);
            } elseif ($action === 'issue') {
                $this->check($c->status === 'draft', 'Hanya draft dapat diterbitkan.');
                $borrowing = Borrowing::findOrFail($c->borrowing_id);
                $this->check($c->charge_type === 'late_fee' || ($borrowing->damage_policy_snapshot && $borrowing->damage_policy_acknowledged_at) || $c->liability_acknowledged_at, 'Peminjam belum menyetujui dasar kebijakan transaksi lama.');
                $c->update(['status' => 'issued', 'issued_by' => $actor->id, 'issued_at' => now()]);
            } elseif ($action === 'cancel') {
                $this->check(in_array($c->status, ['draft', 'issued']) && ! $c->payments()->where('status', 'confirmed')->exists() && ! $c->credits()->exists(), 'Tagihan memiliki pembayaran sah/kredit atau sudah dibatalkan.');
                $this->check(mb_strlen(trim($data['reason'] ?? '')) >= 3, 'Alasan wajib.');
                $c->payments()->where('status', 'pending')->update(['status' => 'rejected', 'rejected_by' => $actor->id, 'rejected_at' => now(), 'rejection_reason' => 'Tagihan dibatalkan: '.$data['reason']]);
                $c->update(['status' => 'cancelled', 'cancelled_by' => $actor->id, 'cancelled_at' => now(), 'cancellation_reason' => $data['reason']]);
            } else {
                $this->check($c->status === 'issued', 'Tagihan belum diterbitkan atau telah dibatalkan.');
                if (in_array($action, ['payment', 'credit'])) {
                    $key = $data['idempotency_key'];
                    $model = $action === 'payment' ? ChargePayment::class : ChargeCredit::class;
                    $existing = $model::where('organization_id', $org->id)->where('idempotency_key', $key)->first();
                    if ($existing) {
                        $this->check($existing->charge_id === $c->id && (int) $existing->amount === (int) $data['amount'], 'Kunci idempotensi dipakai untuk input berbeda.');

                        return;
                    }
                    $amount = (int) $data['amount'];
                    $this->check($amount > 0 && $amount <= $c->balance, 'Nominal harus positif dan tidak melebihi sisa.');
                    $base = ['organization_id' => $org->id, 'charge_id' => $c->id, 'borrower_id' => $c->borrower_id, 'amount' => $amount, 'idempotency_key' => $key];
                    if ($action === 'credit') {
                        $this->check(mb_strlen(trim($data['reason'] ?? '')) >= 3, 'Alasan pembebasan wajib.');
                        ChargeCredit::create($base + ['reason' => $data['reason'], 'created_by' => $actor->id]);
                    } else {
                        $paid = CarbonImmutable::parse($data['paid_at'], 'UTC');
                        $this->check($paid->lte(now()) && $paid->gte(CarbonImmutable::parse($c->issued_at)), 'Waktu pembayaran tidak valid.');
                        $method = $data['method'];
                        $this->check(in_array($method, ['cash', 'bank_transfer', 'other']), 'Metode tidak valid.');
                        $this->check($method === 'bank_transfer' ? (! empty($data['reference']) || ! empty($data['evidence_path'])) : ! empty(trim($data['notes'] ?? '')), 'Referensi/bukti transfer atau catatan penerimaan wajib.');
                        ChargePayment::create($base + ['method' => $method, 'paid_at' => $paid, 'reference' => $data['reference'] ?? null, 'evidence_path' => $data['evidence_path'] ?? null, 'notes' => $data['notes'] ?? null, 'status' => 'pending', 'recorded_by' => $actor->id]);
                    }
                } else {
                    $p = $c->payments()->lockForUpdate()->findOrFail($data['payment_id']);
                    if ($action === 'confirm') {
                        if ($p->status === 'confirmed') {
                            return;
                        }
                        $this->check($p->status === 'pending' && (int) $p->amount <= $c->balance, 'Pembayaran tidak pending atau melebihi sisa.');
                        $p->update(['status' => 'confirmed', 'confirmed_by' => $actor->id, 'confirmed_at' => now()]);
                    } elseif ($action === 'reject') {
                        $this->check($p->status === 'pending' && mb_strlen(trim($data['reason'] ?? '')) >= 3, 'Pembayaran tidak pending atau alasan kosong.');
                        $p->update(['status' => 'rejected', 'rejected_by' => $actor->id, 'rejected_at' => now(), 'rejection_reason' => $data['reason']]);
                    } elseif ($action === 'void') {
                        if ($p->status === 'voided') {
                            return;
                        }
                        $this->check($p->status === 'confirmed' && mb_strlen(trim($data['reason'] ?? '')) >= 3, 'Pembayaran tidak confirmed atau alasan kosong.');
                        $p->update(['status' => 'voided', 'voided_by' => $actor->id, 'voided_at' => now(), 'void_reason' => $data['reason']]);
                    } else {
                        $this->check(false, 'Tindakan tidak valid.');
                    }
                }
            }
            $this->audit($actor, $org, 'charge.'.$action, $c);
        });
    }
}
