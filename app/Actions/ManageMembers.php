<?php

namespace App\Actions;

use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\ChargePayment;
use App\Models\DamageReport;
use App\Models\Membership;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ManageMembers extends WorkspaceAction
{
    public function obligations(Organization $org, ?int $userId = null): bool
    {
        if (Borrowing::where('organization_id', $org->id)->when($userId, fn ($q) => $q->where('user_id', $userId))->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return true;
        }
        if (ChargePayment::where('organization_id', $org->id)->when($userId, fn ($q) => $q->where('borrower_id', $userId))->where('status', 'pending')->exists()) {
            return true;
        }
        foreach (BorrowerCharge::where('organization_id', $org->id)->when($userId, fn ($q) => $q->where('borrower_id', $userId))->whereIn('status', ['draft', 'issued'])->get() as $c) {
            if ($c->status === 'draft' || $c->balance > 0) {
                return true;
            }
        }

        return false;
    }

    public function change(User $actor, Organization $org, int $id, string $role): void
    {
        $this->transact($actor, $org, 'administer', function ($org) use ($actor, $id, $role) {
            $this->check(in_array($role, ['admin', 'staff', 'member', 'revoked']), 'Role tidak valid.');
            $m = Membership::where('organization_id', $org->id)->lockForUpdate()->findOrFail($id);
            $this->check(! ($m->role === 'admin' && $m->status === 'active' && $role !== 'admin' && Membership::where('organization_id', $org->id)->where('role', 'admin')->where('status', 'active')->count() === 1), 'Admin aktif terakhir harus dipertahankan.');
            if ($role === 'revoked') {
                $this->check(! $this->obligations($org, $m->user_id), 'Anggota masih memiliki pinjaman/kewajiban terbuka.');
                $m->update(['status' => 'revoked', 'revoked_at' => now(), 'revoked_by' => $actor->id]);
                Borrowing::where('organization_id', $org->id)->where('user_id', $m->user_id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $actor->id, 'cancellation_reason' => 'Keanggotaan dicabut']);
            } else {
                $m->update(['role' => $role]);
            }
            $this->audit($actor, $org, 'membership.changed', $m);
        });
    }

    public function settings(User $actor, Organization $org, array $data): void
    {
        $this->transact($actor, $org, 'administer', function ($org) use ($actor, $data) {
            $org->update($data);
            $this->audit($actor, $org, 'organization.settings', $org);
        });
    }

    public function delete(User $actor, Organization $org, string $confirmation): void
    {
        $this->transact($actor, $org, 'administer', function ($org) use ($actor, $confirmation) {
            $this->check($confirmation === $org->name, 'Nama konfirmasi tidak cocok.');
            $this->check(! $this->obligations($org) && ! DamageReport::where('organization_id', $org->id)->where('status', 'in_maintenance')->exists(), 'Masih ada aktivitas atau kewajiban terbuka.');
            Borrowing::where('organization_id', $org->id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_by' => $actor->id, 'cancelled_at' => now(), 'cancellation_reason' => 'Ruang dihapus']);
            MembershipRequest::where('organization_id', $org->id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_by' => $actor->id, 'cancelled_at' => now()]);
            OrganizationInvitation::where('organization_id', $org->id)->where('status', 'pending')->update(['status' => 'revoked', 'revoked_by' => $actor->id, 'revoked_at' => now()]);
            $org->delete();
            $this->audit($actor, $org, 'organization.deleted', $org);
        });
    }

    public function archive(User $actor, Organization $org): void
    {
        if ($org->archived_at) {
            DB::transaction(function () use ($actor, $org) {
                User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
                $org = Organization::whereKey($org->id)->lockForUpdate()->firstOrFail();
                Gate::forUser($actor)->authorize('administer', $org);
                $org->update(['archived_at' => null]);
                $this->audit($actor, $org, 'organization.unarchived', $org);
            }, 3);

            return;
        }
        $this->transact($actor, $org, 'administer', function ($org) use ($actor) {
            $this->check(! $this->obligations($org) && ! DamageReport::where('organization_id', $org->id)->where('status', 'in_maintenance')->exists(), 'Masih ada pekerjaan/kewajiban terbuka.');
            $org->update(['archived_at' => now()]);
            $this->audit($actor, $org, 'organization.archived', $org);
        });
    }
}
