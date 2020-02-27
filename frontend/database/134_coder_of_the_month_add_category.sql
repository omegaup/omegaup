ALTER TABLE
	`Coder_Of_The_Month`
ADD COLUMN
	`category` ENUM('all', 'female') NOT NULL DEFAULT 'all';