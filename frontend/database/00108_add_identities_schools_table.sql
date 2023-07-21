-- Identities Schools table
CREATE TABLE `Identities_Schools` (
    `identity_school_id` int(11) NOT NULL AUTO_INCREMENT,
    `identity_id` int(11) NOT NULL,
    `school_id` int(11) NOT NULL,
    `graduation_date` date DEFAULT NULL,
    `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `end_time` timestamp NULL,
    PRIMARY KEY (`identity_school_id`),
    KEY `identity_id` (`identity_id`),
    KEY `school_id` (`school_id`),
    CONSTRAINT `fk_isi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_iss_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    UNIQUE KEY `identity_school_graduation_date` (`identity_id`, `school_id`, `graduation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Todas las escuelas por las que un usuario ha estudiado desde que se unió a omegaUp';

-- Fill Identities Schools table
INSERT INTO `Identities_Schools` (`identity_id`, `school_id`, `graduation_date`)
SELECT
    `i`.`identity_id`,
    `i`.`school_id`,
    `u`.`graduation_date`
FROM
    `Users` `u`
INNER JOIN
    `Identities` `i`
ON
    `u`.`user_id` = `i`.`user_id`
WHERE
    `i`.`school_id` IS NOT NULL;