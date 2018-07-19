-- Alter Identities table, adding and updating gender column

ALTER TABLE `Identities`
  ADD COLUMN `gender` enum('female','male','other','decline') COMMENT 'Género de la identidad';
  