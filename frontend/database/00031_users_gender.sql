-- Users
ALTER TABLE `Users`
  ADD COLUMN `gender` enum('female','male','other','decline') DEFAULT NULL AFTER `birth_date`;