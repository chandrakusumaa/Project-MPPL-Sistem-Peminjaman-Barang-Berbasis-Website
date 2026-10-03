<?php

namespace App\Events;

use App\Models\Asset;
use App\Models\DamageReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceFinished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DamageReport $report, public Asset $asset) {}
}
