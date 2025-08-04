-- Add index to the Team_Groups table
ALTER TABLE `Team_Groups`
  ADD KEY `idx_create_time` (`create_time`);
