<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $tables = ['organization_invitations', 'membership_requests'];

    public function up(): void
    {
        $tables = array_values(array_filter($this->tables, fn ($table) => Schema::hasTable($table)));
        if (! $tables) {
            return;
        }
        // Unexpected custom dependencies must be reviewed before any destructive DDL.
        $inbound = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('REFERENCED_TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->whereIn('REFERENCED_TABLE_NAME', $tables)->count();
        $triggers = DB::table('information_schema.TRIGGERS')
            ->where('TRIGGER_SCHEMA', DB::connection()->getDatabaseName())
            ->where(function ($query) use ($tables) {
                $query->whereIn('EVENT_OBJECT_TABLE', $tables);
                foreach ($tables as $table) {
                    $query->orWhere('ACTION_STATEMENT', 'like', '%'.$table.'%');
                }
            })->count();
        if ($inbound || $triggers) {
            throw new RuntimeException('Legacy membership tables have custom FK/trigger dependencies. Review them before migration.');
        }

        // MySQL DDL is not transactional. Hold write locks while taking a private
        // archive and dropping only these two tables, preventing an in-flight old
        // request from adding rows after the archive was taken.
        DB::statement('LOCK TABLES '.implode(', ', array_map(fn ($table) => '`'.$table.'` WRITE', $tables)));
        try {
            $this->archive($tables);
            foreach ($tables as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            DB::statement('UNLOCK TABLES');
        }
    }

    private function archive(array $tables): void
    {
        if (! collect($tables)->contains(fn ($table) => DB::table($table)->exists())) {
            return;
        }
        $disk = Storage::disk('local');
        $directory = 'legacy-membership/'.now('UTC')->format('Ymd-His').'-'.Str::uuid();
        if (! $disk->makeDirectory($directory)) {
            throw new RuntimeException('Cannot create the private legacy membership archive. No tables were dropped.');
        }
        $disk->setVisibility($directory, 'private');
        $manifest = ['database' => DB::connection()->getDatabaseName(), 'archived_at' => now('UTC')->toIso8601String(), 'tables' => []];
        foreach ($tables as $table) {
            $stream = tmpfile();
            if ($stream === false) {
                throw new RuntimeException('Cannot open a legacy membership archive stream.');
            }
            try {
                $hash = hash_init('sha256');
                $rows = 0;
                foreach (DB::table($table)->orderBy('id')->lazyById(500) as $row) {
                    $line = json_encode($row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n";
                    if (fwrite($stream, $line) !== strlen($line)) {
                        throw new RuntimeException('Incomplete legacy membership archive write.');
                    }
                    hash_update($hash, $line);
                    $rows++;
                }
                rewind($stream);
                if (! $disk->writeStream($directory.'/'.$table.'.jsonl', $stream, ['visibility' => 'private'])) {
                    throw new RuntimeException('Cannot persist the legacy membership archive. No tables were dropped.');
                }
                $checksum = hash_final($hash);
                if (! hash_equals($checksum, hash_file('sha256', $disk->path($directory.'/'.$table.'.jsonl')))) {
                    throw new RuntimeException('Legacy membership archive checksum mismatch. No tables were dropped.');
                }
                $ddl = (array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
                if (! $disk->put($directory.'/'.$table.'.sql', array_values($ddl)[1].";\n", ['visibility' => 'private'])) {
                    throw new RuntimeException('Cannot persist the legacy table definition.');
                }
                $manifest['tables'][$table] = ['rows' => $rows, 'sha256' => $checksum];
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }
        if (! $disk->put($directory.'/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT), ['visibility' => 'private'])) {
            throw new RuntimeException('Cannot persist the legacy membership archive manifest.');
        }
    }

    public function down(): void
    {
        // Recreate the old structure and lifecycle checks, never deleted rows.
        // Retained private archives require a deliberate, separately reviewed import.
        $sql = file_get_contents(database_path('schema/2026-10-27-inventory-legacy.sql'));
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }
            preg_match('/CREATE TABLE `'.$table.'` \(.*?\) ENGINE=InnoDB[^;]*;/s', $sql, $match);
            DB::unprepared($match[0]);
        }
        $checks = [
            'membership_requests' => ["status NOT IN ('approved','rejected') OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL)", "status <> 'cancelled' OR cancelled_at IS NOT NULL"],
            'organization_invitations' => ['expires_at > created_at', "status <> 'accepted' OR (accepted_by IS NOT NULL AND accepted_at IS NOT NULL)", "status <> 'revoked' OR revoked_at IS NOT NULL"],
        ];
        foreach ($checks as $table => $rules) {
            foreach ($rules as $index => $rule) {
                $name = 'hard_'.$table.'_'.$index;
                $exists = DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())->where('TABLE_NAME', $table)->where('CONSTRAINT_NAME', $name)->exists();
                if (! $exists) {
                    DB::statement("ALTER TABLE `$table` ADD CONSTRAINT `$name` CHECK ($rule)");
                }
            }
        }
    }
};
