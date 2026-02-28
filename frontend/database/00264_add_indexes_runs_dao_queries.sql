-- Index for getProblemsetRuns: filter Submissions by problemset_id, problem_id, status, type
-- Supports the join from Problemset_Problems and the WHERE clause
CREATE INDEX idx_submissions_problemset_problem_status_type
  ON Submissions (problemset_id, problem_id, status, type);

-- Index for getByProblemset: filter by problemset_id and ORDER BY time DESC
CREATE INDEX idx_submissions_problemset_time
  ON Submissions (problemset_id, time DESC);

-- Index for searchWithRunIdGreaterThan: filter by problem_id, range on submission_id, ORDER BY submission_id
CREATE INDEX idx_submissions_problem_submission
  ON Submissions (problem_id, submission_id);

-- Index for getBestProblemScoreInProblemset: filter by identity_id, problemset_id, problem_id, type
-- Extends idx_submissions_identity_problem_problemset_time with type for better selectivity
CREATE INDEX idx_submissions_identity_problemset_problem_type
  ON Submissions (identity_id, problemset_id, problem_id, type);
