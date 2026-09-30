<?php

namespace App\Actions\Organization;

use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrganizationAction
{
    /**
     * Execute the action to create an organization and assign creator as admin.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(User $creator, array $data): Organization
    {
        return DB::transaction(function () use ($creator, $data) {
            $slug = ! empty($data['slug'])
                ? Str::slug($data['slug'])
                : $this->generateUniqueSlug($data['name']);

            $organization = Organization::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'],
                'category' => $data['category'],
                'logo' => $data['logo'] ?? null,
                'max_borrow_days' => $data['max_borrow_days'] ?? 7,
                'late_fine_per_day' => $data['late_fine_per_day'] ?? 0,
                'created_by' => $creator->id,
            ]);

            $organization->users()->attach($creator->id, [
                'role' => Role::ADMIN->value,
                'joined_at' => now(),
            ]);

            return $organization;
        });
    }

    /**
     * Generate a unique slug based on organization name.
     */
    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Organization::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
