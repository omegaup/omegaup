-- omegaup:support
INSERT INTO
  `Roles` (`role_id`, `name`, `description`)
VALUES
  (6, 'Support', 'Miembro del grupo de soporte');

INSERT INTO `Group_Roles` (`group_id`, `role_id`, `acl_id`)
VALUES ((SELECT `group_id` FROM `Groups_` WHERE `alias` = 'omegaup:support'), 6, 6);
