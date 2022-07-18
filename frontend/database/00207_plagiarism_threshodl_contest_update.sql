-- Adds a new feild "plagiarism_threshold" in Contest table.

ALTER TABLE `Contests`
    ADD COLUMN `plagiarism_threshold` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'El porcentaje mínimo permitido de similitud entre un par de envíos. Cuando plagio Seleccionado, será 90.';