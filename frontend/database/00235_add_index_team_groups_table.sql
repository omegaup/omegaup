-- Add index to the Team_Groups table
ALTER TABLE `Team_Groups`
  ADD KEY `idx_acl_id_create_time_alias_description` (`create_time`, `alias`, `description`);
