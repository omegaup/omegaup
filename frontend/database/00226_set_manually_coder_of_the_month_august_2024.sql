-- Add a unique constraint to the table in order to avoid duplicates
ALTER TABLE `Coder_Of_The_Month`
  ADD UNIQUE KEY `unique_user_time_category` (`user_id`, `time`, `category`);

-- Replace the coder of the month for August 2024 with the correct one
REPLACE INTO `Coder_Of_The_Month`
    (
      `coder_of_the_month_id`,
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
      (
        SELECT `coder_of_the_month_id` FROM (
          SELECT `coder_of_the_month_id` FROM `Coder_Of_The_Month` WHERE `user_id` = (
            SELECT `user_id` FROM `Identities` WHERE `username` = 'Mixer6151' AND `time` = '2024-08-01' AND `category` = 'all'
          )
        ) AS temp_table
      ),
      COALESCE((SELECT `user_id` FROM `Identities` WHERE `username` = 'Mixer6151'), 1),
      '2024-08-01',
      '1',
      COALESCE((SELECT `identity_id` FROM `Identities` WHERE `username` = 'heduenas'), 1),
      'all',
      1952,
      184
    );
