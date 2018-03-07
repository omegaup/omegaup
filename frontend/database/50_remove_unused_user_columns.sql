-- Remove unused column in Users table --
ALTER TABLE `Users`
    DROP COLUMN `solved`,
    DROP COLUMN `submissions` ,
    DROP COLUMN `last_access`;
