-- Add the unique index to the Users_Badges table
ALTER TABLE `Users_Badges`
  ADD UNIQUE KEY `user_badge` (`badge_alias`, `user_id`);
