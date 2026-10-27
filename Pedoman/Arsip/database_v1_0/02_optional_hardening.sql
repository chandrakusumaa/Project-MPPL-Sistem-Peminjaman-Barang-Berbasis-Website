-- Optional database hardening. Run after 01_schema_mysql8.sql.
-- Requires CREATE TRIGGER privilege. A migration runner must handle DELIMITER locally.
-- No ordinary application role should have TRUNCATE/ALTER/DROP privileges on log tables.
DELIMITER $$

CREATE TRIGGER `trg_asset_logs_no_update` BEFORE UPDATE ON `asset_logs`
FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Append-only history cannot be changed'$$

CREATE TRIGGER `trg_asset_logs_no_delete` BEFORE DELETE ON `asset_logs`
FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Append-only history cannot be changed'$$

CREATE TRIGGER `trg_audit_logs_no_update` BEFORE UPDATE ON `audit_logs`
FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Append-only history cannot be changed'$$

CREATE TRIGGER `trg_audit_logs_no_delete` BEFORE DELETE ON `audit_logs`
FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Append-only history cannot be changed'$$

CREATE TRIGGER `trg_asset_identity_immutable` BEFORE UPDATE ON `assets`
FOR EACH ROW
BEGIN
  IF NEW.code <> OLD.code OR NEW.organization_id <> OLD.organization_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Asset code and owner organization are immutable';
  END IF;
END$$

CREATE TRIGGER `trg_org_slug_immutable` BEFORE UPDATE ON `organizations`
FOR EACH ROW
BEGIN
  IF NEW.slug <> OLD.slug THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Organization slug is immutable for QR stability';
  END IF;
END$$

CREATE TRIGGER `trg_borrowing_snapshot_immutable` BEFORE UPDATE ON `borrowings`
FOR EACH ROW
BEGIN
  IF OLD.status IN ('borrowed','overdue','returned') AND (
    NOT (NEW.late_fine_per_day_snapshot <=> OLD.late_fine_per_day_snapshot) OR
    NOT (NEW.max_borrow_days_snapshot <=> OLD.max_borrow_days_snapshot) OR
    NEW.asset_id <> OLD.asset_id OR NEW.organization_id <> OLD.organization_id OR NEW.user_id <> OLD.user_id OR
    NEW.borrow_date <> OLD.borrow_date OR NEW.due_date <> OLD.due_date OR
    NOT (NEW.approved_by <=> OLD.approved_by) OR NOT (NEW.approved_at <=> OLD.approved_at)
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Approved borrowing identity and policy snapshot are immutable';
  END IF;
  IF OLD.status IN ('returned','rejected','cancelled') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Terminal borrowing is immutable; use an audited correction process';
  END IF;
END$$

DELIMITER ;
