-- Users table
ALTER TABLE `Users`
ADD COLUMN `parent_verified` boolean 
ADD COLUMN `creation_timestamp` timestamp DEFAULT NULL
ADD COLUMN `random_token_generated` VARCHAR(100) DEFAULT NULL
ADD COLUMN `parent_email_verification_deadline` timestamp DEFAULT NULL;