-- Removing Keys, Foreign keys and columns in Users table in order to maintain data integrity
ALTER TABLE `Users`
  DROP KEY `school_id`,
  DROP FOREIGN KEY `school_id`,
  DROP COLUMN `name`,
  DROP COLUMN `school_id`,
  DROP COLUMN `gender`;
