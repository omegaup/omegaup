-- Alter Problems table

ALTER TABLE `Problems` ADD COLUMN `show_diff` enum('none', 'examples', 'all') NOT NULL DEFAULT 'none' AFTER `quality_seal`;
