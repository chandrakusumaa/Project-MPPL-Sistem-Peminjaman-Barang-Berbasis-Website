<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function join(User $user, Organization $organization): bool
    {
        return ! $user->trashed() && $user->hasVerifiedEmail()
            && ! $organization->trashed() && ! $organization->archived_at
            && ! Membership::where('organization_id', $organization->id)->where('user_id', $user->id)->where('status', 'revoked')->exists();
    }

    public function view(User $user, Organization $organization): bool
    {
        return ! $user->trashed() && ! $organization->trashed() && Membership::where('organization_id', $organization->id)->where('user_id', $user->id)->where('status', 'active')->exists();
    }

    public function operate(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization) && Membership::where('organization_id', $organization->id)->where('user_id', $user->id)->whereIn('role', [Role::Admin->value, Role::Staff->value])->where('status', 'active')->exists();
    }

    public function administer(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization) && Membership::where('organization_id', $organization->id)->where('user_id', $user->id)->where('role', Role::Admin->value)->where('status', 'active')->exists();
    }
}
