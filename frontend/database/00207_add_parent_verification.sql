-- Users table
ALTER TABLE `Users`
ADD COLUMN parent_verified boolean 
ADD COLUMN Creation_timestamp timestamp DEFAULT NULL
ADD COLUMN parent_identity_id int NOT NULL;