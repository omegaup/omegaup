-- Problemset_Users: consent for contests
ALTER TABLE `Course_Identity_Request`
  ADD COLUMN `extra_note` mediumtext
    COMMENT 'Indica una descripción con el motivo de aceptar o rechazar un usuario al curso';