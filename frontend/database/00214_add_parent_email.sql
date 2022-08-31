-- Users table
ALTER TABLE `Users`
ADD COLUMN `parent_email_id` varchar(100) DEFAULT NULL;
