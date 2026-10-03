<?php

namespace App\Actions\Asset;

use App\Enums\AssetLogEvent;
use App\Enums\AssetStatus;
use App\Events\AssetMarkedFound;
use App\Models\Asset;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class MarkAssetFound
{
    public function execute(Asset $asset, User $handledBy, array $data): Asset
    {
        return DB::transaction(function () use ($asset, $handledBy, $data) {
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if ($asset->status !== AssetStatus::LOST) {
                throw new Exception('Status aset bukan Hilang (Lost).');
            }

            $asset->update([
                'status' => AssetStatus::AVAILABLE,
            ]);

            $asset->logs()->create([
                'organization_id' => $asset->organization_id,
                'user_id' => $handledBy->id,
                'event' => AssetLogEvent::MARKED_FOUND,
                'description' => 'Aset ditandai ditemukan. Catatan: '.($data['notes'] ?? '-'),
                'metadata' => [],
            ]);

            AssetMarkedFound::dispatch($asset, $handledBy);

            return $asset->fresh();
        });
    }
}
