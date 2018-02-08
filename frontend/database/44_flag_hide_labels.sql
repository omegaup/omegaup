-- Users table

ALTER TABLE
	Users
ADD COLUMN
	`hide_problem_tags` tinyint(1) DEFAULT NULL AFTER `recruitment_optin`;

