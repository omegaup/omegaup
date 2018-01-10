-- Problemsets
ALTER TABLE `Problemsets`
  ADD COLUMN `needs_basic_information` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un curso o concurso sólo si ya llenó su información de perfil';
  