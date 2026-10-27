<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $checks = [
        'organization_user' => ["(status='active' AND revoked_at IS NULL AND revoked_by IS NULL) OR (status='revoked' AND revoked_at IS NOT NULL)"],
        'membership_requests' => ["status NOT IN ('approved','rejected') OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL)", "status <> 'cancelled' OR cancelled_at IS NOT NULL"],
        'organization_invitations' => ['expires_at > created_at', "status <> 'accepted' OR (accepted_by IS NOT NULL AND accepted_at IS NOT NULL)", "status <> 'revoked' OR revoked_at IS NOT NULL"],
        'assets' => ['CHAR_LENGTH(TRIM(name))>0', 'CHAR_LENGTH(TRIM(location))>0'],
        'asset_categories' => ['CHAR_LENGTH(TRIM(name))>0'],
        'borrowings' => ["status NOT IN ('borrowed','overdue','returned') OR (max_borrow_days_snapshot IS NOT NULL AND late_fine_per_day_snapshot IS NOT NULL)", "status <> 'overdue' OR overdue_at IS NOT NULL", "status <> 'returned' OR (late_days IS NOT NULL AND calculated_fine_amount IS NOT NULL AND fine_amount IS NOT NULL AND returned_at >= approved_at AND return_processed_at >= returned_at)", 'fine_amount IS NULL OR calculated_fine_amount=fine_amount OR CHAR_LENGTH(TRIM(fine_override_reason))>0'],
        'damage_reports' => ['severity IS NULL OR (assessed_at IS NOT NULL AND handled_by IS NOT NULL)', "status <> 'noted' OR resolution_notes IS NOT NULL"],
        'borrower_charges' => ['CHAR_LENGTH(TRIM(reason))>0 AND CHAR_LENGTH(TRIM(policy_basis))>0', "charge_type='late_fee' OR created_by IS NOT NULL", "status <> 'issued' OR charge_type='late_fee' OR issued_by IS NOT NULL"],
        'charge_payments' => ["(method='bank_transfer' AND (CHAR_LENGTH(TRIM(reference))>0 OR CHAR_LENGTH(TRIM(evidence_path))>0)) OR (method IN ('cash','other') AND CHAR_LENGTH(TRIM(notes))>0)"],
    ];

    public function up(): void
    {
        foreach ($this->checks as $table => $checks) {
            foreach ($checks as $i => $check) {
                DB::statement("ALTER TABLE `$table` ADD CONSTRAINT `hard_{$table}_{$i}` CHECK ($check)");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->checks as $table => $checks) {
            foreach ($checks as $i => $check) {
                DB::statement("ALTER TABLE `$table` DROP CHECK `hard_{$table}_{$i}`");
            }
        }
    }
};
