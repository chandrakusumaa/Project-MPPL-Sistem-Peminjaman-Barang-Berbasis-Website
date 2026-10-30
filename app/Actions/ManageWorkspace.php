<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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

    /**
     * Bergabung langsung sebagai Member. Role awal selalu 'member' dan tidak dapat diberikan pemanggil.
     * Idempoten untuk anggota aktif (role dan joined_at tidak berubah); membership revoked ditolak.
     * Mengembalikan Membership; gunakan ->wasRecentlyCreated untuk membedakan join baru.
     */
    public function join(User $user, Organization $org): Membership
    {
        return DB::transaction(function () use ($user, $org) {
            // Urutan lock sama dengan transact(): user lalu Organization lalu membership.
            $user = User::withTrashed()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $org = Organization::withTrashed()->whereKey($org->id)->lockForUpdate()->firstOrFail();
            $this->check(! $user->trashed() && ! $org->trashed(), 'Akun atau Organization tidak tersedia.');

            $membership = Membership::where('organization_id', $org->id)->where('user_id', $user->id)->lockForUpdate()->first();
            if ($membership?->status === 'active') {
                return $membership;
            }
            $this->check($membership === null, 'Keanggotaan Anda pada Organization ini telah dicabut. Hubungi Admin Organization.');
            $this->check($org->archived_at === null, 'Organization diarsipkan dan tidak menerima anggota baru.');
            Gate::forUser($user)->authorize('join', $org);

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
