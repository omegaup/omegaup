-- Add FULLTEXT index to the Courses table for efficient name searching
ALTER TABLE `Courses` ADD FULLTEXT `ft_name` (`name`);
