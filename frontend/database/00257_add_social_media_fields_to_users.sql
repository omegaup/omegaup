ALTER TABLE `Users`
  ADD COLUMN `x_url` varchar(255) DEFAULT NULL COMMENT 'X (formerly Twitter) profile URL',
  ADD COLUMN `linkedin_url` varchar(255) DEFAULT NULL COMMENT 'LinkedIn profile URL',
  ADD COLUMN `github_url` varchar(255) DEFAULT NULL COMMENT 'GitHub profile URL';
