-- Problemset_Identities
ALTER TABLE `Problemset_Identities`
    ADD COLUMN `is_invited` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad ingresará al concurso por invitación o lo encontró en el listado de concursos públicos';
