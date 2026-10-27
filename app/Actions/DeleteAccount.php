<?php

namespace App\Actions;

use App\Models\BorrowerCharge;
use App\Models\Borrowing;
use App\Models\Membership;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteAccount extends WorkspaceAction
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $ids = Membership::where('user_id', $user->id)->pluck('organization_id')->merge(MembershipRequest::where('user_id', $user->id)->where('status', 'pending')->pluck('organization_id'))->merge(BorrowerCharge::where('borrower_id', $user->id)->pluck('organization_id'))->unique();
            $orgs = Organization::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            foreach ($orgs as $org) {
                $m = Membership::where('organization_id', $org->id)->where('user_id', $user->id)->lockForUpdate()->first();
                $this->check(! ($m?->status === 'active' && $m->role === 'admin' && Membership::where('organization_id', $org->id)->where('status', 'active')->where('role', 'admin')->count() === 1), 'Anda adalah Admin aktif terakhir.');
                $this->check(! app(ManageMembers::class)->obligations($org, $user->id), 'Akun masih memiliki kewajiban terbuka.');
                $m?->update(['status' => 'revoked', 'revoked_at' => now(), 'revoked_by' => $user->id]);
                Borrowing::where('organization_id', $org->id)->where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $user->id, 'cancellation_reason' => 'Akun dihapus']);
                MembershipRequest::where('organization_id', $org->id)->where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $user->id]);
                $this->audit($user, $org, 'account.deleted', $user);
            }
            $user->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }, 3);
    }
}
