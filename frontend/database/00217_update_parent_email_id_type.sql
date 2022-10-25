-- Users table
ALTER TABLE `Users`
  MODIFY COLUMN `parent_email_id` int DEFAULT NULL,
  ADD KEY `fk_parent_email_id` (`parent_email_id`),
  ADD CONSTRAINT `fk_parent_email_id` FOREIGN KEY (`parent_email_id`) REFERENCES `Emails` (`email_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
