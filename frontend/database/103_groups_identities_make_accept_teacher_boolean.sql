ALTER TABLE `Groups_Identities`
  CHANGE COLUMN `accept_teacher` `accept_t` enum('yes','no');

ALTER TABLE `Groups_Identities`
  ADD `accept_teacher` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.';

UPDATE `Groups_Identities`
  SET `accept_teacher` = (`accept_t` = 'yes')
  WHERE `accept_t` IS NOT NULL;

ALTER TABLE `Groups_Identities`
  DROP COLUMN `accept_t`;
