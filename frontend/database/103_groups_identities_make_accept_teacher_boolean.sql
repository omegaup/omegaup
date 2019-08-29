ALTER TABLE `Groups_Identities`
  CHANGE COLUMN `accept_teacher` `accept_t` enum('yes','no');

ALTER TABLE `Groups_Identities`
  ADD `accept_teacher` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.';

UPDATE `Groups_Identities`
  SET `accept_teacher` = 1
  WHERE `accept_t` = 'yes';

UPDATE `Groups_Identities`
  SET `accept_teacher` = 0
  WHERE `accept_t` = 'no';

ALTER TABLE `Groups_Identities`
  DROP COLUMN `accept_t`;
