ALTER TABLE `Groups_`
  ADD COLUMN `archived` tinyint(1) NOT NULL DEFAULT '0'
    COMMENT 'Indica si el grupo ha sido archivado.';