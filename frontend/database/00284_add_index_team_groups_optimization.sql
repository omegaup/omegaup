-- Add composite index to Team_Groups to optimize query filtering by acl_id with ordering by create_time
ALTER TABLE `Team_Groups`
  ADD KEY `idx_team_groups_acl_create_alias_desc_name` (`acl_id`, `create_time`, `alias`, `description`, `name`);
