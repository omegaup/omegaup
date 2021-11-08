-- Auth_Tokens: addin acting identity id for no main identities for a user
ALTER TABLE `Auth_Tokens`
  ADD COLUMN `acting_identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario que indica que no está actuando como identidad principal' AFTER `identity_id`,
  ADD KEY `acting_identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_ati_acting_identity_id` FOREIGN KEY (`acting_identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
