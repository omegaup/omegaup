-- Clarifications
ALTER TABLE `Clarifications`
  ADD COLUMN `receiver_id` int(11) DEFAULT NULL COMMENT 'Usuario que recibirá el mensaje' AFTER `author_id`,
  ADD KEY `receiver_id` (`receiver_id`),
  ADD CONSTRAINT `fk_ci_receiver_id` FOREIGN KEY (`receiver_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
