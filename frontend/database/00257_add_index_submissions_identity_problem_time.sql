-- Add indexes to optimize the submission gap query (Issue #8854)
-- These indexes support the query in Submissions::isInsideSubmissionGapSingle()
-- which checks the last submission time for a given identity and problem

-- Index for queries without problemset_id
CREATE INDEX idx_submissions_identity_problem_time
  ON Submissions (identity_id, problem_id, time DESC);

-- Index for queries with problemset_id
CREATE INDEX idx_submissions_identity_problem_problemset_time
  ON Submissions (identity_id, problem_id, problemset_id, time DESC);
