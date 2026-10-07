-- Drop idx_problems_allow_tags from Problems.
-- This index (problem_id, allow_user_add_tags) starts with the PRIMARY KEY column,
-- making it a covering index that MySQL's optimizer abuses: it scans all ~20k Problems
-- rows in the wrong join direction instead of driving from Problems_Tags filtered by
-- tag_id. The idx_pt_tag_problem_source index on Problems_Tags is sufficient.
ALTER TABLE `Problems`
  DROP INDEX `idx_problems_allow_tags`;
