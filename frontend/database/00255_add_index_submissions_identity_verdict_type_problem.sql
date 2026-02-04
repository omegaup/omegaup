-- Add index to Submissions table on identity_id, verdict, type, problem_id columns
-- to optimize the getTagsDistributionForSolvedProblems query
CREATE INDEX idx_submissions_identity_verdict_type_problem ON Submissions (identity_id, verdict, type, problem_id);
