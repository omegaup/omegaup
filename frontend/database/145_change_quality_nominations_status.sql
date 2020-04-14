ALTER TABLE `QualityNominations`
CHANGE COLUMN `status` `status` ENUM('open','approved','denied','warning') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación' ;

ALTER TABLE `QualityNomination_Log`
CHANGE COLUMN `to_status` `to_status` ENUM('open','approved','denied','warning') NOT NULL DEFAULT 'open',
CHANGE COLUMN `from_status` `from_status` ENUM('open','approved','denied','warning') NOT NULL DEFAULT 'open';
