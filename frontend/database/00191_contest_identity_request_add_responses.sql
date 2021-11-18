ALTER TABLE `Course_Identity_Request`
    ADD COLUMN `accept_teacher` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.',
    ADD COLUMN `share_user_information` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.';
