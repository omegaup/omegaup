-- Add index to the Users_Badges table
ALTER TABLE `Users_Badges`
  ADD KEY `user_badge` (`badge_alias`);
