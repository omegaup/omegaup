-- omegaup:support
INSERT INTO
  `Roles` (`role_id`,`name`,`description`)
VALUES
  (6,'Support','Miembro del equipo de soporte');

INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (6, 1);

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
  6,
  'omegaup:support',
  'omegaup:support',
  'Equipo de soporte de omegaup'
);

SET @support_group_id = LAST_INSERT_ID();

INSERT INTO `Group_Roles` VALUES(@support_group_id, 6, 6);
