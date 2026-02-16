-- Add index to the Users table
ALTER TABLE `Users`
  ADD KEY `idx_is_private` (`is_private`);

-- Add index to the Submissions table
ALTER TABLE `Submissions`
  ADD KEY `idx_time_status` (`time`, `status`);
