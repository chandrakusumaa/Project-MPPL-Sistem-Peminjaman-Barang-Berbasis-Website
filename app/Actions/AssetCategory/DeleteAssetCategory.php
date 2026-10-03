<?php

namespace App\Actions\AssetCategory;

use App\Models\AssetCategory;
use Exception;

class DeleteAssetCategory
{
    public function execute(AssetCategory $category): void
    {
        if ($category->assets()->exists()) {
            throw new Exception('Kategori tidak dapat dihapus karena masih digunakan oleh aset.');
        }

        $category->delete();
    }
}
