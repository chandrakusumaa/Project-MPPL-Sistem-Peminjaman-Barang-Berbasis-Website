<?php

namespace App\Actions\Organization;

use App\Models\Organization;

class ArchiveOrganization
{
    /**
     * Archive or unarchive an organization.
     */
    public function execute(Organization $organization, bool $archive = true): void
    {
        $organization->update([
            'archived_at' => $archive ? now() : null,
        ]);
    }
}
