-- Add covering index for NOT EXISTS subquery and time-range filters in getCodersOfTheMonth.
-- The index (category, time, selected_by) enables:
-- 1. Efficient lookup in the "months with selected coder" subquery (selected_by IS NOT NULL)
-- 2. Better time-range filtering for the main query (category=? AND time IN range)
-- 3. Index-only scan when checking existence, avoiding table access
ALTER TABLE `Coder_Of_The_Month`
ADD INDEX `idx_category_time_selected` (`category`, `time`, `selected_by`);
