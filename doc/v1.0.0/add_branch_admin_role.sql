-- Add Branch Admin role if it doesn't exist
INSERT INTO `roles` (`code`, `name`, `description`, `created_at`)
SELECT 'BRANCH_ADMIN', 'Branch Admin', 'Branch Administrator with full access to their assigned branch', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `roles` WHERE `code` = 'BRANCH_ADMIN'
);
