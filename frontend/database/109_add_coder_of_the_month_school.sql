-- Add school_id to Coder_Of_The_Month
ALTER TABLE `Coder_Of_The_Month`
ADD COLUMN `school_id` int(11) DEFAULT NULL,
ADD KEY `school_id` (`school_id`),
ADD CONSTRAINT `fk_coms_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools`(`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Fill school_id on Coder_Of_The_Month table
UPDATE
    `Coder_Of_The_Month` `com`
INNER JOIN
    `Users` `u`
ON
    `u`.`user_id` = `com`.`user_id`
INNER JOIN
    `Identities` `i`
ON
    `i`.`identity_id` = `u`.`main_identity_id`
SET
    `com`.`school_id` = `i`.`school_id`;