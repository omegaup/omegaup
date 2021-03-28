-- omegaup:certificate-generator
INSERT INTO
  `Roles` (`role_id`,`name`,`description`)
VALUES
  (8,'CertificateGenerator','Miembro del grupo que tiene privilegios para generar diplomas');

INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (8, 1);

INSERT INTO `Groups_` (`acl_id`, `alias`, `name`, `description`) VALUES (
  8,
  'omegaup:certificate-generator',
  'omegaup:certificate-generator',
  'Equipo de usuarios con privilegios para generar diplomas'
);

SET @certificate_generator_group_id = LAST_INSERT_ID();

INSERT INTO `Group_Roles` VALUES(@certificate_generator_group_id, 8, 8);
