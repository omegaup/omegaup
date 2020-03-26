-- Add current_identity_school_id to Identities
ALTER TABLE `Identities`
ADD COLUMN `current_identity_school_id` int(11) DEFAULT NULL,
ADD KEY `current_identity_school_id` (`current_identity_school_id`),
ADD CONSTRAINT `fk_iis_current_identity_school_id` FOREIGN KEY (`current_identity_school_id`) REFERENCES `Identities_Schools`(`identity_school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Fill current_identity_school_id on Identities table
UPDATE
    `Identities` `i`
INNER JOIN
    `Identities_Schools` `is`
ON
    `is`.`identity_id` = `i`.`identity_id` AND `is`.`school_id` = `i`.`school_id`
SET
    `i`.`current_identity_school_id` = `is`.`identity_school_id`;
