ALTER TABLE
	Users
ADD COLUMN
	`in_mailing_list` BOOLEAN NOT NULL DEFAULT FALSE AFTER `recruitment_optin`;

UPDATE Users SET `in_mailing_list` = TRUE;

