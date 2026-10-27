<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (explode(';', file_get_contents(database_path('schema/inventory-v1.2.sql'))) as $sql) {
            if (trim($sql)) {
                DB::unprepared($sql);
            }
        }
    }

    public function down(): void
    {
        foreach (['charge_credits', 'charge_payments', 'borrower_charges', 'audit_logs', 'asset_logs', 'damage_reports', 'borrowings', 'assets', 'asset_categories', 'notifications', 'organization_invitations', 'membership_requests', 'organization_user', 'organizations', 'password_reset_tokens', 'users'] as $table) {
            DB::statement("DROP TABLE IF EXISTS `$table`");
        }
    }
};
