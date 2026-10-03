<?php

namespace App\Policies;

use App\Models\Borrowing;
use App\Models\Organization;
use App\Models\User;

class BorrowingPolicy
{
    /**
     * Determine whether the user can view any borrowings of the given organization.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        // Any member of the organization can list its borrowings.
        return $user->isMemberOf($organization);
    }

    /**
     * Determine whether the user can view a specific borrowing.
     */
    public function view(User $user, Borrowing $borrowing): bool
    {
        // Owner can view; admin/staff of the same organization can also view.
        return $borrowing->user_id === $user->id ||
            $user->hasRoleIn($borrowing->organization, ['admin', 'staff']);
    }

    /**
     * Determine whether the user can create a borrowing request.
     */
    public function create(User $user, Organization $organization): bool
    {
        // Only members of the organization can request a borrowing.
        return $user->isMemberOf($organization);
    }

    /**
     * Determine whether the user can approve or reject a borrowing (update).
     */
    public function update(User $user, Borrowing $borrowing): bool
    {
        // Only admin or staff of the borrowing's organization can approve/reject.
        return $user->hasRoleIn($borrowing->organization, ['admin', 'staff']);
    }

    /**
     * Determine whether the user can delete a borrowing.
     */
    public function delete(User $user, Borrowing $borrowing): bool
    {
        $terminal = in_array(
            $borrowing->status->value,
            [
                \App\Enums\BorrowingStatus::REJECTED->value,
                \App\Enums\BorrowingStatus::CANCELLED->value,
                \App\Enums\BorrowingStatus::RETURNED->value,
            ]
        );

        if ($terminal) {
            return $user->hasRoleIn($borrowing->organization, ['admin']);
        }

        // When pending, the owner may cancel the request.
        return $borrowing->user_id === $user->id &&
            $borrowing->status->value === \App\Enums\BorrowingStatus::PENDING->value;
    }

    /**
     * Determine whether the user can restore a soft‑deleted borrowing.
     */
    public function restore(User $user, Borrowing $borrowing): bool
    {
        return $user->hasRoleIn($borrowing->organization, ['admin']);
    }

    /**
     * Determine whether the user can permanently delete a borrowing.
     */
    public function forceDelete(User $user, Borrowing $borrowing): bool
    {
        return $user->hasRoleIn($borrowing->organization, ['admin']);
    }
}
