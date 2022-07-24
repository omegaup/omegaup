-- Submission_Feedback_Thread
CREATE TABLE `Submission_Feedback_Thread` (
  `submission_feedback_thread_id` int NOT NULL AUTO_INCREMENT,
  `submission_feedback_id` int NOT NULL COMMENT 'Identificador del comentario asociado',
  `identity_id` int NOT NULL COMMENT 'Identidad de quien envió el feedback',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se envió el feedback',
  `contents` text NOT NULL,
  PRIMARY KEY (`submission_feedback_thread_id`),
  CONSTRAINT `fk_sfs_submission_feedback_id` FOREIGN KEY (`submission_feedback_id`) REFERENCES `Submission_Feedback` (`submission_feedback_id`),
  CONSTRAINT `fk_sfti_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="Darle seguimiento a un comentario";

ALTER TABLE `Submission_Feedback`
    ADD COLUMN `range_bytes_start` int NOT NULL DEFAULT 0 COMMENT 'Inicio de la subcadena seleccionada (en bytes) para agregarle el comentario',
    ADD COLUMN `range_bytes_end` int NOT NULL DEFAULT 0 COMMENT 'Fin de la subcadena seleccionada (en bytes) para agregarle el comentario';
