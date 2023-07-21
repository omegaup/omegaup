ALTER TABLE
    `Contests`
ADD COLUMN
    `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el concurso ha sido archivado por el administrador.';
