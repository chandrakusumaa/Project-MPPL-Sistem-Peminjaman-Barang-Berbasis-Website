<?php

use App\Models\AssetLog;
use App\Models\AuditLog;
use App\Models\Borrowing;
use App\Models\Organization;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inventory:overdue', function () {
    $count = 0;
    foreach (Organization::whereNull('archived_at')->orderBy('id')->get() as $org) {
        DB::transaction(function () use ($org, &$count) {
            $org = Organization::whereKey($org->id)->lockForUpdate()->firstOrFail();
            if ($org->archived_at) {
                return;
            }
            foreach (Borrowing::where('organization_id', $org->id)->where('status', 'borrowed')->where('due_date', '<', now('Asia/Jakarta')->toDateString())->lockForUpdate()->get() as $b) {
                $b->update(['status' => 'overdue', 'overdue_at' => now()]);
                $count++;
                AssetLog::firstOrCreate(['event_key' => 'borrowing:'.$b->id.':overdue'], ['organization_id' => $org->id, 'asset_id' => $b->asset_id, 'borrowing_id' => $b->id, 'event' => 'overdue', 'description' => 'Peminjaman melewati jatuh tempo']);
                AuditLog::firstOrCreate(['event_key' => 'borrowing:'.$b->id.':overdue'], ['organization_id' => $org->id, 'action' => 'borrowing.overdue', 'subject_type' => 'Borrowing', 'subject_id' => $b->id]);
            }
        }, 3);
    }
    $this->info('Ditandai overdue: '.$count);
});
Schedule::command('inventory:overdue')->everyMinute()->withoutOverlapping();
