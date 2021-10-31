ALTER TABLE
    `Coder_Of_The_Month`
ADD INDEX
    `rank_time_category` (`category`, `rank`, `time`),
DROP INDEX
    `rank_time` ;