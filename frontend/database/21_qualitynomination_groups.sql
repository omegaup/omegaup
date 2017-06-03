-- omegaup:quality-reviewer-admin
INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (2, 1);

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
	2,
	'omegaup:quality-reviewer-admin',
	'omegaup:quality-reviewer-admin',
	'Administradores de omegaup:quality-reviewer'
);

SET @quality_reviewer_admin_id = LAST_INSERT_ID();

-- omegaup:quality-reviewer
INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (3, 1);

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
	3,
	'omegaup:quality-reviewer',
	'omegaup:quality-reviewer',
	'Jueces del programa de Problemas de Calidad'
);

INSERT INTO `Group_Roles` VALUES(@quality_reviewer_admin_id, 1, 3);
