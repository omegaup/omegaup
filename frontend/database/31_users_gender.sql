-- Users
ALTER TABLE `Users`
  ADD COLUMN `gender` enum('female', 'male', 'prefer_not_answer') NULL AFTER `birth_date`;