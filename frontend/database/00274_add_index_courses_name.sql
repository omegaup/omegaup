-- Add index to optimize exact course name lookups.
ALTER TABLE `Courses`
    ADD INDEX `idx_courses_name` (`name`);
