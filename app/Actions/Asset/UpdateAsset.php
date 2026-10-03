<?php

namespace App\Actions\Asset;

use App\Models\Asset;
use App\Models\User;

class UpdateAsset
{
    public function execute(Asset $asset, User $user, array $data): Asset
    {
        $asset->update([
            'asset_category_id' => $data['asset_category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'specifications' => $data['specifications'],
            'location' => $data['location'],
            'purchase_date' => $data['purchase_date'] ?? null,
        ]);

        if (array_key_exists('photo', $data)) {
            $asset->update(['photo' => $data['photo']]);
        }

        $asset->logs()->create([
            'organization_id' => $asset->organization_id,
            'user_id' => $user->id,
            'event' => 'updated',
            'description' => 'Data aset diperbarui.',
        ]);

        return $asset;
    }
}
