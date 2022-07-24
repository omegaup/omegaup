-- omegaup:teaching-assistant
INSERT INTO
  `Roles` (`role_id`,`name`,`description`)
VALUES
  (9,'TeachingAssistant','Usuario con privilegios para ver envíos, dar feedback y descalificar envíos en su curso asignado');

INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (9, 1);

INSERT INTO `Groups_` (`acl_id`, `alias`, `name`, `description`) VALUES (
  9,
  'omegaup:teaching-assistant',
  'omegaup:teaching-assistant',
  'Equipo de usuarios con privilegios para generar retroalimentaciones'
);

SET @teaching_assistant_group_id = LAST_INSERT_ID();

INSERT INTO `Group_Roles` VALUES(@teaching_assistant_group_id, 9, 9);

