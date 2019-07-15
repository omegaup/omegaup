-- Users
ALTER TABLE `Users`
    ADD COLUMN `was_notified_for_first_submission_with_no_main_identity` tinyint(1) DEFAULT NULL COMMENT 'Bandera para indicar si un usuario ya fue notificado que está intentando realizar un envío desde una identidad que no es la principal' AFTER `preferred_language`;
