-- Add index to the Courses table for efficient name searching
ALTER TABLE `Courses` ADD INDEX `idx_name` (`name`);
