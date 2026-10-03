<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use Exception;

class DeleteOrganization
{
    /**
     * Soft delete an organization.
     */
    public function execute(Organization $organization): void
    {
        // Check for active borrowings
        $activeBorrowings = $organization->borrowings()
            ->whereIn('status', ['borrowed', 'overdue'])
            ->exists();

        if ($activeBorrowings) {
            throw new Exception('Tidak bisa menghapus organisasi karena masih ada peminjaman aktif.');
        }

        $organization->delete(); // Soft delete
    }
}
