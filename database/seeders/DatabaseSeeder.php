<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\BorrowingStatus;
use App\Enums\DamageReportStatus;
use App\Enums\DamageSeverity;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Borrowing;
use App\Models\DamageReport;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $adminUser = User::factory()->create([
            'name' => 'Budi Admin',
            'email' => 'admin@example.com',
        ]);

        $staffUser = User::factory()->create([
            'name' => 'Siti Staff',
            'email' => 'staff@example.com',
        ]);

        $memberUser1 = User::factory()->create([
            'name' => 'Andi Member',
            'email' => 'member1@example.com',
        ]);

        $memberUser2 = User::factory()->create([
            'name' => 'Dewi Member',
            'email' => 'member2@example.com',
        ]);

        // 2. Organizations
        $orgA = Organization::factory()->create([
            'name' => 'Kampus IT',
            'slug' => 'kampus-it',
            'category' => 'Kampus',
            'created_by' => $adminUser->id,
        ]);

        $orgB = Organization::factory()->create([
            'name' => 'Komunitas Dev',
            'slug' => 'komunitas-dev',
            'category' => 'Komunitas',
            'created_by' => $memberUser1->id, // Let's make member1 create orgB
        ]);

        // 3. Organization Memberships
        // Org A: Admin is admin, Staff is staff, Member1 & Member2 are members
        $orgA->users()->attach($adminUser->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
        $orgA->users()->attach($staffUser->id, ['role' => Role::STAFF->value, 'joined_at' => now()]);
        $orgA->users()->attach($memberUser1->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);
        $orgA->users()->attach($memberUser2->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

        // Org B: Member1 is admin, Admin is member (cross roles testing)
        $orgB->users()->attach($memberUser1->id, ['role' => Role::ADMIN->value, 'joined_at' => now()]);
        $orgB->users()->attach($adminUser->id, ['role' => Role::MEMBER->value, 'joined_at' => now()]);

        // 4. Asset Categories (for Org A)
        $catLaptop = AssetCategory::create(['organization_id' => $orgA->id, 'name' => 'Laptop']);
        $catProjector = AssetCategory::create(['organization_id' => $orgA->id, 'name' => 'Proyektor']);

        // 5. Assets (for Org A) - Around 15 assets
        for ($i = 1; $i <= 10; $i++) {
            Asset::create([
                'organization_id' => $orgA->id,
                'asset_category_id' => $catLaptop->id,
                'code' => Str::ulid(),
                'name' => "Laptop Lenovo Thinkpad T490 - #$i",
                'description' => 'Laptop untuk dipinjamkan ke mahasiswa',
                'specifications' => 'Core i5, 8GB RAM, 256GB SSD',
                'location' => 'Ruang Lab 1',
                'status' => AssetStatus::AVAILABLE->value,
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Asset::create([
                'organization_id' => $orgA->id,
                'asset_category_id' => $catProjector->id,
                'code' => Str::ulid(),
                'name' => "Proyektor Epson - #$i",
                'status' => $i === 1 ? AssetStatus::MAINTENANCE->value : ($i === 2 ? AssetStatus::BORROWED->value : AssetStatus::AVAILABLE->value),
            ]);
        }

        // 6. Borrowings (for Org A)
        $borrowedAsset = Asset::where('status', AssetStatus::BORROWED->value)->first();

        if ($borrowedAsset) {
            Borrowing::create([
                'organization_id' => $orgA->id,
                'asset_id' => $borrowedAsset->id,
                'user_id' => $memberUser1->id,
                'borrow_date' => now()->toDateString(),
                'due_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Presentasi kelas',
                'status' => BorrowingStatus::BORROWED->value,
                'approved_by' => $adminUser->id,
                'approved_at' => now(),
            ]);
        }

        // Pending borrowing
        $availableAsset = Asset::where('status', AssetStatus::AVAILABLE->value)->first();
        if ($availableAsset) {
            Borrowing::create([
                'organization_id' => $orgA->id,
                'asset_id' => $availableAsset->id,
                'user_id' => $memberUser2->id,
                'borrow_date' => now()->addDay()->toDateString(),
                'due_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Keperluan event',
                'status' => BorrowingStatus::PENDING->value,
            ]);
        }

        // 7. Damage Reports (for Org A)
        $maintenanceAsset = Asset::where('status', AssetStatus::MAINTENANCE->value)->first();
        if ($maintenanceAsset) {
            DamageReport::create([
                'organization_id' => $orgA->id,
                'asset_id' => $maintenanceAsset->id,
                'reported_by' => $staffUser->id,
                'severity' => DamageSeverity::MAJOR->value,
                'description' => 'Lampu proyektor mati total',
                'status' => DamageReportStatus::IN_MAINTENANCE->value,
            ]);
        }

        $otherAsset = Asset::where('status', AssetStatus::AVAILABLE->value)->skip(1)->first();
        if ($otherAsset) {
            DamageReport::create([
                'organization_id' => $orgA->id,
                'asset_id' => $otherAsset->id,
                'reported_by' => $memberUser1->id,
                'severity' => DamageSeverity::MINOR->value,
                'description' => 'Lecet sedikit di casing',
                'status' => DamageReportStatus::NOTED->value,
            ]);
        }
    }
}
