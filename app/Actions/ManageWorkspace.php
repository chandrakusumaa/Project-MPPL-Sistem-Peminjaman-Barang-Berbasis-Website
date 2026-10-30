<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ManageWorkspace extends WorkspaceAction
{
    public function create(User $user, array $data): Organization
    {
        $this->check($user->hasVerifiedEmail(), 'Verifikasi email diperlukan.');

        return DB::transaction(function () use ($user, $data) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $org = Organization::create($data + ['slug' => Str::slug($data['name']).'-'.strtolower(Str::random(8)), 'created_by' => $user->id, 'max_borrow_days' => 7, 'late_fine_per_day' => 0]);
            Membership::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'admin', 'status' => 'active', 'joined_at' => now()]);
            $this->audit($user, $org, 'organization.created', $org);

            return $org;
        }, 3);
    }

    public function join(User $user, Organization $org): Membership
    {
        $key = 'organization-join:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw new TooManyRequestsHttpException(RateLimiter::availableIn($key), 'Terlalu banyak percobaan Gabung. Coba lagi sebentar.');
        }
        RateLimiter::hit($key, 60);

        return DB::transaction(function () use ($user, $org) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $org = Organization::whereKey($org->id)->lockForUpdate()->firstOrFail();
            $membership = Membership::where('organization_id', $org->id)->where('user_id', $user->id)->lockForUpdate()->first();
            Gate::forUser($user)->authorize('join', $org);
            if ($membership) {
                // Active membership keeps its existing role and original timestamps.
                return $membership;
            }
            $membership = Membership::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'member', 'status' => 'active', 'joined_at' => now()]);
            $this->audit($user, $org, 'membership.joined', $membership);

            return $membership;
        }, 3);
    }

    public function category(User $actor, Organization $org, string $name): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($name) {
            AssetCategory::firstOrCreate(['organization_id' => $org->id, 'name' => trim($name)]);
        });
    }

    public function categoryRename(User $actor, Organization $org, int $id, string $name): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id, $name) {
            $cat = AssetCategory::where('organization_id', $org->id)->findOrFail($id);
            $this->check(! AssetCategory::where('organization_id', $org->id)->where('id', '!=', $id)->where('name', trim($name))->exists(), 'Nama kategori sudah digunakan.');
            $cat->update(['name' => trim($name)]);
            $this->audit($actor, $org, 'category.renamed', $cat);
        });
    }

    public function categoryDelete(User $actor, Organization $org, int $id): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($actor, $id) {
            $cat = AssetCategory::where('organization_id', $org->id)->findOrFail($id);
            $this->check(! Asset::withTrashed()->where('asset_category_id', $cat->id)->exists(), 'Kategori masih direferensikan aset/histori.');
            $cat->delete();
            $this->audit($actor, $org, 'category.deleted', $cat);
        });
    }

    public function asset(User $actor, Organization $org, array $data): Asset
    {
        return $this->transact($actor, $org, 'operate', function ($org) use ($actor, $data) {
            AssetCategory::where('organization_id', $org->id)->findOrFail($data['asset_category_id']);
            $asset = Asset::create($data + ['organization_id' => $org->id, 'code' => (string) Str::ulid(), 'status' => 'available', 'physical_condition' => 'good']);
            $this->log($actor, $org, $asset, 'created');
            $this->audit($actor, $org, 'asset.created', $asset);

            return $asset;
        });
    }
}
