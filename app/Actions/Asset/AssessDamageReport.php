<?php

namespace App\Actions\Asset;

use App\Models\DamageReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssessDamageReport
{
    public function execute(DamageReport $report, User $assessedBy, array $data): DamageReport
    {
        return DB::transaction(function () use ($report, $assessedBy, $data) {
            $report->update([
                'severity' => $data['severity'] ?? $report->severity,
                'resolution_notes' => $data['resolution_notes'] ?? $report->resolution_notes,
                'handled_by' => $assessedBy->id,
            ]);

            return $report->fresh();
        });
    }
}
