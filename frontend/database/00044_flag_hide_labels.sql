-- Users table

ALTER TABLE
	Users
ADD COLUMN
	`hide_problem_tags` tinyint(1) DEFAULT NULL COMMENT 'Determina si el usuario quiere ocultar las etiquetas de los problemas' AFTER `recruitment_optin`;
