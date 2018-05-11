-- Audit_Log table

CREATE TABLE `Audit_Log` (
  `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  `git_object_id` varchar(50) NOT NULL COMMENT 'Id de la versión del documento en el que se almacena la nueva política',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora en la que el usuario acepta las nuevas políticas',
  PRIMARY KEY (`identity_id`,`git_object_id`),
  CONSTRAINT `fk_ali_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log para auditar las identidades que han aceptado las políticas ed omegaUp.';
