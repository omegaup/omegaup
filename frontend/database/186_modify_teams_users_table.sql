-- Remove keys
ALTER TABLE `Team_Users`
  DROP PRIMARY KEY,
  DROP KEY `user_id`,
  DROP FOREIGN KEY `fk_tuu_user_id`;

-- Add identity_id column
ALTER TABLE `Team_Users`
  ADD COLUMN `identity_id` int NOT NULL COMMENT 'Id de la identidad que pertenece al equipo';

-- Update column identity_id
UPDATE
  `Team_Users`
SET
  `identity_id` = (
    SELECT
      `identity_id`
    FROM
      `Identities`
    WHERE
      `Team_Users`.`user_id` = `Identities`.`user_id`);

-- Remove user_id column
ALTER TABLE `Team_Users`
  DROP COLUMN `user_id`;

ALTER TABLE `Team_Users`
  ADD PRIMARY KEY (`team_id`, `identity_id`),
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_tui_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
