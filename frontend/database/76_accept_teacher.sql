-- Groups_Users: consent for teacher
ALTER TABLE `Groups_Identities`
  ADD COLUMN `accept_teacher` enum('yes','no') DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.';
