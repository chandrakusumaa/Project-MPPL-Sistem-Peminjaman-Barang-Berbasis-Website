<?php

namespace App\Events;

use App\Models\DamageReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DamageReported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DamageReport $report) {}
}
