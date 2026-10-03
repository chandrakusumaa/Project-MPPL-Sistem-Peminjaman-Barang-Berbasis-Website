<?php

namespace App\Actions\Organization;

use App\Models\Organization;

class UpdateOrganizationRules
{
    /**
     * Update organization borrowing rules.
     */
    public function execute(Organization $organization, array $data): Organization
    {
        $organization->update([
            'max_borrow_days' => $data['max_borrow_days'],
            'late_fine_per_day' => $data['late_fine_per_day'],
        ]);

        return $organization;
    }
}
