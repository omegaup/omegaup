-- Users table
ALTER TABLE `Users`
ADD COLUMN `deletion_token` VARCHAR( 50 ) NULL DEFAULT NULL AFTER `verification_id`;