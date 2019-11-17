-- Remove Users graduation_date
ALTER TABLE `Users`
DROP COLUMN `graduation_date`;

-- Remove Identities school_id
ALTER TABLE `Identities`
DROP FOREIGN KEY `fk_is_school_id`,
DROP COLUMN `school_id`;
