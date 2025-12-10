
-- Add recommended column in Courses table
ALTER TABLE `Courses`
  ADD COLUMN `recommended` tinyint NOT NULL DEFAULT '0' COMMENT 'Mostrar el curso en la lista de cursos públicos, los cursos que no tengan la bandera encendida pueden ser cursos públicos pero no se mostrarán en la lista.' AFTER `certificates_status`;

-- Set all the existing public courses as recommended
UPDATE `Courses` SET `recommended` = 1 WHERE `admission_mode` = 'public';
