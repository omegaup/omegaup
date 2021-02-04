ALTER TABLE
    `Courses`
ADD COLUMN
    `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el curso ha sido archivado por el administrador.';
