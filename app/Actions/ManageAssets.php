<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Borrowing;
use App\Models\Organization;
use App\Models\User;

class ManageAssets extends WorkspaceAction
{
    public function update(User $actor, Organization $org, int $id, array $data): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id, $data) {
            $asset = Asset::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            AssetCategory::where('organization_id', $org->id)->findOrFail($data['asset_category_id']);
            $asset->update(array_intersect_key($data, array_flip(['name', 'asset_category_id', 'description', 'specifications', 'location', 'photo', 'purchase_date'])));
            $this->log($actor, $org, $asset, 'updated');
            $this->audit($actor, $org, 'asset.updated', $asset);
        });
    }

    public function delete(User $actor, Organization $org, int $id): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id) {
            $asset = Asset::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            $this->check(in_array($asset->status, ['available', 'lost']), 'Aset memiliki aktivitas operasional aktif.');
            Borrowing::where('asset_id', $asset->id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_by' => $actor->id, 'cancelled_at' => now(), 'cancellation_reason' => 'Aset dihapus']);
            $asset->delete();
            $this->log($actor, $org, $asset, 'deleted');
            $this->audit($actor, $org, 'asset.deleted', $asset);
        });
    }

    public function found(User $actor, Organization $org, int $id, string $condition, string $notes): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id, $condition, $notes) {
            $asset = Asset::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            $this->check($asset->status === 'lost' && in_array($condition, ['good', 'minor_damage']) && mb_strlen(trim($notes)) >= 3, 'Aset harus hilang, kondisi laik, dan catatan wajib.');
            $asset->update(['status' => 'available', 'physical_condition' => $condition]);
            $this->log($actor, $org, $asset, 'marked_found');
            $this->audit($actor, $org, 'asset.found', $asset);
        });
    }
}
