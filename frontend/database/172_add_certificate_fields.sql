-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `minimum_progress_for_certificate` int(11) DEFAULT NULL
    COMMENT 'Progreso mínimo que debe cumplir el estudiante para que se le otorgue el diploma del curso. NULL indica que el curso no da diplomas.';

-- Contests
ALTER TABLE `Contests`
  ADD COLUMN `certificate_cutoff` int(11) DEFAULT NULL
    COMMENT 'Número de concursantes a premiar con diplomas que mencionan su lugar en el ranking',
  ADD COLUMN `certificates_status` enum('uninitiated', 'queued', 'generated', 'retryable_error', 'fatal_error')
    COMMENT 'Estado de la petición de generar diplomas';
