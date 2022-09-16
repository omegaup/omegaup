ALTER TABLE `Plagiarisms`
    MODIFY COLUMN `score_1` int NOT NULL COMMENT 'porcentaje de plagio encontrado usando copydetect en el envío 1',
    MODIFY COLUMN `score_2` int NOT NULL COMMENT 'porcentaje de plagio encontrado usando copydetect en el envío 2';