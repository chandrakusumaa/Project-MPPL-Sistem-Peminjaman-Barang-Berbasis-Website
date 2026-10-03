<?php

namespace App\Actions\AssetCategory;

use App\Models\AssetCategory;

class UpdateAssetCategory
{
    public function execute(AssetCategory $category, array $data): AssetCategory
    {
        $category->update([
            'name' => $data['name'],
        ]);

        return $category;
    }
}
