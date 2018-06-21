ALTER TABLE `Runs` ADD COLUMN `type` ENUM('normal', 'test', 'disqualify') DEFAULT 'normal';
UPDATE `Runs` SET type='test' WHERE test=1;
ALTER TABLE `Runs` DROP COLUMN test;
ALTER TABLE `Runs` CHANGE COLUMN `type` `test` ENUM('normal', 'test', 'disqualify');
ALTER TABLE `Runs` ALTER `test` SET DEFAULT "normal"
