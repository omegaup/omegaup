-- Problemset_Identities
ALTER TABLE `Problemset_Identities`
    ADD COLUMN `end_time` datetime DEFAULT NULL COMMENT 'Hora en la que finaliza un concurso para el usuario cuando se habilita la opción de inicios diferentes' AFTER `access_time`;
