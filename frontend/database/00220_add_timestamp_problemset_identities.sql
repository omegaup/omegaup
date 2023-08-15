-- Problemset_Identities table
ALTER TABLE `Problemset_Identities`
ADD COLUMN `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se creó la invitación del usuario a un concurso';
