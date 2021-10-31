-- Add new column

ALTER TABLE
  `Contests`
ADD COLUMN
  `submission_feedback` enum('none','summary','detailed') NOT NULL DEFAULT 'none'
    COMMENT 'Indica la cantidad de información que se mostrará en los detalles de un envío. "detailed" muestra el veredicto de la solución caso por caso. "summary" muestra porcentaje de casos que tuvo bien, así como el veredicto del caso con peor calificación. "none" oculta toda la información de los veredictos.'
    AFTER `feedback`;

UPDATE
  `Contests`
SET
  `submission_feedback` = 'summary'
WHERE
  `feedback` = 'yes';

UPDATE
  `Contests`
SET
  `submission_feedback` = 'detailed'
WHERE
  `feedback` = 'partial';

ALTER TABLE
  `Contests`
DROP COLUMN `feedback`;

ALTER TABLE
  `Contests`
ADD COLUMN
  `feedback` enum('none','summary','detailed') NOT NULL DEFAULT 'none'
    COMMENT 'Indica la cantidad de información que se mostrará en los detalles de un envío. "detailed" muestra el veredicto de la solución caso por caso. "summary" muestra porcentaje de casos que tuvo bien, así como el veredicto del caso con peor calificación. "none" oculta toda la información de los veredictos.'
    AFTER `submission_feedback`;

UPDATE
  `Contests`
SET
  `feedback` = `submission_feedback`;

ALTER TABLE
  `Contests`
DROP COLUMN `submission_feedback`;
