-- Users table
ALTER TABLE `Users`
ADD COLUMN `parent_verified` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del padre cuando este verifica la cuenta de su hijo',
ADD COLUMN `creation_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se creó la cuenta de usuario',
ADD COLUMN `random_token_generated` VARCHAR(25) DEFAULT NULL COMMENT 'Token que se generará para los usuarios menores de 13 años al momento de registrar su cuenta, el cuál será enviado por correo electrónico al padre',
ADD COLUMN `parent_email_verification_deadline` timestamp NULL DEFAULT NULL 'Almacena la hora y fecha límite que tienen los padres para verificar la cuenta de su hijo menor a 13 años';