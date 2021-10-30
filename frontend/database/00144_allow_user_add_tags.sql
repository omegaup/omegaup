-- Alter Problems table

ALTER TABLE
  `Problems`
ADD COLUMN
  `allow_user_add_tags` tinyint(1) NOT NULL DEFAULT '1'
    COMMENT 'Bandera que sirve para indicar si un problema puede permitir que los usuarios agreguen tags.'
    AFTER `show_diff`;
