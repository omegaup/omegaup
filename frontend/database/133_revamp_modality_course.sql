-- Adding admission_mode field in Courses Table

ALTER TABLE `Courses`
    ADD COLUMN `admission_mode` enum('private','registration','public') NOT NULL DEFAULT 'private'
    COMMENT 'Modalidad en la que se registra un curso.' AFTER `finish_time`;

-- Updating admission_mode field in existing courses

UPDATE
    `Courses`
SET
    `admission_mode` = 'public'
WHERE
    `public` = '1';

-- Dropping old fields

ALTER TABLE `Courses`
    DROP COLUMN `public`;
