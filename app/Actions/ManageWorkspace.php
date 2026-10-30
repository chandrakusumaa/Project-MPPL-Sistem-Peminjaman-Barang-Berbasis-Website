<?php

namespace App\Actions;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Membership;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public function join(User $user, Organization $org): void
    {
        DB::transaction(function () use ($user, $org) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $org = Organization::whereKey($org->id)->lockForUpdate()->firstOrFail();
            $this->check(! $org->archived_at, 'Organization diarsipkan.');
            $this->check(! Membership::where('organization_id', $org->id)->where('user_id', $user->id)->where('status', 'active')->exists(), 'Anda sudah menjadi anggota.');
            MembershipRequest::firstOrCreate(['organization_id' => $org->id, 'user_id' => $user->id, 'status' => 'pending']);
        }, 3);
    }

    public function review(User $actor, Organization $org, int $id, bool $approve): void
    {
        $this->transact($actor, $org, 'administer', function ($org) use ($actor, $id, $approve) {
            $request = MembershipRequest::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            $this->check($request->status === 'pending', 'Pengajuan sudah diproses.');
            if ($approve) {
                User::findOrFail($request->user_id);
                $member = Membership::firstOrNew(['organization_id' => $org->id, 'user_id' => $request->user_id]);
                if (! $member->exists || $member->status !== 'active') {
                    $member->fill(['role' => 'member', 'status' => 'active', 'joined_at' => now(), 'revoked_at' => null, 'revoked_by' => null])->save();
                }
            }
            $request->update(['status' => $approve ? 'approved' : 'rejected', 'reviewed_by' => $actor->id, 'reviewed_at' => now()]);
            $this->audit($actor, $org, 'membership.reviewed', $request);
        });
    }

    public function category(User $actor, Organization $org, string $name): void
    {
        $this->transact($actor, $org, 'operate', function ($org) use ($name) {
            AssetCategory::firstOrCreate(['organization_id' => $org->id, 'name' => trim($name)]);
        });
    }

    public function cancelJoin(User $actor, Organization $org): void
    {
        DB::transaction(function () use ($actor, $org) {
            $org = Organization::whereKey($org->id)->lockForUpdate()->firstOrFail();
            $this->check(! $org->archived_at, 'Organization diarsipkan.');
            MembershipRequest::where('organization_id', $org->id)->where('user_id', $actor->id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_by' => $actor->id, 'cancelled_at' => now()]);
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
