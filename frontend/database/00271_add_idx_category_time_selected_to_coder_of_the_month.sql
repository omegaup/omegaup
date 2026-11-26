-- Add covering index on (category, time, selected_by) to speed up the
-- getCodersOfTheMonth query which filters and joins on these three columns.
ALTER TABLE `Coder_Of_The_Month`
ADD INDEX `idx_category_time_selected` (`category`, `time`, `selected_by`);
