-- Add index to the Submission table
ALTER TABLE `Submissions`
  ADD KEY `submissions_verdict_type_time` (`verdict`, `type`, `time`);

