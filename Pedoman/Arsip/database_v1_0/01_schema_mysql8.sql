-- Organization Inventory | Proposed Database Design v1.0 | 20 October 2026
-- Target: MySQL >= 8.0.16, InnoDB. Select an EMPTY database before import.
-- This file creates tables only. It does not create/drop a database or disable FK checks.
-- Application transactions/policies remain mandatory; see the accompanying design document.
SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;
SET time_zone = '+00:00';

-- Identitas akun global; role berada pada keanggotaan organisasi.
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `email_verified_at` DATETIME(6) NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `pending_email` VARCHAR(254) NULL,
  `pending_email_requested_at` DATETIME(6) NULL,
  `avatar` VARCHAR(255) NULL,
  `notification_preferences` JSON NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `deleted_at` DATETIME(6) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `ix_users_pending_email` (`pending_email`),
  CONSTRAINT `ck_users_name` CHECK (CHAR_LENGTH(TRIM(name)) > 0),
  CONSTRAINT `ck_users_pending_email` CHECK ((pending_email IS NULL AND pending_email_requested_at IS NULL) OR (pending_email IS NOT NULL AND pending_email_requested_at IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Organisasi pemilik aset dan kebijakan operasional.
CREATE TABLE `organizations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `description` TEXT NULL,
  `category` ENUM('kampus','sekolah','komunitas','perusahaan','lainnya') NOT NULL,
  `logo` VARCHAR(255) NULL,
  `max_borrow_days` SMALLINT UNSIGNED NOT NULL DEFAULT 7,
  `late_fine_per_day` DECIMAL(12,0) NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `archived_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `deleted_at` DATETIME(6) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_organizations_slug` (`slug`),
  KEY `ix_org_explore` (`deleted_at`, `archived_at`, `category`),
  CONSTRAINT `fk_organizations_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_org_name` CHECK (CHAR_LENGTH(TRIM(name)) > 0),
  CONSTRAINT `ck_org_slug` CHECK (CHAR_LENGTH(slug) > 0),
  CONSTRAINT `ck_org_max_borrow_days` CHECK (max_borrow_days >= 1),
  CONSTRAINT `ck_org_fine` CHECK (late_fine_per_day >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Keanggotaan dan role per organisasi; baris dicabut tetap disimpan.
CREATE TABLE `organization_user` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role` ENUM('admin','staff','member') NOT NULL DEFAULT 'member',
  `status` ENUM('active','revoked') NOT NULL DEFAULT 'active',
  `joined_at` DATETIME(6) NOT NULL,
  `revoked_at` DATETIME(6) NULL,
  `revoked_by` BIGINT UNSIGNED NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_org_user` (`organization_id`, `user_id`),
  KEY `ix_membership_user` (`user_id`, `status`, `organization_id`),
  KEY `ix_org_admins` (`organization_id`, `status`, `role`),
  CONSTRAINT `fk_membership_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_membership_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_membership_revoked_by` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_membership_lifecycle` CHECK ((status = 'active' AND revoked_at IS NULL AND revoked_by IS NULL) OR (status = 'revoked' AND revoked_at IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Pengajuan join; setiap pengajuan ulang adalah baris baru.
CREATE TABLE `membership_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `reviewed_by` BIGINT UNSIGNED NULL,
  `reviewed_at` DATETIME(6) NULL,
  `review_notes` TEXT NULL,
  `cancelled_by` BIGINT UNSIGNED NULL,
  `cancelled_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `pending_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN 1 ELSE NULL END) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_join_pending` (`organization_id`, `user_id`, `pending_guard`),
  KEY `ix_join_inbox` (`organization_id`, `status`, `created_at`),
  KEY `ix_join_user` (`user_id`, `created_at`),
  CONSTRAINT `fk_join_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_join_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_join_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_join_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_join_review` CHECK ((status IN ('approved','rejected') AND reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL) OR (status NOT IN ('approved','rejected') AND reviewed_by IS NULL AND reviewed_at IS NULL)),
  CONSTRAINT `ck_join_cancel` CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL) OR (status <> 'cancelled' AND cancelled_at IS NULL AND cancelled_by IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Undangan satu email; token mentah hanya dikirim lewat link, bukan disimpan.
CREATE TABLE `organization_invitations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `role` ENUM('staff','member') NOT NULL,
  `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `invited_by` BIGINT UNSIGNED NOT NULL,
  `expires_at` DATETIME(6) NOT NULL,
  `last_sent_at` DATETIME(6) NULL,
  `status` ENUM('pending','accepted','expired','revoked') NOT NULL DEFAULT 'pending',
  `accepted_by` BIGINT UNSIGNED NULL,
  `accepted_at` DATETIME(6) NULL,
  `revoked_by` BIGINT UNSIGNED NULL,
  `revoked_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `pending_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN 1 ELSE NULL END) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invitation_hash` (`token_hash`),
  UNIQUE KEY `uq_invitation_pending` (`organization_id`, `email`, `pending_guard`),
  KEY `ix_invitation_expiry` (`status`, `expires_at`),
  KEY `ix_invitation_inbox` (`organization_id`, `status`, `created_at`),
  CONSTRAINT `fk_invitation_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_invitation_invited_by` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_invitation_accepted_by` FOREIGN KEY (`accepted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_invitation_revoked_by` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_invitation_hash` CHECK (CHAR_LENGTH(token_hash) = 64),
  CONSTRAINT `ck_invitation_expiry` CHECK (expires_at > created_at),
  CONSTRAINT `ck_invitation_accept` CHECK ((status = 'accepted' AND accepted_by IS NOT NULL AND accepted_at IS NOT NULL AND accepted_at < expires_at) OR (status <> 'accepted' AND accepted_by IS NULL AND accepted_at IS NULL)),
  CONSTRAINT `ck_invitation_revoke` CHECK ((status = 'revoked' AND revoked_at IS NOT NULL) OR (status <> 'revoked' AND revoked_at IS NULL AND revoked_by IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Kategori aset milik satu organisasi; nama unik per organisasi.
CREATE TABLE `asset_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_name` (`organization_id`, `name`),
  UNIQUE KEY `uq_category_id_org` (`id`, `organization_id`),
  CONSTRAINT `fk_category_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `ck_category_name` CHECK (CHAR_LENGTH(TRIM(name)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Satu baris = satu unit fisik = satu kode/QR; tidak ada quantity.
CREATE TABLE `assets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `asset_category_id` BIGINT UNSIGNED NOT NULL,
  `code` CHAR(26) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `specifications` TEXT NULL,
  `location` VARCHAR(180) NOT NULL,
  `purchase_date` DATE NULL,
  `photo` VARCHAR(255) NULL,
  `status` ENUM('available','borrowed','maintenance','lost') NOT NULL DEFAULT 'available',
  `physical_condition` ENUM('good','minor_damage','major_damage','unknown') NOT NULL DEFAULT 'good',
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `deleted_at` DATETIME(6) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asset_code` (`code`),
  UNIQUE KEY `uq_asset_id_org` (`id`, `organization_id`),
  KEY `ix_asset_inventory` (`organization_id`, `deleted_at`, `status`),
  KEY `ix_asset_category` (`organization_id`, `asset_category_id`, `deleted_at`),
  CONSTRAINT `fk_asset_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_asset_category_tenant` FOREIGN KEY (`asset_category_id`, `organization_id`) REFERENCES `asset_categories` (`id`, `organization_id`),
  CONSTRAINT `ck_asset_code` CHECK (CHAR_LENGTH(code) = 26),
  CONSTRAINT `ck_asset_name` CHECK (CHAR_LENGTH(TRIM(name)) > 0),
  CONSTRAINT `ck_asset_location` CHECK (CHAR_LENGTH(TRIM(location)) > 0),
  CONSTRAINT `ck_asset_usable` CHECK (status <> 'available' OR physical_condition IN ('good','minor_damage'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Satu request untuk satu aset; approval dan inspeksi return berada pada baris yang sama.
CREATE TABLE `borrowings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `asset_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `borrow_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending','rejected','cancelled','borrowed','overdue','returned') NOT NULL DEFAULT 'pending',
  `approved_by` BIGINT UNSIGNED NULL,
  `approved_at` DATETIME(6) NULL,
  `max_borrow_days_snapshot` SMALLINT UNSIGNED NULL,
  `late_fine_per_day_snapshot` DECIMAL(12,0) NULL,
  `rejected_by` BIGINT UNSIGNED NULL,
  `rejected_at` DATETIME(6) NULL,
  `rejection_reason` TEXT NULL,
  `cancelled_by` BIGINT UNSIGNED NULL,
  `cancelled_at` DATETIME(6) NULL,
  `cancellation_reason` TEXT NULL,
  `overdue_at` DATETIME(6) NULL,
  `returned_at` DATETIME(6) NULL,
  `processed_by` BIGINT UNSIGNED NULL,
  `return_processed_at` DATETIME(6) NULL,
  `return_condition` ENUM('good','minor_damage','major_damage','lost') NULL,
  `return_notes` TEXT NULL,
  `late_days` INT UNSIGNED NULL,
  `calculated_fine_amount` DECIMAL(14,0) NULL,
  `fine_amount` DECIMAL(14,0) NULL,
  `fine_override_reason` TEXT NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `active_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status IN ('borrowed','overdue') THEN 1 ELSE NULL END) VIRTUAL,
  `open_user_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status IN ('pending','borrowed','overdue') THEN 1 ELSE NULL END) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_borrowing_active_asset` (`asset_id`, `active_guard`),
  UNIQUE KEY `uq_borrowing_open_user` (`asset_id`, `user_id`, `open_user_guard`),
  UNIQUE KEY `uq_borrowing_id_asset_org` (`id`, `asset_id`, `organization_id`),
  KEY `ix_borrowing_org_status` (`organization_id`, `status`, `created_at`),
  KEY `ix_borrowing_user` (`user_id`, `status`, `created_at`),
  KEY `ix_borrowing_due` (`status`, `due_date`),
  KEY `ix_borrowing_report` (`organization_id`, `borrow_date`),
  CONSTRAINT `fk_borrowing_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_borrowing_asset_tenant` FOREIGN KEY (`asset_id`, `organization_id`) REFERENCES `assets` (`id`, `organization_id`),
  CONSTRAINT `fk_borrowing_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_borrowing_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_borrowing_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_borrowing_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_borrowing_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_borrowing_dates` CHECK (due_date > borrow_date),
  CONSTRAINT `ck_borrowing_reason` CHECK (CHAR_LENGTH(TRIM(reason)) >= 10),
  CONSTRAINT `ck_borrowing_approval` CHECK ((status IN ('borrowed','overdue','returned') AND approved_by IS NOT NULL AND approved_at IS NOT NULL AND max_borrow_days_snapshot IS NOT NULL AND late_fine_per_day_snapshot IS NOT NULL) OR (status NOT IN ('borrowed','overdue','returned') AND approved_by IS NULL AND approved_at IS NULL AND max_borrow_days_snapshot IS NULL AND late_fine_per_day_snapshot IS NULL)),
  CONSTRAINT `ck_borrowing_snapshot` CHECK ((max_borrow_days_snapshot IS NULL OR max_borrow_days_snapshot >= 1) AND (late_fine_per_day_snapshot IS NULL OR late_fine_per_day_snapshot >= 0)),
  CONSTRAINT `ck_borrowing_reject` CHECK ((status = 'rejected' AND rejected_by IS NOT NULL AND rejected_at IS NOT NULL AND rejection_reason IS NOT NULL AND CHAR_LENGTH(TRIM(rejection_reason)) > 0) OR (status <> 'rejected' AND rejected_by IS NULL AND rejected_at IS NULL AND rejection_reason IS NULL)),
  CONSTRAINT `ck_borrowing_cancel` CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL AND cancellation_reason IS NOT NULL AND CHAR_LENGTH(TRIM(cancellation_reason)) > 0) OR (status <> 'cancelled' AND cancelled_at IS NULL AND cancelled_by IS NULL AND cancellation_reason IS NULL)),
  CONSTRAINT `ck_borrowing_overdue` CHECK ((status = 'overdue' AND overdue_at IS NOT NULL) OR status = 'returned' OR (status NOT IN ('overdue','returned') AND overdue_at IS NULL)),
  CONSTRAINT `ck_borrowing_return` CHECK ((status = 'returned' AND returned_at IS NOT NULL AND processed_by IS NOT NULL AND return_processed_at IS NOT NULL AND return_condition IS NOT NULL AND late_days IS NOT NULL AND calculated_fine_amount IS NOT NULL AND fine_amount IS NOT NULL) OR (status <> 'returned' AND returned_at IS NULL AND processed_by IS NULL AND return_processed_at IS NULL AND return_condition IS NULL AND return_notes IS NULL AND late_days IS NULL AND calculated_fine_amount IS NULL AND fine_amount IS NULL AND fine_override_reason IS NULL)),
  CONSTRAINT `ck_borrowing_return_notes` CHECK (return_condition IS NULL OR return_condition = 'good' OR (return_notes IS NOT NULL AND CHAR_LENGTH(TRIM(return_notes)) > 0)),
  CONSTRAINT `ck_borrowing_return_time` CHECK (returned_at IS NULL OR (returned_at >= approved_at AND return_processed_at >= returned_at)),
  CONSTRAINT `ck_borrowing_fine` CHECK ((fine_amount IS NULL OR fine_amount >= 0) AND (calculated_fine_amount IS NULL OR calculated_fine_amount >= 0)),
  CONSTRAINT `ck_borrowing_calculation` CHECK (calculated_fine_amount IS NULL OR calculated_fine_amount = late_days * late_fine_per_day_snapshot),
  CONSTRAINT `ck_borrowing_override` CHECK (fine_amount IS NULL OR fine_amount = calculated_fine_amount OR (fine_override_reason IS NOT NULL AND CHAR_LENGTH(TRIM(fine_override_reason)) > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Laporan kerusakan manual/anggota/inspeksi; sekaligus workflow maintenance MVP.
CREATE TABLE `damage_reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `asset_id` BIGINT UNSIGNED NOT NULL,
  `borrowing_id` BIGINT UNSIGNED NULL,
  `source` ENUM('member_report','staff_report','return_inspection') NOT NULL,
  `reported_by` BIGINT UNSIGNED NOT NULL,
  `description` TEXT NOT NULL,
  `photo` VARCHAR(255) NULL,
  `severity` ENUM('minor','major') NULL,
  `status` ENUM('open','in_maintenance','resolved','noted') NOT NULL DEFAULT 'open',
  `handled_by` BIGINT UNSIGNED NULL,
  `assessed_at` DATETIME(6) NULL,
  `assessment_notes` TEXT NULL,
  `maintenance_started_at` DATETIME(6) NULL,
  `resolution_notes` TEXT NULL,
  `resolved_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `maintenance_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN status = 'in_maintenance' THEN 1 ELSE NULL END) VIRTUAL,
  `inspection_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN source = 'return_inspection' THEN 1 ELSE NULL END) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_damage_active_maintenance` (`asset_id`, `maintenance_guard`),
  UNIQUE KEY `uq_damage_return_inspection` (`borrowing_id`, `inspection_guard`),
  UNIQUE KEY `uq_damage_id_asset_org` (`id`, `asset_id`, `organization_id`),
  KEY `ix_damage_inbox` (`organization_id`, `status`, `created_at`),
  KEY `ix_damage_severity` (`organization_id`, `severity`, `created_at`),
  CONSTRAINT `fk_damage_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_damage_asset_tenant` FOREIGN KEY (`asset_id`, `organization_id`) REFERENCES `assets` (`id`, `organization_id`),
  CONSTRAINT `fk_damage_borrowing_tenant` FOREIGN KEY (`borrowing_id`, `asset_id`, `organization_id`) REFERENCES `borrowings` (`id`, `asset_id`, `organization_id`),
  CONSTRAINT `fk_damage_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_damage_handled_by` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_damage_description` CHECK (CHAR_LENGTH(TRIM(description)) > 0),
  CONSTRAINT `ck_damage_source` CHECK (source <> 'return_inspection' OR (borrowing_id IS NOT NULL AND severity IS NOT NULL)),
  CONSTRAINT `ck_damage_assessment` CHECK ((severity IS NULL AND assessed_at IS NULL) OR (severity IS NOT NULL AND assessed_at IS NOT NULL AND handled_by IS NOT NULL)),
  CONSTRAINT `ck_damage_maintenance` CHECK (status <> 'in_maintenance' OR (maintenance_started_at IS NOT NULL AND severity IS NOT NULL AND handled_by IS NOT NULL)),
  CONSTRAINT `ck_damage_resolved` CHECK ((status = 'resolved' AND resolved_at IS NOT NULL AND maintenance_started_at IS NOT NULL AND severity IS NOT NULL AND resolution_notes IS NOT NULL AND CHAR_LENGTH(TRIM(resolution_notes)) > 0 AND handled_by IS NOT NULL) OR (status <> 'resolved' AND resolved_at IS NULL)),
  CONSTRAINT `ck_damage_noted` CHECK (status <> 'noted' OR (severity IS NOT NULL AND handled_by IS NOT NULL AND resolution_notes IS NOT NULL AND CHAR_LENGTH(TRIM(resolution_notes)) > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Timeline append-only per aset; metadata yang tampil ke Member harus disaring.
CREATE TABLE `asset_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `asset_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `borrowing_id` BIGINT UNSIGNED NULL,
  `damage_report_id` BIGINT UNSIGNED NULL,
  `event` VARCHAR(40) NOT NULL,
  `description` TEXT NOT NULL,
  `metadata` JSON NULL,
  `event_key` VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asset_log_event` (`event_key`),
  KEY `ix_asset_timeline` (`organization_id`, `asset_id`, `created_at`, `id`),
  KEY `ix_asset_log_event` (`organization_id`, `event`, `created_at`),
  CONSTRAINT `fk_log_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_log_asset_tenant` FOREIGN KEY (`asset_id`, `organization_id`) REFERENCES `assets` (`id`, `organization_id`),
  CONSTRAINT `fk_log_borrowing_tenant` FOREIGN KEY (`borrowing_id`, `asset_id`, `organization_id`) REFERENCES `borrowings` (`id`, `asset_id`, `organization_id`),
  CONSTRAINT `fk_log_damage_tenant` FOREIGN KEY (`damage_report_id`, `asset_id`, `organization_id`) REFERENCES `damage_reports` (`id`, `asset_id`, `organization_id`),
  CONSTRAINT `fk_asset_log_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_asset_log_event` CHECK (event IN ('created','updated','qr_generated','borrow_requested','borrow_approved','borrow_rejected','borrow_cancelled','borrowed','returned','inspected','overdue','damage_reported','damage_assessed','damage_noted','maintenance_started','maintenance_finished','marked_lost','marked_found','deleted'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Audit append-only lintas entitas: membership, settings, akun, aset, dan transaksi.
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NULL,
  `actor_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(80) NOT NULL,
  `subject_type` VARCHAR(60) NOT NULL,
  `subject_id` BIGINT UNSIGNED NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `metadata` JSON NULL,
  `event_key` VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_audit_event` (`event_key`),
  KEY `ix_audit_org` (`organization_id`, `created_at`, `id`),
  KEY `ix_audit_actor` (`actor_id`, `created_at`),
  KEY `ix_audit_subject` (`subject_type`, `subject_id`, `created_at`),
  CONSTRAINT `fk_audit_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  CONSTRAINT `fk_audit_actor_id` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ck_audit_action` CHECK (CHAR_LENGTH(TRIM(action)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Notifikasi database dengan bentuk polymorphic Laravel dan tambahan organization_id.
CREATE TABLE `notifications` (
  `id` CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `organization_id` BIGINT UNSIGNED NULL,
  `data` TEXT NOT NULL,
  `event_key` VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin NULL,
  `read_at` DATETIME(6) NULL,
  `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_notification_recipient_event` (`notifiable_type`, `notifiable_id`, `event_key`),
  KEY `ix_notification_owner` (`notifiable_type`, `notifiable_id`, `read_at`, `created_at`),
  KEY `ix_notification_org` (`organization_id`, `created_at`),
  CONSTRAINT `fk_notification_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabel autentikasi pendukung mengikuti pola Laravel; bukan entitas domain.
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(254) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` DATETIME(6) NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
