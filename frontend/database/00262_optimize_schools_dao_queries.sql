-- Optimize Schools DAO queries (getUsersFromSchool, countActiveSchools)
-- Fixes full table scans and correlated subquery inefficiencies in DAO/Schools.php
--
-- Indexes support:
-- 1. getUsersFromSchool: created_problems subquery (Problems by acl_id + visibility)
-- 2. getUsersFromSchool: organized_contests subquery (Contests by acl_id)
-- 3. countActiveSchools: Submissions filtered by time range, joined on identity_id

-- Problems: composite index for created_problems subquery
-- Joins ACLs on acl_id, filters by visibility
CREATE INDEX idx_problems_acl_visibility ON Problems (acl_id, visibility);

-- Contests: covering index for organized_contests subquery
-- Joins on acl_id, references problemset_id
CREATE INDEX idx_contests_acl_problemset ON Contests (acl_id, problemset_id);

-- Identities: covering index for school members join
-- Join on current_identity_school_id, select identity_id, user_id, username
CREATE INDEX idx_identities_current_school_user ON Identities (
    current_identity_school_id,
    identity_id,
    user_id
);

-- Submissions: index for countActiveSchools time-range filter + identity join
CREATE INDEX idx_submissions_time_identity ON Submissions (time, identity_id);
