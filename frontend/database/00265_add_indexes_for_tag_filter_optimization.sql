-- Add indexes to optimize addTagFilter() query performance
-- This migration addresses slow queries when filtering problems by tags

-- Index for Tags table to speed up tag name lookups
-- Used in: Problems::addTagFilter() when pre-fetching tag IDs
ALTER TABLE `Tags`
  ADD INDEX `idx_tags_name` (`name`);

-- Composite index for Problems_Tags to optimize the main subquery
-- Covers tag_id, problem_id, and source columns used in WHERE and GROUP BY
ALTER TABLE `Problems_Tags`
  ADD INDEX `idx_pt_tag_problem_source` (`tag_id`, `problem_id`, `source`);

-- Index for Problems to help with the allow_user_add_tags condition
-- This is a covering index that avoids accessing the full table row
ALTER TABLE `Problems`
  ADD INDEX `idx_problems_allow_tags` (`problem_id`, `allow_user_add_tags`);
