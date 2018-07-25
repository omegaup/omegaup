-- omegaup:organizer
INSERT INTO
  `Roles` (`role_id`,`name`,`description`)
VALUES
  (6,'group_identity_creator','Miembro del grupo que tiene privilegios para crear identidades a partir de un grupo');

INSERT INTO `ACLs` (`owner_id`) VALUES (1);

SET @acl_id = LAST_INSERT_ID();

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
  @acl_id,
  'omegaup:group-identity-creator',
  'omegaup:group-identity-creator',
  'Equipo de usuarios con privilegios para crear identidades a partir de un grupo'
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
  