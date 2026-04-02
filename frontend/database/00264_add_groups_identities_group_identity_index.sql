ALTER TABLE `Groups_Identities`
  ADD KEY `idx_groups_identities_group_identity` (`group_id`, `identity_id`);
