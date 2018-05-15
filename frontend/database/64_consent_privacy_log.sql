-- PrivacyStatements table

CREATE TABLE `PrivacyStatements` (
  `privacystatement_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del documento de privacidad',
  `git_object_id` varchar(50) NOT NULL COMMENT 'Id de la versión del documento en el que se almacena la nueva política',
  `type` ENUM('privacy_policy') NOT NULL DEFAULT 'privacy_policy' COMMENT 'Tipo de documento de privacidad',
  PRIMARY KEY (`privacystatement_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabla encargada de almacenar cada una de las versiones en git de los documentos de privacidad.';

-- PrivacyStatement_Consent_Log table

CREATE TABLE `PrivacyStatement_Consent_Log` (
  `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  `privacystatement_id` int(11) NOT NULL COMMENT 'Id del documento de privacidad',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora en la que el usuario acepta las nuevas políticas',
  PRIMARY KEY (`identity_id`,`privacystatement_id`),
  UNIQUE KEY `identity_privacy` (`identity_id`, `privacystatement_id`),
  CONSTRAINT `fk_pci_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pcp_privacystatement_id` FOREIGN KEY (`privacystatement_id`) REFERENCES `PrivacyStatements` (`privacystatement_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Log para auditar las identidades que han aceptado los documentos de privacidad de omegaUp.';
