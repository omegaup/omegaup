-- QualityNominations
ALTER TABLE `Problems` MODIFY COLUMN `difficulty` double DEFAULT NULL;

ALTER TABLE `Problems` ADD COLUMN `quality` double DEFAULT NULL;

ALTER TABLE `Problems_Tags` ADD COLUMN `autogenerated` tinyint(1) NOT NULL DEFAULT 0;