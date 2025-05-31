-- Add index to the Assignments table
ALTER TABLE `Assignments`
    ADD KEY `idx_finish_time` (`finish_time`);
