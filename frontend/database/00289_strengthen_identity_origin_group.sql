ALTER TABLE `Groups_Identities`
  MODIFY COLUMN `group_id` int NOT NULL COMMENT 'Id del grupo',
  MODIFY COLUMN `identity_id` int NOT NULL COMMENT 'Id de la identidad miembro del grupo',
  MODIFY COLUMN `share_user_information` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta de la identidad miembro sobre si está de acuerdo en divulgar su información.',
  MODIFY COLUMN `accept_teacher` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta de la identidad miembro sobre si acepta al organizador como su maestro.',
  MODIFY COLUMN `is_invited` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad ingresó al grupo por invitación o mediante registro abierto',
  ADD COLUMN `is_origin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad fue creada originalmente en este grupo',
  ADD KEY `idx_groups_identities_origin` (`identity_id`, `is_origin`);

UPDATE `Groups_Identities` AS `gi`
INNER JOIN `Groups_` AS `g` ON `g`.`group_id` = `gi`.`group_id`
INNER JOIN `Identities` AS `i` ON `i`.`identity_id` = `gi`.`identity_id`
SET `gi`.`is_origin` = 1
WHERE LOCATE(':', `i`.`username`) > 0
  AND LOCATE(':', `i`.`username`, LOCATE(':', `i`.`username`) + 1) = 0
  AND SUBSTRING_INDEX(`i`.`username`, ':', 1) = `g`.`alias`;

ALTER TABLE `Team_Users`
  ADD COLUMN `is_origin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad fue creada originalmente en este equipo',
  ADD KEY `idx_team_users_origin` (`identity_id`, `is_origin`);

UPDATE `Team_Users` AS `tu`
INNER JOIN `Teams` AS `t` ON `t`.`team_id` = `tu`.`team_id`
INNER JOIN `Team_Groups` AS `tg` ON `tg`.`team_group_id` = `t`.`team_group_id`
INNER JOIN `Identities` AS `i` ON `i`.`identity_id` = `tu`.`identity_id`
SET `tu`.`is_origin` = 1
WHERE LOCATE(':', `i`.`username`) > 0
  AND LOCATE(':', `i`.`username`, LOCATE(':', `i`.`username`) + 1) = 0
  AND SUBSTRING_INDEX(`i`.`username`, ':', 1) = `tg`.`alias`
  AND NOT EXISTS (
    SELECT 1
    FROM `Groups_Identities` AS `gi`
    WHERE `gi`.`identity_id` = `tu`.`identity_id`
      AND `gi`.`is_origin` = 1
  );
