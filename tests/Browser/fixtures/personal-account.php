<?php

// Isolated local browser fixtures. Cleanup only removes this run's own rows.
use App\Actions\BorrowAsset;
use App\Actions\ManageWorkspace;
use App\Actions\SettleCharge;
use App\Models\AssetCategory;
use App\Models\BorrowerCharge;
use App\Models\ChargePayment;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

require dirname(__DIR__, 3).'/vendor/autoload.php';
$app = require dirname(__DIR__, 3).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('local', 'testing') || ! preg_match('/^[a-f0-9]{16}$/', $argv[2] ?? '')) {
    throw new RuntimeException('Browser fixtures require a local/testing app and a unique run identifier.');
}
$token = $argv[2];
$manifest = sys_get_temp_dir().'/oi-personal-account-'.$token.'.json';

if (($argv[1] ?? '') === 'cleanup') {
    if (! is_file($manifest)) {
        exit(0);
    }
    $fixture = json_decode(file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
    DB::transaction(function () use ($fixture, $token) {
        $emails = User::withTrashed()->whereIn('id', $fixture['userIds'])->pluck('email');
        foreach ($emails as $email) {
            if (! str_ends_with($email, '-'.$token.'@example.test')) {
                throw new RuntimeException('Refusing to delete a user outside this browser run.');
            }
        }
        if (DB::table('organizations')->whereIn('id', $fixture['organizationIds'])->whereNotIn('created_by', $fixture['userIds'])->exists()) {
            throw new RuntimeException('Refusing to delete an organization outside this browser run.');
        }
        foreach (['notifications', 'asset_logs', 'audit_logs', 'charge_credits', 'charge_payments', 'borrower_charges', 'damage_reports', 'borrowings', 'assets', 'asset_categories', 'membership_requests', 'organization_invitations', 'organization_user'] as $table) {
            DB::table($table)->whereIn('organization_id', $fixture['organizationIds'])->delete();
        }
        DB::table('organizations')->whereIn('id', $fixture['organizationIds'])->delete();
        DB::table('sessions')->whereIn('user_id', $fixture['userIds'])->delete();
        DB::table('users')->whereIn('id', $fixture['userIds'])->delete();
    });
    unlink($manifest);
    echo "Browser fixture cleaned up.\n";
    exit(0);
}
if (($argv[1] ?? '') !== 'setup' || is_file($manifest)) {
    throw new RuntimeException('Invalid or reused browser fixture command.');
}

Notification::fake();
try {
    $fixture = DB::transaction(function () use ($token) {
        Date::setTestNow(now()->subDays(4));
        $user = User::create(['name' => 'Nadia Putri', 'email' => 'browser-'.$token.'@example.test', 'password' => 'DemoPassword123!']);
        $manager = User::create(['name' => 'Raka Pengelola', 'email' => 'manager-'.$token.'@example.test', 'password' => 'DemoPassword123!']);
        foreach ([$user, $manager] as $account) {
            $account->forceFill(['email_verified_at' => now()])->save();
        }
        $workspace = app(ManageWorkspace::class);
        $own = $workspace->create($user, ['name' => 'Studio Kreatif Nadia', 'description' => 'Peralatan produksi untuk kegiatan kreatif dan dokumentasi.', 'category' => 'komunitas']);
        $followed = $workspace->create($manager, ['name' => 'Perlengkapan Komunitas', 'description' => 'Berbagi perlengkapan untuk acara dan kegiatan bersama.', 'category' => 'komunitas', 'late_fine_per_day' => 50000, 'damage_charge_policy' => 'Kerusakan diperiksa pengelola setelah pengembalian.']);
        $hidden = $workspace->create($manager, ['name' => 'Organization Privat Pengelola', 'category' => 'lainnya']);
        Membership::create(['organization_id' => $followed->id, 'user_id' => $user->id, 'status' => 'active', 'role' => 'member', 'joined_at' => now()]);
        $category = AssetCategory::create(['organization_id' => $followed->id, 'name' => 'Dokumentasi']);
        $assets = [];
        foreach (['Proyektor Epson', 'Tripod Aluminium', 'Laptop Lenovo', 'Kamera Canon EOS'] as $name) {
            $assets[] = $workspace->asset($manager, $followed, ['name' => $name, 'asset_category_id' => $category->id, 'location' => 'Sekretariat komunitas']);
        }
        $borrowing = app(BorrowAsset::class);
        $returned = [];
        foreach (array_slice($assets, 0, 2) as $asset) {
            $request = $borrowing->request($user, $followed, $asset->id, ['borrow_date' => today('Asia/Jakarta')->toDateString(), 'due_date' => today('Asia/Jakarta')->addDay()->toDateString(), 'reason' => 'Dokumentasi kegiatan bersama', 'acknowledged' => true]);
            $borrowing->decide($manager, $followed, $request->id, 'approve');
            $returned[] = $request;
        }
        Date::setTestNow();
        foreach ($returned as $request) {
            $borrowing->inspect($manager, $followed, $request->id, ['condition' => 'good']);
        }
        foreach (array_slice($assets, 2) as $index => $asset) {
            $request = $borrowing->request($user, $followed, $asset->id, ['borrow_date' => today('Asia/Jakarta')->toDateString(), 'due_date' => today('Asia/Jakarta')->addDays(2)->toDateString(), 'reason' => 'Kebutuhan kegiatan komunitas', 'acknowledged' => true]);
            if ($index === 0) {
                $borrowing->decide($manager, $followed, $request->id, 'approve');
            }
        }
        $settle = app(SettleCharge::class);
        $charges = BorrowerCharge::where('organization_id', $followed->id)->orderBy('id')->get();
        $payment = function ($charge, int $amount, string $status) use ($manager, $followed, $settle) {
            $key = (string) Str::uuid();
            $settle->change($manager, $followed, $charge->id, 'payment', ['amount' => $amount, 'method' => 'cash', 'notes' => 'Pembayaran di sekretariat komunitas', 'paid_at' => now(), 'idempotency_key' => $key]);
            $entry = ChargePayment::where('idempotency_key', $key)->firstOrFail();
            if ($status !== 'pending') {
                $settle->change($manager, $followed, $charge->id, 'confirm', ['payment_id' => $entry->id]);
            }
            if ($status === 'voided') {
                $settle->change($manager, $followed, $charge->id, 'void', ['payment_id' => $entry->id, 'reason' => 'Koreksi nominal pencatatan']);
            }
        };
        $payment($charges[0], 50000, 'confirmed');
        $payment($charges[0], 25000, 'pending');
        $payment($charges[0], 10000, 'voided');
        $payment($charges[1], (int) $charges[1]->amount, 'confirmed');

        return ['email' => $user->email, 'ownSlug' => $own->slug, 'followedSlug' => $followed->slug, 'hiddenSlug' => $hidden->slug, 'userIds' => [$user->id, $manager->id], 'organizationIds' => [$own->id, $followed->id, $hidden->id]];
    });
    file_put_contents($manifest, json_encode($fixture, JSON_THROW_ON_ERROR));
    echo json_encode($fixture, JSON_THROW_ON_ERROR)."\n";
} finally {
    Date::setTestNow();
}
