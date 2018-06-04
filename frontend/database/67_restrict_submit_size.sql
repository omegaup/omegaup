-- Alter Problems table

ALTER TABLE `Problems` ADD COLUMN `input_limit` int(11) NOT NULL DEFAULT '10240' AFTER `output_limit`;
