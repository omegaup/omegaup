-- Problemsets
ALTER TABLE `Problemsets`
  ADD COLUMN `needs_user_info` tinyint(1) NOT NULL DEFAULT '0'
    COMMENT 'Se solicita información de los participantes para contactarlos posteriormente.';

-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `needs_user_info` tinyint(1) NOT NULL DEFAULT '0'
    COMMENT 'Se solicita información de los participantes para contactarlos posteriormente.';

-- Problemset_Users: consent for contests
ALTER TABLE `Problemset_Users`
  ADD COLUMN `accept_disclose_info` tinyint(1) DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un concurso si está de acuerdo en divulgar su información.';

-- Groups_Users: consent for courses
ALTER TABLE `Groups_Users`
  ADD COLUMN `accept_disclose_info` tinyint(1) DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.';