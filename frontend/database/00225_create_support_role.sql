-- omegaup:support
INSERT INTO
  `Roles` (`role_id`, `name`, `description`)
VALUES
  (6, 'Support', 'Miembro del grupo de soporte')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

INSERT INTO `Group_Roles` (`group_id`, `role_id`, `acl_id`)
VALUES ((SELECT `group_id` FROM `Groups_` WHERE `alias` = 'omegaup:support'), 6, 6)
ON DUPLICATE KEY UPDATE
  `role_id` = VALUES(`role_id`),
  `acl_id` = VALUES(`acl_id`);
