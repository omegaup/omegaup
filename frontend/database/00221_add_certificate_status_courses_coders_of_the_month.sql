-- Coder_Of_The_Month
ALTER TABLE `Coder_Of_The_Month`
  ADD COLUMN `certificate_status` enum('uninitiated', 'queued', 'generated', 'retryable_error', 'fatal_error') NOT NULL DEFAULT 'uninitiated'
    COMMENT 'Estado de la petición de generar diplomas';

-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `certificates_status` enum('uninitiated', 'queued', 'generated', 'retryable_error', 'fatal_error') NOT NULL DEFAULT 'uninitiated'
    COMMENT 'Estado de la petición de generar diplomas';
