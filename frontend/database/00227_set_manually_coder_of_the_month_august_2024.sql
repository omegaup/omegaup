-- Delete any existing entry for the coder of the month for August 2024
DELETE FROM `Coder_Of_The_Month`
WHERE `time` = '2024-08-01'
  AND `ranking` = '1'
  AND `category` = 'all';

  -- Insert the new coder of the month for August 2024
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
      COALESCE((SELECT `identity_id` FROM `Identities` WHERE `username` = 'heduenas'), 1),
      'all',
      1952,
      184
    );
