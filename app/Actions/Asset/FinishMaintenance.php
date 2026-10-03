<?php

namespace App\Actions\Asset;

use App\Enums\AssetLogEvent;
use App\Enums\AssetStatus;
use App\Enums\DamageReportStatus;
use App\Events\MaintenanceFinished;
use App\Models\Asset;
use App\Models\DamageReport;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class FinishMaintenance
{
    public function execute(DamageReport $report, User $handledBy, array $data): DamageReport
    {
        return DB::transaction(function () use ($report, $handledBy, $data) {
            $asset = Asset::where('id', $report->asset_id)->lockForUpdate()->firstOrFail();

            if ($report->status !== DamageReportStatus::IN_MAINTENANCE) {
                throw new Exception('Laporan belum dalam status Maintenance.');
            }

            // Update Report
            $report->update([
                'status' => DamageReportStatus::RESOLVED,
                'resolution_notes' => $data['resolution_notes'] ?? $report->resolution_notes,
                'resolved_at' => now(),
                'handled_by' => $handledBy->id,
            ]);

            // Update Asset back to AVAILABLE
            $asset->update([
                'status' => AssetStatus::AVAILABLE,
            ]);

            // Log
            $asset->logs()->create([
                'organization_id' => $asset->organization_id,
                'user_id' => $handledBy->id,
                'event' => AssetLogEvent::MAINTENANCE_FINISHED,
                'description' => 'Perbaikan selesai. Catatan: '.($data['resolution_notes'] ?? '-'),
                'metadata' => ['damage_report_id' => $report->id],
            ]);

            MaintenanceFinished::dispatch($report, $asset);

            return $report->fresh();
        });
    }
}
