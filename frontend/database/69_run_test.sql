ALTER TABLE `Runs` ADD COLUMN `type` ENUM('normal', 'test', 'disqualified') DEFAULT 'normal';
UPDATE `Runs` SET `type`='test' WHERE test=1;
ALTER TABLE `Runs` DROP COLUMN test;
