ALTER TABLE 
    `Team_Groups`
ADD COLUMN 
    `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether the team group has been archived.';