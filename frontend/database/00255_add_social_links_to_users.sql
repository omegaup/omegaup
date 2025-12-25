-- Add social profile links to Users table
ALTER TABLE `Users`
ADD COLUMN `github_url` VARCHAR(255) NULL,
ADD COLUMN `linkedin_url` VARCHAR(255) NULL,
ADD COLUMN `website_url` VARCHAR(255) NULL;
