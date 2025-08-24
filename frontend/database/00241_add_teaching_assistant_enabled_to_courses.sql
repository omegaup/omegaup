-- Add teaching_assistant_enabled field to Courses table

ALTER TABLE `Courses`
ADD COLUMN `teaching_assistant_enabled` TINYINT(1) NOT NULL DEFAULT 0
COMMENT 'Indica si el Asistente de enseñanza de IA está habilitado para este curso';
