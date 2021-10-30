-- Users
ALTER TABLE `Users`
  ADD COLUMN `is_private` tinyint(1) NOT NULL DEFAULT 0
    COMMENT 'Determina si el usuario eligió no compartir su información de manera pública'
    AFTER `in_mailing_list`;
