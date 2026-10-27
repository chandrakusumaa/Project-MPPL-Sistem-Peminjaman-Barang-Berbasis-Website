<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Development fixtures only; never run this seeder in production.
        if (app()->isProduction()) {
            throw new \RuntimeException('Demo seeder dilarang pada produksi.');
        }
        $users = [];
        foreach (['admin' => 'Raka', 'staff' => 'Dimas', 'member' => 'Sinta', 'other' => 'Nadia'] as $role => $name) {
            $u = User::firstOrCreate(['email' => $role.'@example.test'], ['name' => $name, 'password' => 'DemoPassword123!']);
            if (! $u->email_verified_at) {
                $u->forceFill(['email_verified_at' => now()])->save();
            }$users[$role] = $u;
        }
        $org = Organization::firstOrCreate(['slug' => 'peralatan-raka'], ['name' => 'Peralatan Raka', 'description' => 'Perlengkapan dokumentasi dan kegiatan bersama.', 'category' => 'komunitas', 'created_by' => $users['admin']->id, 'max_borrow_days' => 7, 'late_fine_per_day' => 5000, 'damage_charge_policy' => 'Biaya kerusakan dinilai berdasarkan bukti dan persetujuan tanggung jawab.']);
        foreach (['admin', 'staff', 'member'] as $role) {
            Membership::firstOrCreate(['organization_id' => $org->id, 'user_id' => $users[$role]->id], ['role' => $role, 'status' => 'active', 'joined_at' => now()]);
        }
        $category = AssetCategory::firstOrCreate(['organization_id' => $org->id, 'name' => 'Dokumentasi']);
        foreach (['Kamera Canon EOS', 'Tripod Aluminium', 'Proyektor Epson'] as $name) {
            Asset::firstOrCreate(['organization_id' => $org->id, 'name' => $name], ['asset_category_id' => $category->id, 'code' => (string) Str::ulid(), 'location' => 'Lemari utama', 'status' => 'available', 'physical_condition' => 'good']);
        }
    }
}
