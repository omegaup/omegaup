-- Adding admission_mode field in  Contests Table

ALTER TABLE `Contests`
    ADD COLUMN `admission_mode` enum('private','registration','public') NOT NULL DEFAULT 'private'
    COMMENT 'Modalidad en la que se registra un concurso.' AFTER `rerun_id`;

-- Updating admission_mode field in existing contests

UPDATE
    `Contests`
SET
    `admission_mode` = 'registration'
WHERE
    `contestant_must_register` = '1';

UPDATE
    `Contests`
SET
    `admission_mode` = 'public'
WHERE
    `public` = '1';

-- Dropping old fields

ALTER TABLE `Contests`
    DROP COLUMN `public`,
    DROP COLUMN `contestant_must_register`,
    DROP KEY `idx_contest_public`;

-- Updating comment in Problemset_Identity_Request table

ALTER TABLE `Problemset_Identity_Request` COMMENT = 'Used when admission_mode = registration';

-- Updating Contest Log fields

ALTER TABLE `Contest_Log`
   CHANGE `from_visibility` `from_admission_mode` varchar(20) NOT NULL,
   CHANGE `to_visibility` `to_admission_mode` varchar(20) NOT NULL;
