-- Adding new constraints in Identities table
ALTER TABLE `Identities`
  ADD KEY `language_id` (`language_id`),
  ADD CONSTRAINT `fk_il_language_id` FOREIGN KEY (`language_id`) REFERENCES `Languages` (`language_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Removing Keys, Foreign keys and columns in Users table in order to maintain data integrity
ALTER TABLE `Users`
  DROP COLUMN `language_id`;
  