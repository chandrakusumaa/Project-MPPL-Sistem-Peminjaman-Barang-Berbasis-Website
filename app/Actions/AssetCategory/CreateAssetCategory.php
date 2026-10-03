<?php

namespace App\Actions\AssetCategory;

use App\Models\AssetCategory;
use App\Models\Organization;

class CreateAssetCategory
{
    public function execute(Organization $organization, array $data): AssetCategory
    {
        return $organization->assetCategories()->create([
            'name' => $data['name'],
        ]);
    }
}
