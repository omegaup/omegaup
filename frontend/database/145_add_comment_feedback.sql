-- Alter Contests table

ALTER TABLE
  `Contests`
MODIFY COLUMN
  `feedback` enum('no','yes','partial') NOT NULL DEFAULT 'yes'
    COMMENT 'Indica el detalle de la información que se mostrará en los detalles para determinado envío. "yes" muestra todos los detalles de los veredictos. "partial" muestra los detalles sin el score para cada veredicto. "no" oculta toda la información de veredictos';
