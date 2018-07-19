-- omegaup:organizer
INSERT INTO
  `Roles` (`role_id`,`name`,`description`)
VALUES
  (6,'Organizer','Miembro del equipo de organizadores de concursos');

INSERT INTO `ACLs` (`owner_id`) VALUES (1);

SET @acl_id = LAST_INSERT_ID();

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
  @acl_id,
  'omegaup:organizer',
  'omegaup:organizer',
  'Equipo de organizadores de omegaup'
);

SET @organizer_group_id = LAST_INSERT_ID();

INSERT INTO `Group_Roles` VALUES(@organizer_group_id, 6, 6);

-- Alter Identities table, adding and updating gender column

ALTER TABLE `Identities`
  ADD COLUMN `gender` enum('female','male','other','decline') COMMENT 'Género de la identidad';

UPDATE
  `Identities`
INNER JOIN
  `Users`
ON
  `Users`.`user_id` = `Identities`.`user_id`
SET
  `Identities`.`gender`= `Users`.`gender`;
  