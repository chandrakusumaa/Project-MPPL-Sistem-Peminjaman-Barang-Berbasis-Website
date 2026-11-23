<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('borrower_charges')) {
            return;
        }

        if (! Schema::hasColumn('borrower_charges', 'damage_report_guard')) {
            DB::statement(
                'ALTER TABLE `borrower_charges` '
                .'ADD COLUMN `damage_report_guard` BIGINT UNSIGNED GENERATED ALWAYS AS (COALESCE(`damage_report_id`, 0)) VIRTUAL AFTER `damage_report_id`, '
                .'DROP INDEX `uq_borrower_charges_1`, '
                .'ADD UNIQUE KEY `uq_borrower_charges_1` (`borrowing_id`, `charge_type`, `damage_report_guard`, `active_guard`)'
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('borrower_charges') || ! Schema::hasColumn('borrower_charges', 'damage_report_guard')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `borrower_charges` '
            .'DROP INDEX `uq_borrower_charges_1`, '
            .'ADD UNIQUE KEY `uq_borrower_charges_1` (`borrowing_id`, `charge_type`, `active_guard`), '
            .'DROP COLUMN `damage_report_guard`'
        );
    }
};
