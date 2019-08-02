-- Removing Keys, Foreign keys and columns in Users table in order to maintain data integrity

ALTER TABLE `Users`
  DROP KEY `country_id`,
  DROP KEY `state_id`,
  DROP FOREIGN KEY `fk_country_id`,
  DROP FOREIGN KEY `fk_us_state_id`,
  DROP COLUMN `country_id`,
  DROP COLUMN `state_id`;
