-- Problems_Languages
ALTER TABLE `Problems_Languages`
  DROP FOREIGN KEY `fk_pl_translator_id`,
  DROP INDEX `translator_id`,
  DROP COLUMN `translator_id`;
