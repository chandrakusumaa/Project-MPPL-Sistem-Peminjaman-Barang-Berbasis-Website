<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model (settings).
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can manage reports.
     */
    public function manageReports(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can access manage area (dashboard, inventory, etc).
     */
    public function manage(User $user, Organization $organization): bool
    {
        if (!$organization->exists) {
            throw new \Exception("Organization does not exist! ID is null.");
        }
        return $user->canManage($organization); // Admin or Staff
    }

    /**
     * Determine whether the user can access the catalog.
     */
    public function accessCatalog(User $user, Organization $organization): bool
    {
        return $user->isMemberOf($organization);
    }
}
