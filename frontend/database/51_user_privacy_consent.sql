-- Problemsets
ALTER TABLE `Problemsets`
  ADD COLUMN `requests_user_information` enum('no', 'optional', 'required') NOT NULL DEFAULT 'no'
    COMMENT 'Se solicita información de los participantes para contactarlos posteriormente.';

-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `requests_user_information` enum('no', 'optional', 'required') NOT NULL DEFAULT 'no'
    COMMENT 'Se solicita información de los participantes para contactarlos posteriormente.';

-- Problemset_Users: consent for contests
ALTER TABLE `Problemset_Users`
  ADD COLUMN `share_user_information` tinyint(1) DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un concurso si está de acuerdo en divulgar su información.';

-- Groups_Users: consent for courses
ALTER TABLE `Groups_Users`
  ADD COLUMN `share_user_information` tinyint(1) DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.';