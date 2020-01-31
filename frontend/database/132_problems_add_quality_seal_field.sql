ALTER TABLE
    `Problems`
ADD COLUMN
    `quality_seal` tinyint(1) NOT NULL DEFAULT '0';

UPDATE
    `Problems`
SET
    `quality_seal` = 0;