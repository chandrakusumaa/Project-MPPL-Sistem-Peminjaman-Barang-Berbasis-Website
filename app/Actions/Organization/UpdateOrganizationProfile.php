<?php

namespace App\Actions\Organization;

use App\Models\Organization;

class UpdateOrganizationProfile
{
    /**
     * Update organization profile (name, description, category, logo).
     */
    public function execute(Organization $organization, array $data): Organization
    {
        $organization->update($data);

        return $organization;
    }
}
