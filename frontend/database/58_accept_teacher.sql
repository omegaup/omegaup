-- Problemset_Identities: consent for teacher
ALTER TABLE `Problemset_Identities`
  ADD COLUMN `accept_teacher` enum('yes', 'no') DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un concurso si acepta al organizador como su maestro.';

-- Groups_Users: consent for teacher
ALTER TABLE `Groups_Identities`
  ADD COLUMN `accept_teacher` enum('yes', 'no') DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.';
