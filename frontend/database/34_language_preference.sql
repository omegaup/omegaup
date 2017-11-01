-- Users
ALTER TABLE `Users`
  ADD COLUMN `preferred_language` enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') DEFAULT NULL COMMENT 'El lenguaje de programación de preferencia de este usuario';