-- Users
ALTER TABLE `Users`
	MODIFY COLUMN `password` varchar(128) DEFAULT NULL COMMENT 'Contraseña del usuario, usando Argon2i o Blowfish',
	MODIFY COLUMN `git_token` varchar(128) DEFAULT NULL COMMENT 'Token de acceso para git, usando Argon2i' AFTER `password`;

-- Identities
ALTER TABLE `Identities`
	MODIFY COLUMN `password` varchar(128) DEFAULT NULL COMMENT 'Contraseña del usuario, usando Argon2i o Blowfish';
