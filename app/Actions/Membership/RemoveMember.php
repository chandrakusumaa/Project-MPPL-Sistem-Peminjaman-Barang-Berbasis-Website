<?php

namespace App\Actions\Membership;

use App\Enums\Role;
use App\Events\MemberRemoved;
use App\Models\Organization;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class RemoveMember
{
    /**
     * Remove a member from the organization.
     */
    public function execute(Organization $organization, User $member): void
    {
        $currentRole = $member->roleIn($organization);

        if (! $currentRole) {
            throw new Exception('User is not a member of this organization.');
        }

        // Check if removing the last admin
        if ($currentRole === Role::ADMIN) {
            $adminCount = $organization->users()->wherePivot('role', Role::ADMIN->value)->count();
            if ($adminCount <= 1) {
                throw new Exception('Tidak bisa menghapus Admin terakhir.');
            }
        }

        // Check for active borrowings
        $activeBorrowings = $organization->borrowings()
            ->where('user_id', $member->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->exists();

        if ($activeBorrowings) {
            throw new Exception('Anggota masih memiliki peminjaman aktif.');
        }

        DB::transaction(function () use ($organization, $member) {
            // Cancel pending requests
            $organization->borrowings()
                ->where('user_id', $member->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Remove user from organization
            $organization->users()->detach($member->id);
        });

        event(new MemberRemoved($organization, $member));
    }
}
