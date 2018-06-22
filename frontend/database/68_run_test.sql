ALTER TABLE `Runs` ADD COLUMN `type` ENUM('normal', 'test', 'disqualify') DEFAULT 'normal';
UPDATE `Runs` SET `type`='test' WHERE test=1;
ALTER TABLE `Runs` DROP COLUMN test;
