-- Add school_id to Submissions
ALTER TABLE `Submissions`
ADD COLUMN `school_id` int(11) DEFAULT NULL,
ADD KEY `school_id` (`school_id`),
ADD CONSTRAINT `fk_sc_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools`(`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Fill school_id on Submissions table
UPDATE
    `Submissions` `s`
SET
    `s`.`school_id` = (
        SELECT
            `i`.`school_id`
        FROM
            `Identities` `i`
        WHERE
            `i`.`identity_id` = `s`.`identity_id`
    );