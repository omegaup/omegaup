-- Add composite index on User_Roles for getAdmins query optimization.
-- The query filters by role_id = ? AND acl_id IN (?, ?) and joins on user_id.
-- This index allows index range scan instead of full table scan + hash join.
CREATE INDEX idx_user_roles_role_acl_user ON User_Roles (role_id, acl_id, user_id);
