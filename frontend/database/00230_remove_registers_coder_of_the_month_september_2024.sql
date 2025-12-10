-- Add a new key to the `Coder_Of_The_Month` table with the columns `category` and `time`
ALTER TABLE
    `Coder_Of_The_Month`
ADD INDEX
    `time_category` (`category`, `time`);

-- Delete any existing entry for the coder of the month for September 2024
DELETE FROM
  `Coder_Of_The_Month`
WHERE
  `time` = '2024-09-01'
  AND `category` = 'all';

DELETE FROM
  `Coder_Of_The_Month`
WHERE
  `time` = '2024-09-01'
  AND `category` = 'female';
