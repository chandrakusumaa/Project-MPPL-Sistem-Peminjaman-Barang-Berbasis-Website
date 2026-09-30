<?php

namespace Database\Seeders;

use App\Actions\Organization\CreateOrganizationAction;
use App\Enums\Role;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@inventory.test'],
            [
                'name' => 'Admin Pengelola',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $staffUser = User::firstOrCreate(
            ['email' => 'staff@inventory.test'],
            [
                'name' => 'Staff Operasional',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $memberUser = User::firstOrCreate(
            ['email' => 'member@inventory.test'],
            [
                'name' => 'Anggota Aktif',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $action = new CreateOrganizationAction;

        $org1 = $action->execute($adminUser, [
            'name' => 'Himpunan Mahasiswa Informatika',
            'slug' => 'hmif',
            'description' => 'Organisasi kemahasiswaan program studi Informatika untuk pengembangan soft skill dan hard skill.',
            'category' => 'Teknologi',
            'max_borrow_days' => 7,
            'late_fine_per_day' => 5000,
        ]);

        // Attach staff and member to org1
        $org1->users()->attach($staffUser->id, [
            'role' => Role::STAFF->value,
            'joined_at' => now(),
        ]);

        $org1->users()->attach($memberUser->id, [
            'role' => Role::MEMBER->value,
            'joined_at' => now(),
        ]);

        // Create a second organization where memberUser is the creator (Admin) and adminUser is just a member
        $org2 = $action->execute($memberUser, [
            'name' => 'Unit Robotika Kampus',
            'slug' => 'robotika',
            'description' => 'Unit kegiatan mahasiswa riset dan pengembangan robotika otomasi.',
            'category' => 'Robotika',
            'max_borrow_days' => 5,
            'late_fine_per_day' => 10000,
        ]);

        $org2->users()->attach($adminUser->id, [
            'role' => Role::MEMBER->value,
            'joined_at' => now(),
        ]);
    }
}
