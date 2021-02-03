-- Assignments
ALTER TABLE `Assignments`
    MODIFY COLUMN `assignment_type` enum('homework','lesson','test') NOT NULL DEFAULT 'homework'
        COMMENT 'Almacena el tipo de contenido que se va a dar de alta';
