ALTER TABLE
  `Runs`
MODIFY COLUMN
  `status` enum('new','waiting','compiling','running','ready','uploading') NOT NULL DEFAULT 'new';
