CREATE TABLE IF NOT EXISTS `Plagiarisms` (
    `plagiarisms_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador único para cada conjunto de plagio',
    `contest_id` int(11) NOT NULL,
    `guid_1` char(32) NOT NULL COMMENT 'La identificación de envío del primer código plagiado',
    `guid_2` char(32) NOT NULL COMMENT 'La identificación de envío del segundo código plagiado',
    `contents` TEXT NOT NULL COMMENT 'Almacena los puntajes de similitud de los envíos y el rango de números de línea de esos puntajes',
    PRIMARY KEY (`plagiarisms_id`),
    KEY (`contest_id`),
    KEY (`guid_1`),
    KEY (`guid_2`),
    CONSTRAINT `fk_pci_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_pg_guid_1` FOREIGN KEY (`guid_1`) REFERENCES `Runs` (`guid`) ON DELETE NO ACTION ON UPDATE NO ACTION, 
    CONSTRAINT `fk_pg_guid_2` FOREIGN KEY (`guid_2`) REFERENCES `Runs` (`guid`) ON DELETE NO ACTION ON UPDATE NO ACTION
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='La lista de Códigos Plagiados para un concurso';

