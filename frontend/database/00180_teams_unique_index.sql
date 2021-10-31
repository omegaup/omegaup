ALTER TABLE `Teams`
    ADD UNIQUE KEY `team_group_identity` (`team_group_id`, `identity_id`);