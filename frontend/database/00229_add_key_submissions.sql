-- Add index to the Submission table
ALTER TABLE `Submissions`
  ADD KEY `verdict_type_time` (`verdict`, `type`, `time`);

