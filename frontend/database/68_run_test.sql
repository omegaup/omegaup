ALTER TABLE `Runs` MODIFY COLUMN `test` ENUM("normal", "test", "disqualify");
ALTER TABLE `Runs` ALTER `test` SET DEFAULT "normal"
