ALTER TABLE `Problems_Forfeited`
  DROP PRIMARY KEY,
  DROP COLUMN `problem_forfeited_id`,
  ADD PRIMARY KEY(`user_id`, `problem_id`);