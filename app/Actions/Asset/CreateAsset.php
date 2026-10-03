<?php

namespace App\Actions\Asset;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;

class CreateAsset
{
    public function execute(Organization $organization, User $user, array $data): Asset
    {
        $asset = $organization->assets()->create([
            'asset_category_id' => $data['asset_category_id'],
            'code' => Str::ulid()->toString(),
            'name' => $data['name'],
            'description' => $data['description'],
            'specifications' => $data['specifications'],
            'location' => $data['location'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'photo' => $data['photo'] ?? null,
            'status' => AssetStatus::AVAILABLE,
        ]);

        $asset->logs()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'event' => 'created',
            'description' => 'Aset didaftarkan ke sistem.',
        ]);

        return $asset;
    }
}
