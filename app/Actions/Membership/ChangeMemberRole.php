<?php

namespace App\Actions\Membership;

use App\Enums\Role;
use App\Events\MemberRoleChanged;
use App\Models\Organization;
use App\Models\User;
use Exception;

class ChangeMemberRole
{
    /**
     * Change a member's role in the organization.
     */
    public function execute(Organization $organization, User $member, string $newRole): void
    {
        $currentRole = $member->roleIn($organization);

        if (! $currentRole) {
            throw new Exception('User is not a member of this organization.');
        }

        if ($currentRole->value === $newRole) {
            return;
        }

        // Check if removing the last admin
        if ($currentRole === Role::ADMIN && $newRole !== Role::ADMIN->value) {
            $adminCount = $organization->users()->wherePivot('role', Role::ADMIN->value)->count();
            if ($adminCount <= 1) {
                throw new Exception('Tidak bisa mengubah role Admin terakhir.');
            }
        }

        $organization->users()->updateExistingPivot($member->id, ['role' => $newRole]);

        event(new MemberRoleChanged($organization, $member, $newRole));
    }
}
