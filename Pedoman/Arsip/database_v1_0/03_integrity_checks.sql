-- Read-only diagnostics. Select the intended database before running.
-- First six queries should return ZERO rows on a consistent implementation.

-- 1. Borrowed asset must have exactly one active loan; all other assets zero.
SELECT a.id AS asset_id, a.organization_id, a.status,
       SUM(CASE WHEN b.status IN ('borrowed','overdue') THEN 1 ELSE 0 END) AS active_loans
FROM assets a
LEFT JOIN borrowings b ON b.asset_id = a.id AND b.organization_id = a.organization_id
GROUP BY a.id, a.organization_id, a.status
HAVING (a.status = 'borrowed' AND active_loans <> 1)
    OR (a.status <> 'borrowed' AND active_loans <> 0);

-- 2. Every nondeleted organization, including archived organizations, needs an active Admin.
SELECT o.id AS organization_id, o.name
FROM organizations o
LEFT JOIN organization_user m ON m.organization_id = o.id AND m.status = 'active' AND m.role = 'admin'
LEFT JOIN users u ON u.id = m.user_id AND u.deleted_at IS NULL
WHERE o.deleted_at IS NULL
GROUP BY o.id, o.name
HAVING COUNT(u.id) = 0;

-- 3. Maintenance state must match exactly one active maintenance report.
SELECT a.id AS asset_id, a.status,
       SUM(CASE WHEN d.status = 'in_maintenance' THEN 1 ELSE 0 END) AS active_maintenance
FROM assets a
LEFT JOIN damage_reports d ON d.asset_id = a.id AND d.organization_id = a.organization_id
GROUP BY a.id, a.status
HAVING (a.status = 'maintenance' AND active_maintenance <> 1)
    OR (a.status <> 'maintenance' AND active_maintenance <> 0);

-- 4. Active loans must still have an active borrower membership/account.
SELECT b.id AS borrowing_id, b.user_id, b.organization_id
FROM borrowings b
LEFT JOIN users u ON u.id = b.user_id
LEFT JOIN organization_user m ON m.organization_id = b.organization_id AND m.user_id = b.user_id AND m.status = 'active'
WHERE b.status IN ('borrowed','overdue')
  AND (u.deleted_at IS NOT NULL OR m.id IS NULL);

-- 5. No active loan or maintenance should be stranded under an archived/deleted organization.
SELECT o.id AS organization_id, 'borrowing' AS resource_type, b.id AS resource_id
FROM organizations o JOIN borrowings b ON b.organization_id = o.id
WHERE (o.archived_at IS NOT NULL OR o.deleted_at IS NOT NULL) AND b.status IN ('borrowed','overdue')
UNION ALL
SELECT o.id, 'maintenance', d.id
FROM organizations o JOIN damage_reports d ON d.organization_id = o.id
WHERE (o.archived_at IS NOT NULL OR o.deleted_at IS NOT NULL) AND d.status = 'in_maintenance';

-- 6. A return inspection creates a single matching report for minor/major, none for good/lost.
SELECT b.id AS borrowing_id, b.return_condition,
       COUNT(d.id) AS inspection_reports,
       SUM(CASE WHEN d.severity = 'minor' THEN 1 ELSE 0 END) AS minor_reports,
       SUM(CASE WHEN d.severity = 'major' THEN 1 ELSE 0 END) AS major_reports
FROM borrowings b
LEFT JOIN damage_reports d ON d.borrowing_id = b.id AND d.asset_id = b.asset_id
                         AND d.organization_id = b.organization_id AND d.source = 'return_inspection'
WHERE b.status = 'returned'
GROUP BY b.id, b.return_condition
HAVING (b.return_condition = 'minor_damage' AND (inspection_reports <> 1 OR minor_reports <> 1))
    OR (b.return_condition = 'major_damage' AND (inspection_reports <> 1 OR major_reports <> 1))
    OR (b.return_condition IN ('good','lost') AND inspection_reports <> 0);

-- EXAMPLES BELOW: bind these values with prepared parameters in application code.
SET @organization_id = 1;
SET @user_id = 1;
SET @asset_id = 1;
-- The caller supplies the current date in Asia/Jakarta, independently of the session UTC date.
SET @business_today = '2026-10-20';

-- Dashboard asset composition; no independently editable statistics table.
SELECT status, COUNT(*) AS total
FROM assets
WHERE organization_id = @organization_id AND deleted_at IS NULL
GROUP BY status;

-- Scheduler candidates; processing must re-check under transaction/row lock.
SELECT id, organization_id, asset_id, user_id, due_date
FROM borrowings
WHERE status = 'borrowed' AND due_date < @business_today
ORDER BY id;

-- Asset timeline; filter/redact fields according to the viewer's role.
SELECT id, user_id, borrowing_id, damage_report_id, event, description, created_at
FROM asset_logs
WHERE organization_id = @organization_id AND asset_id = @asset_id
ORDER BY created_at DESC, id DESC
LIMIT 30;

-- Top five assets by approved borrowing count, not pending requests.
SELECT a.id, a.code, a.name, COUNT(*) AS borrowing_count
FROM borrowings b
JOIN assets a ON a.id = b.asset_id AND a.organization_id = b.organization_id
WHERE b.organization_id = @organization_id AND b.approved_at IS NOT NULL
GROUP BY a.id, a.code, a.name
ORDER BY borrowing_count DESC, a.id
LIMIT 5;

-- Borrower history remains readable even if the asset/user is soft-deleted.
-- Authorization still limits this to the signed-in user's own history.
SELECT b.id, a.code, a.name, b.status, b.borrow_date, b.due_date, b.returned_at, b.fine_amount
FROM borrowings b
JOIN assets a ON a.id = b.asset_id AND a.organization_id = b.organization_id
WHERE b.user_id = @user_id
ORDER BY b.created_at DESC, b.id DESC
LIMIT 30;
