-- Add a unique constraint to the table in order to avoid duplicates
ALTER TABLE `Coder_Of_The_Month`
  ADD UNIQUE KEY `unique_user_time_category` (`user_id`, `time`, `category`);

-- Insert the coder of the month for August 2024
INSERT INTO `Coder_Of_The_Month`
    (
      `user_id`,
      `time`,
      `ranking`,
      `selected_by`,
      `category`,
      `score`,
      `problems_solved`
    )
	VALUES
		(
      COALESCE((SELECT `user_id` FROM `Identities` WHERE `username` = 'Mixer6151'), 1),
      '2024-08-01',
      '1',
      COALESCE((SELECT `user_id` FROM `Identities` WHERE `username` = 'heduenas'), 1),
      'all',
      420,
      13
    )
	ON DUPLICATE KEY UPDATE
    `selected_by` = COALESCE((SELECT `user_id` FROM `Identities` WHERE `username` = 'heduenas'), 1);
