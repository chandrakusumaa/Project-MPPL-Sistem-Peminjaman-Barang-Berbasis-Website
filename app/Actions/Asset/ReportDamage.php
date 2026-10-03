<?php

namespace App\Actions\Asset;

use App\Enums\AssetLogEvent;
use App\Enums\DamageReportStatus;
use App\Events\DamageReported;
use App\Models\Asset;
use App\Models\DamageReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportDamage
{
    public function execute(Asset $asset, User $reportedBy, array $data, ?int $borrowingId = null): DamageReport
    {
        return DB::transaction(function () use ($asset, $reportedBy, $data, $borrowingId) {
            $report = DamageReport::create([
                'organization_id' => $asset->organization_id,
                'asset_id' => $asset->id,
                'borrowing_id' => $borrowingId,
                'reported_by' => $reportedBy->id,
                'description' => $data['description'],
                'photo' => $data['photo'] ?? null,
                'status' => DamageReportStatus::OPEN,
            ]);

            $asset->logs()->create([
                'organization_id' => $asset->organization_id,
                'user_id' => $reportedBy->id,
                'event' => AssetLogEvent::DAMAGE_REPORTED,
                'description' => "Kerusakan dilaporkan: {$data['description']}",
                'metadata' => ['damage_report_id' => $report->id],
            ]);

            DamageReported::dispatch($report);

            return $report;
        });
    }
}
