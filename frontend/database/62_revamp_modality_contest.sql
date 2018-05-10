-- Adding modality field in  Contests Table

ALTER TABLE `Contests`
    ADD COLUMN `modality` enum('private','registration','public') NOT NULL DEFAULT 'private'
    COMMENT 'Modalidad en la que se registra un concurso.' AFTER `rerun_id`;

-- Updating modality field in existing contests

UPDATE
    `Contests`
SET
    `modality` = 'registration'
WHERE
    `contestant_must_register` = '1';

UPDATE
    `Contests`
SET
    `modality` = 'public'
WHERE
    `public` = '1';

-- Dropping old fields

ALTER TABLE `Contests`
    DROP COLUMN `public`,
    DROP COLUMN `contestant_must_register`,
    DROP KEY `idx_contest_public`;

-- Updating comment in Problemset_Identity_Request table

ALTER TABLE `Problemset_Identity_Request` COMMENT = 'Used when modality = registration';
