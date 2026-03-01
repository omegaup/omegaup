-- Add composite index to optimize getAllContestsAdminedByIdentity query.
-- The (acl_id, archived) index allows efficient lookup when joining on acl_id
-- and filtering by archived = 0 in the Group_Roles branch.
CREATE INDEX idx_contests_acl_archived ON Contests (acl_id, archived);
