-- omegaup:mentor
INSERT INTO
  `Roles` (`role_id`,`name`,`description`)
VALUES
  (5,'Mentor','Miembro del equipo de mentoreo');

INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (5, 1);

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
	5,
	'omegaup:mentor',
	'omegaup:mentor',
	'Equipo de mentores de omegaup'
);

SET @mentor_group_id = LAST_INSERT_ID();

INSERT INTO `Group_Roles` VALUES(@mentor_group_id, 5, 5);
