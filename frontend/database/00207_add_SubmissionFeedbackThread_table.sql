-- Submission_Feedback_Thread
CREATE TABLE `Submission_Feedback_Thread` (
  `submission_feedback_thread_id` int NOT NULL AUTO_INCREMENT,
  `submission_feedback_id` int NOT NULL COMMENT 'Identificador del comentario asociado',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se envió el feedback',
  `feedback` text NOT NULL,
  PRIMARY KEY (`submission_feedback_thread_id`),
  CONSTRAINT `fk_sfs_submission_feedback_id` FOREIGN KEY (`submission_feedback_id`) REFERENCES `Submission_Feedback` (`submission_feedback_id`)
) COMMENT="Darle seguimiento a un comentario";

ALTER TABLE `Submission_Feedback`
    ADD COLUMN `start_index` int NOT NULL DEFAULT 0 COMMENT 'Inicio de la subcadena seleccionada para agregarle el comentario',
    ADD COLUMN `end_index` int NOT NULL DEFAULT 0 COMMENT 'Fin de la subcadena seleccionada para agregarle el comentario';