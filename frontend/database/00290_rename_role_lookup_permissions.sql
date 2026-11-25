-- Renames the role lookup permissions after moving the endpoints from the Role
-- controller to the Admin controller (issue #10174).
UPDATE `Permissions`
SET `name` = '/api/admin/getRoles/'
WHERE `name` = '/api/role/list/';

UPDATE `Permissions`
SET `name` = '/api/admin/getRoleDetails/'
WHERE `name` = '/api/role/details/';

UPDATE `Permissions`
SET `name` = '/api/admin/getRoleAssignments/'
WHERE `name` = '/api/role/assignments/';