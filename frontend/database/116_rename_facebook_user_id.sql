-- Rename column facebook_user_id to be more generic
ALTER TABLE `Users` CHANGE COLUMN `facebook_user_id` `provider_user_id` varchar(20) COMMENT 'Provider ID for this user.';