--
-- Add a FULLTEXT index on `Courses`.`name` to speed up name searches.
--
ALTER TABLE `Courses`
  ADD FULLTEXT KEY `idx_courses_name` (`name`);
