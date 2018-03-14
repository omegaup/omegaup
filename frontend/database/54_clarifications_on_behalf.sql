-- Clarifications
ALTER TABLE `Clarifications`
  ADD COLUMN `receiver_id` int(11) DEFAULT NULL COMMENT 'Usuario que recibirá el mensaje' AFTER `author_id`,
  ADD CONSTRAINT `fk_cu_receiver_id` FOREIGN KEY (`author_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
