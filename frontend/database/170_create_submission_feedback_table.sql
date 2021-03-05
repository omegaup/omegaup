CREATE TABLE `Submission_Feedback` (
    `submission_feedback_id` int NOT NULL AUTO_INCREMENT,
    `identity_id` int(11) NOT NULL COMMENT 'Identidad de quien envió el feedback',
    `submission_id` int(11) NOT NULL COMMENT 'Identificador del envío asociado',
    `feedback` text NOT NULL,
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se envió el feedback',
    PRIMARY KEY (`submission_feedback_id`),
    UNIQUE KEY `submission_id` (`submission_id`),
    CONSTRAINT `fk_sfi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_sfs_submission_id` FOREIGN KEY (`submission_id`) REFERENCES `Submissions` (`submission_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Almacena el feedback dejado por los profesores para los envíos de los estudiantes.';