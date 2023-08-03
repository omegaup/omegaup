-- Submission_Feedback table

ALTER TABLE `Submission_Feedback`
    MODIFY COLUMN `range_bytes_start` int DEFAULT NULL COMMENT 'Inicio de la subcadena seleccionada (en bytes) para agregarle el comentario',
    MODIFY COLUMN `range_bytes_end` int DEFAULT NULL COMMENT 'Fin de la subcadena seleccionada (en bytes) para agregarle el comentario';
