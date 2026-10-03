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
        // Prevent deletion if there are active borrowings
        if ($organization->borrowings()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return false;
        }
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        // Ensure at least one admin remains after removal
        if ($user->isAdminOf($organization)) {
            $adminCount = $organization->admins()->count();
            // If this admin is the only one, disallow removal/demotion
            if ($adminCount <= 1 && request()->routeIs('manage.members.remove')) {
                return false;
            }
        }
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
     * Determine whether the user can export reports (assets, borrowings, damage).
     */
    public function exportReports(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    /**
     * Determine whether the user can access manage area (dashboard, inventory, etc).
     */
    public function manage(User $user, Organization $organization): bool
    {
        if (! $organization->exists) {
            return false;
        }
        return $user->canManage($organization); // Admin or Staff
    }

    /**
     * Determine whether the user can delete an asset.
     */
    public function deleteAsset(User $user, \App\Models\Asset $asset): bool
    {
        // Only Admin or Staff can delete, and only when asset is AVAILABLE
        if (! $user->canManage($asset->organization)) {
            return false;
        }
        return $asset->status->isAvailable();
    }

    /**
     * Determine whether the user can access the catalog.
     */
    public function accessCatalog(User $user, Organization $organization): bool
    {
        return $user->isMemberOf($organization);
    }
}
