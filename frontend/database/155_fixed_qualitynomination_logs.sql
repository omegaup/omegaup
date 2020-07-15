-- Need next line to disable safe update
SET SQL_SAFE_UPDATES = 0;

-- Fixing user_id and from_status
UPDATE
    `QualityNomination_Log`
SET
    `from_status` = CASE `from_status`
        WHEN `from_status` = `to_status` THEN 'open'
        ELSE `from_status`
    END,
    `user_id` = (SELECT `user_id` FROM `Identities` WHERE `username` = 'rcc' LIMIT 1)
WHERE
    `time` >= '2020-04-26';

SET SQL_SAFE_UPDATES = 1;
