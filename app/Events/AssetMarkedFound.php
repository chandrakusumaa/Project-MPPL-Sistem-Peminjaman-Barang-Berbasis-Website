<?php

namespace App\Events;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetMarkedFound
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Asset $asset, public User $foundBy) {}
}
