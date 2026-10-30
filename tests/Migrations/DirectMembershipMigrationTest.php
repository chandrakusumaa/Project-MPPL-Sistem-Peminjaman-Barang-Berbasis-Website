<?php

use App\Models\Asset;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Notification::fake();
    $this->seed();
    $this->migration = require database_path('migrations/2026_10_30_000003_remove_legacy_membership_flows.php');
});

function legacyMembershipRows(): void
{
    $user = User::where('email', 'other@example.test')->firstOrFail();
    $admin = User::where('email', 'admin@example.test')->firstOrFail();
    $org = Organization::firstOrFail();
    DB::table('membership_requests')->insert(['organization_id' => $org->id, 'user_id' => $user->id, 'message' => 'Historical request fixture', 'status' => 'pending']);
    DB::table('organization_invitations')->insert(['organization_id' => $org->id, 'email' => $user->email, 'role' => 'staff', 'token_hash' => hash('sha256', 'obsolete-test-token'), 'invited_by' => $admin->id, 'expires_at' => now()->addDays(7), 'status' => 'pending']);
}

it('migrates to fourteen domain tables while preserving booking guards', function () {
    expect(DB::getDriverName())->toBe('mysql')
        ->and(Schema::hasTable('membership_requests'))->toBeFalse()
        ->and(Schema::hasTable('organization_invitations'))->toBeFalse();
    $sql = file_get_contents(database_path('schema/inventory-v1.2.sql'));
    preg_match_all('/CREATE TABLE `([^`]+)`/', $sql, $tables);
    expect($tables[1])->toHaveCount(14)->not->toContain('membership_requests', 'organization_invitations');
    $unique = DB::select("SHOW INDEX FROM organization_user WHERE Key_name='uq_organization_user_0'");
    expect($unique)->toHaveCount(2);
    expect(Schema::hasColumns('borrowings', ['active_guard', 'open_user_guard', 'approved_at', 'cancelled_at']))->toBeTrue();
});

it('archives legacy records before dropping and recreates only empty structures on rollback', function () {
    Storage::fake('local');
    $this->migration->down();
    legacyMembershipRows();
    $members = DB::table('organization_user')->orderBy('id')->get()->toJson();
    $assets = Asset::orderBy('id')->get()->toJson();
    $this->migration->up();

    expect(Schema::hasTable('membership_requests'))->toBeFalse()
        ->and(Schema::hasTable('organization_invitations'))->toBeFalse()
        ->and(DB::table('organization_user')->orderBy('id')->get()->toJson())->toBe($members)
        ->and(Asset::orderBy('id')->get()->toJson())->toBe($assets);
    $files = Storage::disk('local')->allFiles('legacy-membership');
    $manifestPath = collect($files)->first(fn ($path) => str_ends_with($path, '/manifest.json'));
    $manifest = json_decode(Storage::disk('local')->get($manifestPath), true, flags: JSON_THROW_ON_ERROR);
    foreach (['membership_requests', 'organization_invitations'] as $table) {
        $data = Storage::disk('local')->get(dirname($manifestPath).'/'.$table.'.jsonl');
        expect($manifest['tables'][$table]['rows'])->toBe(1)
            ->and($manifest['tables'][$table]['sha256'])->toBe(hash('sha256', $data));
    }
    $this->migration->down();
    expect(DB::table('membership_requests')->count())->toBe(0)
        ->and(DB::table('organization_invitations')->count())->toBe(0)
        ->and(DB::table('organization_user')->orderBy('id')->get()->toJson())->toBe($members);
    $this->migration->up();
});

it('refuses to drop tables when a custom inbound foreign key exists', function () {
    $this->migration->down();
    legacyMembershipRows();
    DB::statement('CREATE TABLE legacy_membership_dependency (id BIGINT UNSIGNED PRIMARY KEY, request_id BIGINT UNSIGNED NOT NULL, FOREIGN KEY (request_id) REFERENCES membership_requests(id)) ENGINE=InnoDB');
    try {
        expect(fn () => $this->migration->up())->toThrow(RuntimeException::class, 'custom FK/trigger');
        expect(Schema::hasTable('membership_requests'))->toBeTrue()->and(DB::table('membership_requests')->count())->toBe(1);
    } finally {
        Schema::dropIfExists('legacy_membership_dependency');
    }
});

it('keeps legacy data intact if the private archive cannot be written', function () {
    $this->migration->down();
    legacyMembershipRows();
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('makeDirectory')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('local')->andReturn($disk);
    expect(fn () => $this->migration->up())->toThrow(RuntimeException::class, 'Cannot create the private');
    expect(DB::table('membership_requests')->count())->toBe(1)->and(DB::table('organization_invitations')->count())->toBe(1);
});

it('refuses custom triggers attached to a retired table even without its name in the trigger body', function () {
    $this->migration->down();
    legacyMembershipRows();
    try {
        DB::statement("CREATE TRIGGER legacy_membership_guard BEFORE UPDATE ON membership_requests FOR EACH ROW SET NEW.message = COALESCE(NEW.message, 'historical')");
    } catch (QueryException $exception) {
        if (($exception->errorInfo[1] ?? null) === 1419) {
            $this->markTestSkipped('MySQL binary logging requires SUPER to create a custom trigger; no global privileges or server settings were changed.');
        }
        throw $exception;
    }
    try {
        expect(fn () => $this->migration->up())->toThrow(RuntimeException::class, 'custom FK/trigger');
        expect(Schema::hasTable('membership_requests'))->toBeTrue()->and(DB::table('membership_requests')->count())->toBe(1);
    } finally {
        DB::statement('DROP TRIGGER IF EXISTS legacy_membership_guard');
    }
});

it('imports the standalone active DDL with fourteen tables and all lifecycle checks on MySQL', function () {
    $sql = file_get_contents(database_path('schema/inventory-v1.2.sql'));
    preg_match_all('/CREATE TABLE `([^`]+)`/', $sql, $tables);
    foreach (array_reverse($tables[1]) as $table) {
        Schema::dropIfExists($table);
    }
    foreach (explode(';', $sql) as $statement) {
        if (trim($statement)) {
            DB::unprepared($statement);
        }
    }
    foreach ($tables[1] as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
    expect(Schema::hasTable('membership_requests'))->toBeFalse()
        ->and(Schema::hasTable('organization_invitations'))->toBeFalse();
    $this->seed();
    expect(DB::table('organization_user')->count())->toBe(3);
    expect(DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())->where('CONSTRAINT_NAME', 'hard_organization_user_0')->exists())->toBeTrue();
});
