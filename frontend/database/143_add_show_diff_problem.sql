-- Alter Problems table

ALTER TABLE
  `Problems`
ADD COLUMN
  `show_diff` enum('none', 'examples', 'all') NOT NULL DEFAULT 'none'
    COMMENT 'Determina si el problema es educativo y debe mostrar diferencias en casos de ejemplos, en todos o en ninguno.'
    AFTER `quality_seal`;
