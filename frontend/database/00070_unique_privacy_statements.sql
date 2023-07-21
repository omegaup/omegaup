ALTER TABLE `PrivacyStatements`
  ADD UNIQUE KEY `type_git_object_id` (`type`, `git_object_id`);
