-- Add index to the Problemsets table
ALTER TABLE `Problemsets`
  ADD KEY `idx_assignment_id_problemset_id_acl_id` (`assignment_id`, `problemset_id`, `acl_id`);

-- Add index to the Assignments table
ALTER TABLE `Assignments`
    ADD KEY `idx_finish_time_assignment` (`finish_time`, `assignment_id`);
