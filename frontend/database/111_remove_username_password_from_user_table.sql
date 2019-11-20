-- Drop columns `username` and `password` from `Users` table

ALTER TABLE `Users`
  DROP KEY `username`,
  DROP COLUMN `username`,
  DROP COLUMN `password`;
