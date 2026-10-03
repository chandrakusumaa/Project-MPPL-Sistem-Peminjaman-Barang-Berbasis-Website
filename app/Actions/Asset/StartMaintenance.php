<?php

namespace App\Actions\Asset;

use App\Enums\AssetLogEvent;
use App\Enums\AssetStatus;
use App\Enums\DamageReportStatus;
use App\Events\MaintenanceStarted;
use App\Models\Asset;
use App\Models\DamageReport;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class StartMaintenance
{
    public function execute(DamageReport $report, User $handledBy, array $data = []): DamageReport
    {
        return DB::transaction(function () use ($report, $handledBy, $data) {
            // Lock the asset row to prevent concurrent updates
            $asset = Asset::where('id', $report->asset_id)->lockForUpdate()->firstOrFail();

            if ($asset->status === AssetStatus::BORROWED) {
                throw new Exception("Aset sedang dipinjam (status: {$asset->status->value}). Maintenance tidak dapat dimulai sebelum dikembalikan.");
            }

            // Update Report
            $report->update([
                'status' => DamageReportStatus::IN_MAINTENANCE,
                'severity' => $data['severity'] ?? $report->severity,
                'resolution_notes' => $data['resolution_notes'] ?? $report->resolution_notes,
                'handled_by' => $handledBy->id,
            ]);

            // Update Asset
            $asset->update([
                'status' => AssetStatus::MAINTENANCE,
            ]);

            // Log
            $asset->logs()->create([
                'organization_id' => $asset->organization_id,
                'user_id' => $handledBy->id,
                'event' => AssetLogEvent::MAINTENANCE_STARTED,
                'description' => 'Perbaikan dimulai dari laporan kerusakan #'.$report->id,
                'metadata' => ['damage_report_id' => $report->id],
            ]);

            MaintenanceStarted::dispatch($report, $asset);

            return $report->fresh();
        });
    }
}
