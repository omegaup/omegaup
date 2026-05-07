ALTER TABLE
    `Team_Groups`
ADD COLUMN
    `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el grupo de equipos ha sido archivado.';