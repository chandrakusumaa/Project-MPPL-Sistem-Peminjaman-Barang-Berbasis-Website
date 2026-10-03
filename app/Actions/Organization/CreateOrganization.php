<?php

namespace App\Actions\Organization;

use App\Enums\Role;
use App\Events\OrganizationCreated;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;

class CreateOrganization
{
    /**
     * Create a new organization and assign the creator as Admin.
     */
    public function execute(User $user, array $data): Organization
    {
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(6);
        $data['created_by'] = $user->id;
        $data['max_borrow_days'] = 7;
        $data['late_fine_per_day'] = 0;

        $organization = Organization::create($data);

        // Attach creator as admin
        $organization->users()->attach($user->id, [
            'role' => Role::ADMIN->value,
            'joined_at' => now(),
        ]);

        event(new OrganizationCreated($organization));

        return $organization;
    }
}
