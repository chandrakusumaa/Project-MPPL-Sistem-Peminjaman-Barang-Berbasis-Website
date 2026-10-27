<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\DamageReport;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class MaintainAsset extends WorkspaceAction
{
    public function report(User $actor, Organization $org, int $assetId, string $description, ?string $photo = null, ?int $borrowingId = null): void
    {
        $this->transact($actor, $org, 'view', function ($org) use ($actor, $assetId, $description, $photo, $borrowingId) {
            $asset = Asset::where('organization_id', $org->id)->lockForUpdate()->findOrFail($assetId);
            $this->check(mb_strlen(trim($description)) >= 10, 'Uraian minimal 10 karakter.');
            $operator = Gate::forUser($actor)->allows('operate', $org);
            if ($borrowingId) {
                $borrowing = Borrowing::where('organization_id', $org->id)->where('asset_id', $assetId)->findOrFail($borrowingId);
                $this->check($operator || $borrowing->user_id === $actor->id, 'Peminjaman bukan milik Anda.');
            }
            $r = DamageReport::create(['organization_id' => $org->id, 'asset_id' => $asset->id, 'borrowing_id' => $borrowingId, 'source' => $operator ? 'staff_report' : 'member_report', 'reported_by' => $actor->id, 'description' => $description, 'photo' => $photo, 'status' => 'open']);
            $this->log($actor, $org, $asset, 'damage_reported');
            $this->audit($actor, $org, 'damage.reported', $r);
        });
    }

    public function handle(User $actor, Organization $org, int $id, string $action, array $data): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id, $action, $data) {
            $found = DamageReport::where('organization_id', $org->id)->findOrFail($id);
            $asset = Asset::whereKey($found->asset_id)->lockForUpdate()->firstOrFail();
            $r = DamageReport::whereKey($id)->lockForUpdate()->firstOrFail();
            $notes = trim($data['notes']);
            $this->check(mb_strlen($notes) >= 3, 'Catatan wajib.');
            if ($action === 'finish') {
                $this->check($r->status === 'in_maintenance' && $asset->status === 'maintenance', 'Maintenance tidak aktif.');
                $this->check(in_array($data['condition'], ['good', 'minor_damage']), 'Kondisi akhir harus good/minor_damage.');
                $r->update(['status' => 'resolved', 'resolved_at' => now(), 'resolution_notes' => $notes, 'handled_by' => $actor->id]);
                $asset->update(['status' => 'available', 'physical_condition' => $data['condition']]);
            } else {
                $this->check($r->status === 'open', 'Laporan sudah ditindaklanjuti.');
                $severity = $data['severity'];
                $this->check(in_array($severity, ['minor', 'major']), 'Severity wajib dinilai.');
                $maintenance = $action === 'maintain';
                $this->check(! $maintenance || $asset->status === 'available', 'Maintenance hanya dapat dimulai dari aset tersedia.');
                $this->check($maintenance || $severity !== 'major' || $asset->status !== 'available', 'Kerusakan major wajib masuk maintenance.');
                $r->update(['severity' => $severity, 'assessed_at' => now(), 'assessment_notes' => $notes, 'handled_by' => $actor->id, 'status' => $maintenance ? 'in_maintenance' : 'noted', 'maintenance_started_at' => $maintenance ? now() : null, 'resolution_notes' => $maintenance ? null : $notes]);
                $asset->update(['status' => $maintenance ? 'maintenance' : $asset->status, 'physical_condition' => $asset->status === 'lost' ? 'unknown' : ($severity === 'major' ? 'major_damage' : 'minor_damage')]);
            }
            $this->log($actor, $org, $asset, 'maintenance_'.$action);
            $this->audit($actor, $org, 'damage.'.$action, $r);
        });
    }
}
