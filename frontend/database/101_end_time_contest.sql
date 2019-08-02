-- Problemset_Identities
ALTER TABLE `Problemset_Identities`
    ADD COLUMN `end_time` datetime DEFAULT NULL COMMENT 'Hora en la que finaliza un concurso para el usuario cuando se habilita la opción de inicios diferentes' AFTER `access_time`;

-- Update end_time for existing users that belong to a contest with window_length
UPDATE
    `Problemset_Identities`
INNER JOIN
    `Contests`
ON
    Contests.problemset_id = Problemset_Identities.problemset_id
SET
    `end_time` = LEAST(`finish_time`, DATE_ADD(`access_time`, INTERVAL `window_length` MINUTE))
WHERE
    `window_length` IS NOT NULL
    AND `access_time` IS NOT NULL;
