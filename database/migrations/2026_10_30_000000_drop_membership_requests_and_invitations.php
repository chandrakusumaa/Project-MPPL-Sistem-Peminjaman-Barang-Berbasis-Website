<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keputusan produk 30 Oktober 2026: Organization dimasuki langsung sebagai Member.
 * Join request dan invitation dihapus. Tabel organization_user (role, status, histori revoke) dipertahankan.
 *
 * Kedua tabel adalah tabel daun: tidak ada FK dari tabel lain dan tidak ada trigger yang mereferensikannya.
 *
 * PENTING: down() hanya membuat ulang STRUKTUR. Baris yang sudah dihapus tidak dipulihkan.
 * Simpan cadangan (mysqldump) sebelum menjalankan migration ini pada database yang berisi data.
 *
 * Pengaman: jika salah satu tabel masih berisi baris, migration berhenti kecuali
 * DROP_LEGACY_MEMBERSHIP_TABLES=true ditetapkan secara sadar setelah cadangan dibuat.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = [];
        foreach (['membership_requests', 'organization_invitations'] as $table) {
            if (Schema::hasTable($table)) {
                $rows[$table] = DB::table($table)->count();
            }
        }

        if (array_sum($rows) > 0 && ! filter_var(env('DROP_LEGACY_MEMBERSHIP_TABLES', false), FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException('Migration dihentikan: membership_requests/organization_invitations masih berisi data ('.json_encode($rows).'). '
                .'Buat cadangan, putuskan penanganan data historis, lalu jalankan ulang dengan DROP_LEGACY_MEMBERSHIP_TABLES=true.');
        }

        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('membership_requests');
    }

    /**
     * Membuat ulang struktur lama (termasuk CHECK hard_* agar down() migration 000002 tetap valid
     * pada rollback berurutan). Data tidak dipulihkan.
     */
    public function down(): void
    {
        if (! Schema::hasTable('membership_requests')) {
            DB::statement(<<<'SQL'
CREATE TABLE `membership_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') NOT NULL,
  `reviewed_by` BIGINT UNSIGNED NULL,
  `reviewed_at` DATETIME(6) NULL,
  `review_notes` TEXT NULL,
  `cancelled_by` BIGINT UNSIGNED NULL,
  `cancelled_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `pending_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN 1 ELSE NULL END) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_membership_requests_0` (organization_id,user_id,pending_guard),
  FOREIGN KEY (organization_id) REFERENCES `organizations` (id),
  FOREIGN KEY (user_id) REFERENCES `users` (id),
  FOREIGN KEY (reviewed_by) REFERENCES `users` (id),
  FOREIGN KEY (cancelled_by) REFERENCES `users` (id),
  CONSTRAINT `hard_membership_requests_0` CHECK (status NOT IN ('approved','rejected') OR (reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL)),
  CONSTRAINT `hard_membership_requests_1` CHECK (status <> 'cancelled' OR cancelled_at IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
SQL);
        }

        if (! Schema::hasTable('organization_invitations')) {
            DB::statement(<<<'SQL'
CREATE TABLE `organization_invitations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `role` ENUM('staff','member') NOT NULL,
  `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `invited_by` BIGINT UNSIGNED NOT NULL,
  `expires_at` DATETIME(6) NOT NULL,
  `last_sent_at` DATETIME(6) NULL,
  `status` ENUM('pending','accepted','expired','revoked') NOT NULL,
  `accepted_by` BIGINT UNSIGNED NULL,
  `accepted_at` DATETIME(6) NULL,
  `revoked_by` BIGINT UNSIGNED NULL,
  `revoked_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `pending_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN 1 ELSE NULL END) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_organization_invitations_0` (token_hash),
  UNIQUE KEY `uq_organization_invitations_1` (organization_id,email,pending_guard),
  FOREIGN KEY (organization_id) REFERENCES `organizations` (id),
  FOREIGN KEY (invited_by) REFERENCES `users` (id),
  FOREIGN KEY (accepted_by) REFERENCES `users` (id),
  FOREIGN KEY (revoked_by) REFERENCES `users` (id),
  CONSTRAINT `hard_organization_invitations_0` CHECK (expires_at > created_at),
  CONSTRAINT `hard_organization_invitations_1` CHECK (status <> 'accepted' OR (accepted_by IS NOT NULL AND accepted_at IS NOT NULL)),
  CONSTRAINT `hard_organization_invitations_2` CHECK (status <> 'revoked' OR revoked_at IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
SQL);
        }
    }
};
