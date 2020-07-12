-- Users
ALTER TABLE `Users`
	ADD COLUMN `git_token` char(40) DEFAULT NULL COMMENT 'Token de acceso para git' AFTER `password`;
