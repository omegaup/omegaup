CREATE TABLE IF NOT EXISTS 'Plagiarisms' (
    `plagiarism_id` int(11) NOT NULL AUTO_INCREMENT,
    `contest_id` int(11) DEFAULT NULL,
    `guid_1` char(32) NOT NULL COMMENT 'The submission id of first plagiarised code',
    `guid_2` char(32) NOT NULL COMMENT 'The submission id of second plagiarised code',
    `contents` TEXT NOT NULL COMMENT 'Stores the similarity scores of both the submissions and line number range of those scores',
    PRIMARY KEY (`plag_id`),
    FOREIGN KEY (`contest_id`),
    FOREIGN KEY (`guid_1`), 
    FOREIGN KEY (`guid_2`),
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Table of plagiarised codes.';

ALTER TABLE `Plagiarisms`
    ADD CONSTRAINT `fk_ppp_contest_id` FOREIGN KEY (`contest_id`) REFRENCES `Plagiarisms` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_ppp_guid_1` FOREIGN KEY (`guid_1`) REFRENCES `Plagiarisms` (`guid_1`) ON DELETE NO ACTION ON UPDATE NO ACTION, 
    ADD CONSTRAINT `fk_ppp_guid_2` FOREIGN KEY (`guid_2`) REFRENCES `Plagiarisms` (`guid_2`) ON DELETE NO ACTION ON UPDATE NO ACTION, 