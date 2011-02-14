SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `omegaup` DEFAULT CHARACTER SET utf8 ;
USE `omegaup` ;

-- -----------------------------------------------------
-- Table `omegaup`.`Paises`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Paises` (
  `paisID` INT(11) NOT NULL ,
  `nombre` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`paisID`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Catálogos para la normalización';


-- -----------------------------------------------------
-- Table `omegaup`.`Estados`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Estados` (
  `estadoID` INT(11) NOT NULL ,
  `paisID` INT(11) NOT NULL ,
  `nombre` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`estadoID`) ,
  INDEX `paisID` (`paisID` ASC) ,
  CONSTRAINT `paisID`
    FOREIGN KEY (`paisID` )
    REFERENCES `omegaup`.`Paises` (`paisID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Catálogos para la normalización';


-- -----------------------------------------------------
-- Table `omegaup`.`Escuelas`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Escuelas` (
  `escuelaID` INT(11) NOT NULL ,
  `estadoID` INT(11) NOT NULL ,
  `nombre` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`escuelaID`) ,
  INDEX `estadoID` (`estadoID` ASC) ,
  CONSTRAINT `estadoID`
    FOREIGN KEY (`estadoID` )
    REFERENCES `omegaup`.`Estados` (`estadoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Catálogos para la normalización';


-- -----------------------------------------------------
-- Table `omegaup`.`Usuarios`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Usuarios` (
  `userID` INT(11) NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(50) NOT NULL ,
  `password` CHAR(32) NULL DEFAULT NULL ,
  `email` VARCHAR(256) NULL DEFAULT NULL ,
  `nombre` VARCHAR(256) NULL DEFAULT NULL ,
  `resueltos` INT(11) NOT NULL DEFAULT '0' ,
  `intentados` INT(11) NOT NULL DEFAULT '0' ,
  `paisID` INT(11) NULL DEFAULT NULL ,
  `estadoID` INT(11) NULL DEFAULT NULL ,
  `escuelaID` INT(11) NULL DEFAULT NULL ,
  `gradoestudios` VARCHAR(64) NULL DEFAULT NULL ,
  `graduacion` DATE NULL DEFAULT NULL ,
  `fechaNacimiento` DATE NULL DEFAULT NULL ,
  `ultimoAcceso` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP ,
  PRIMARY KEY (`userID`) ,
  INDEX `paisID` (`paisID` ASC) ,
  INDEX `estadoID` (`estadoID` ASC) ,
  INDEX `escuelaID` (`escuelaID` ASC) ,
  CONSTRAINT `paisID`
    FOREIGN KEY (`paisID` )
    REFERENCES `omegaup`.`Paises` (`paisID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `estadoID`
    FOREIGN KEY (`estadoID` )
    REFERENCES `omegaup`.`Estados` (`estadoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `escuelaID`
    FOREIGN KEY (`escuelaID` )
    REFERENCES `omegaup`.`Escuelas` (`escuelaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Usuarios registrados.';


-- -----------------------------------------------------
-- Table `omegaup`.`AuthTokens`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`AuthTokens` (
  `userID` INT(11) NOT NULL ,
  `authToken` VARCHAR(256) NOT NULL ,
  PRIMARY KEY (`authToken`) ,
  INDEX `userID` (`userID` ASC) ,
  CONSTRAINT `userID`
    FOREIGN KEY (`userID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Tokens de autorización para los logins.';


-- -----------------------------------------------------
-- Table `omegaup`.`Avisos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Avisos` (
  `avisoID` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del aviso' ,
  `userID` INT(11) NOT NULL COMMENT 'UserID del autor de este aviso' ,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este aviso' ,
  `aviso` TEXT NOT NULL COMMENT 'Mensaje de texto del aviso' ,
  PRIMARY KEY (`avisoID`) ,
  INDEX `userID` (`userID` ASC) ,
  CONSTRAINT `userID`
    FOREIGN KEY (`userID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Sistema de mensajería dentro del sitio.';


-- -----------------------------------------------------
-- Table `omegaup`.`CambioPass`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`CambioPass` (
  `userID` INT(11) NOT NULL COMMENT 'Identificador de a que usuario pertenece este token' ,
  `token` CHAR(64) NOT NULL COMMENT 'El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link' ,
  `ip` CHAR(15) NOT NULL COMMENT 'El ip desde donde se genero este reseteo de password' ,
  `expiracion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'La fecha en que vence este token' ,
  PRIMARY KEY (`userID`) ,
  INDEX `userID` (`userID` ASC) ,
  CONSTRAINT `userID`
    FOREIGN KEY (`userID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `omegaup`.`Mensajes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Mensajes` (
  `mensajeID` INT(11) NOT NULL AUTO_INCREMENT ,
  `leido` TINYINT(1) NOT NULL DEFAULT '0' ,
  `deID` INT(11) NOT NULL ,
  `paraID` INT(11) NOT NULL ,
  `mensaje` TINYTEXT NOT NULL ,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP ,
  PRIMARY KEY (`mensajeID`) ,
  INDEX `deID` (`deID` ASC, `paraID` ASC) ,
  CONSTRAINT `deID`
    FOREIGN KEY (`deID` , `paraID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` , `userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Sistema de mensajería dentro del sitio.';


-- -----------------------------------------------------
-- Table `omegaup`.`Problemas`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Problemas` (
  `problemaID` INT(11) NOT NULL AUTO_INCREMENT ,
  `publico` TINYINT(1) NOT NULL DEFAULT '1' ,
  `autorID` INT(11) NOT NULL ,
  `titulo` VARCHAR(256) NOT NULL ,
  `alias` VARCHAR(10) NULL DEFAULT NULL ,
  `validador` ENUM('remoto','literal','token','token-caseless','token-numeric') NOT NULL DEFAULT 'token-numeric' ,
  `servidor` ENUM('uva','livearchive','pku','tju','spoj') NULL DEFAULT NULL ,
  `id_remoto` VARCHAR(10) NULL DEFAULT NULL ,
  `tiempoLimite` INT(11) NULL DEFAULT '3000' ,
  `memoriaLimite` INT(11) NULL DEFAULT '64' ,
  `vistas` INT(11) NOT NULL DEFAULT '0' ,
  `envios` INT(11) NOT NULL DEFAULT '0' ,
  `aceptados` INT(11) NOT NULL DEFAULT '0' ,
  `dificultad` DOUBLE NOT NULL DEFAULT '0' ,
  `fechaDeCreacion` DATETIME NOT NULL ,
  `fuente` VARCHAR(256) NULL DEFAULT NULL ,
  PRIMARY KEY (`problemaID`) ,
  INDEX `autorID` (`autorID` ASC) ,
  CONSTRAINT `autorID`
    FOREIGN KEY (`autorID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Se crea un registro por cada prob externo.';



-- -----------------------------------------------------
-- Table `omegaup`.`Concursos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Concursos` (
  `concursoID` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador unico para cada concurso' ,
  `titulo` VARCHAR(256) NOT NULL COMMENT 'El titulo que aparecera en cada concurso' ,
  `descripcion` TINYTEXT NOT NULL COMMENT 'Una breve descripcion de cada concurso.' ,
  `inicio` TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora de inicio de este concurso' ,
  `final` TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora de finalizacion de este concurso' ,
  `creadorID` INT(11) NOT NULL COMMENT 'el userID del usuario que creo este concurso' ,
  `repeticionID` INT(11) NOT NULL COMMENT 'Este campo es para las repeticiones de algún concurso' ,
  `abierto` TINYINT(1) NOT NULL DEFAULT TRUE COMMENT 'False implica concurso cerrado, ver la tabla ConcursantesConcurso' ,
  `token` VARCHAR(20) COMMENT 'Almacenará el token necesario para acceder al concurso',
  `scoreboard` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible',
  `puntaje_parcial` TINYINT(1) NOT NULL DEFAULT TRUE COMMENT 'Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos',
  `submissions_gap` TINYINT(1) NOT NULL DEFAULT TRUE COMMENT 'Tiempo mínimo que debe de esperar un usuario despues de realizar un envío para hacer otro',
  `feedback` ENUM('no','si','parcial') NOT NULL ,
  `scoreboard` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible',
  `penalizacion` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Entero indicando el número de minutos con que se penaliza por recibir un no-accepted',
  `inicio_tiempo` ENUM('concurso', 'problema') NOT NULL COMMENT 'Indica el momento cuando se inicia a contar el timpo: cuando inicia el concurso o cuando se abre el problema',
  PRIMARY KEY (`concursoID`) ,
  INDEX `creadorID` (`creadorID` ASC) ,
  INDEX `repeticionID` (`concursoID` ASC) ,
  CONSTRAINT `creadorID`
    FOREIGN KEY (`creadorID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `repeticionID`
    FOREIGN KEY (`concursoID` )
    REFERENCES `omegaup`.`Concursos` (`concursoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Concursos que se llevan a cabo en el juez.';


-- -----------------------------------------------------
-- Table `omegaup`.`ProblemasConcurso`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`ProblemasConcurso` (
  `concursoID` INT(11) NOT NULL ,
  `problemaID` INT(11) NOT NULL ,
  `puntos` DOUBLE NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`concursoID`, `problemaID`) ,
  INDEX `concursoID` (`concursoID` ASC) ,
  INDEX `problemaID` (`problemaID` ASC) ,
  CONSTRAINT `concursoID`
    FOREIGN KEY (`concursoID` )
    REFERENCES `omegaup`.`Concursos` (`concursoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `problemaID`
    FOREIGN KEY (`problemaID` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Relacion entre Concursos y los problemas que tiene este';


-- -----------------------------------------------------
-- Table `omegaup`.`Ejecuciones`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Ejecuciones` (
  `ejecucionID` INT(11) NOT NULL AUTO_INCREMENT ,
  `usuarioID` INT(11) NOT NULL ,
  `problemaID` INT(11) NOT NULL ,
  `concursoID` INT(11) NULL DEFAULT NULL ,
  `guid` CHAR(32) NOT NULL ,
  `lenguaje` ENUM('c','cpp','java','py','rb','pl','cs') NOT NULL ,
  `estado` ENUM('nuevo','espera','compilando','ejecutando','listo') NOT NULL DEFAULT 'nuevo' ,
  `veredicto` ENUM('AC','WA','PE','RTE','MLE','TLE','RFE','JE') NOT NULL ,
  `tiempo` INT(11) NOT NULL DEFAULT '0' ,
  `memoria` INT(11) NOT NULL DEFAULT '0' ,
  `puntuacion` DOUBLE NOT NULL DEFAULT '0' ,
  `ip` CHAR(15) NOT NULL ,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`ejecucionID`) ,
  INDEX `usuarioID` (`usuarioID` ASC) ,
  INDEX `problemaID` (`problemaID` ASC) ,
  INDEX `concursoID` (`concursoID` ASC) ,
  CONSTRAINT `usuarioID`
    FOREIGN KEY (`usuarioID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `problemaID`
    FOREIGN KEY (`problemaID` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `concursoID`
    FOREIGN KEY (`concursoID` )
    REFERENCES `omegaup`.`Concursos` (`concursoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Estado de todas las ejecuciones.';


-- -----------------------------------------------------
-- Table `omegaup`.`CoderDelMes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`CoderDelMes` (
  `coderID` INT(11) NOT NULL AUTO_INCREMENT ,
  `descripcion` TINYTEXT NULL DEFAULT NULL ,
  `fecha` DATE NOT NULL DEFAULT '2000/01/01' COMMENT 'Fecha no es UNIQUE por si hay más de 1 coder de mes.' ,
  `entrevistaURL` VARCHAR(256) NULL DEFAULT NULL COMMENT 'Para linekar a un post del blog con entrevistas.' ,
  PRIMARY KEY (`coderID`) ,
  INDEX `coderDelMesID` (`coderID` ASC) ,
  CONSTRAINT `coderDelMesID`
    FOREIGN KEY (`coderID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Guardar histórico de coders del mes de forma sencilla.';


-- -----------------------------------------------------
-- Table `omegaup`.`ConcursanteConcursos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`ConcursanteConcursos` (
  `userID` INT(11) NOT NULL ,
  `concursoID` INT(11) NOT NULL ,
  PRIMARY KEY (`userID`, `concursoID`) ,
  INDEX `userID` (`userID` ASC) ,
  INDEX `concursoID` (`concursoID` ASC) ,
  CONSTRAINT `userID`
    FOREIGN KEY (`userID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `concursoID`
    FOREIGN KEY (`concursoID` )
    REFERENCES `omegaup`.`Concursos` (`concursoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Concursantes que pueden participar en concurso cerrado.';


-- -----------------------------------------------------
-- Table `omegaup`.`Favoritos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Favoritos` (
  `userID` INT(11) NOT NULL ,
  `problemaID` INT(11) NOT NULL ,
  PRIMARY KEY (`userID`, `problemaID`) ,
  INDEX `userID` (`userID` ASC) ,
  INDEX `problemaID` (`problemaID` ASC) ,
  CONSTRAINT `userID`
    FOREIGN KEY (`userID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `problemaID`
    FOREIGN KEY (`problemaID` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Problemas favoritos de los usuarios';


-- -----------------------------------------------------
-- Table `omegaup`.`Tags`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Tags` (
  `tagID` INT(11) NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(45) NOT NULL ,
  `descripcion` TINYTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`tagID`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Guarda los tags para los problemas';


-- -----------------------------------------------------
-- Table `omegaup`.`TagsProblemas`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`TagsProblemas` (
  `problemaID` INT(11) NOT NULL ,
  `tagID` INT(11) NOT NULL ,
  PRIMARY KEY (`problemaID`, `tagID`) ,
  INDEX `tagID` (`tagID` ASC) ,
  INDEX `problemaID` (`problemaID` ASC) ,
  CONSTRAINT `tagID`
    FOREIGN KEY (`tagID` )
    REFERENCES `omegaup`.`Tags` (`tagID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `problemaID`
    FOREIGN KEY (`problemaID` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Guarda la relacion entre Problemas y sus Tags';


-- -----------------------------------------------------
-- Table `omegaup`.`Idiomas`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Idiomas` (
  `idiomaID` INT(11) NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(45) NOT NULL ,
  `paisID` INT(11) NULL DEFAULT NULL COMMENT 'Se guarda la relación con el país para defaultear más rápido.' ,
  PRIMARY KEY (`idiomaID`) ,
  UNIQUE INDEX `nombre_UNIQUE` (`nombre` ASC) ,
  INDEX `paisID` (`paisID` ASC) ,
  CONSTRAINT `paisID`
    FOREIGN KEY (`paisID` )
    REFERENCES `omegaup`.`Paises` (`paisID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Lista de idiomas que potencialmente se soportarían.';


-- -----------------------------------------------------
-- Table `omegaup`.`ProblemasIdiomas`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`ProblemasIdiomas` (
  `problemaID` INT(11) NOT NULL ,
  `idiomaID` INT(11) NOT NULL ,
  `traductorID` INT(11) NOT NULL ,
  PRIMARY KEY (`problemaID`, `idiomaID`) ,
  INDEX `problemaID` (`problemaID` ASC) ,
  INDEX `idiomaID` (`idiomaID` ASC) ,
  INDEX `traductorID` (`traductorID` ASC) ,
  CONSTRAINT `problemaID`
    FOREIGN KEY (`problemaID` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `idiomaID`
    FOREIGN KEY (`idiomaID` )
    REFERENCES `omegaup`.`Idiomas` (`idiomaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `traductorID`
    FOREIGN KEY (`traductorID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Las traducciones viven en el filesystem y no en la bdd.';


-- -----------------------------------------------------
-- Table `omegaup`.`Clarificaciones`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Clarificaciones` (
  `clarificacionID` INT(11) NOT NULL AUTO_INCREMENT ,
  `autorID` INT(11) NOT NULL COMMENT 'Autor de la clarificación.' ,
  `mensaje` TEXT NOT NULL ,
  `respuesta` TEXT NOT NULL ,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `problemaID` INT(11) NOT NULL COMMENT 'Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema.' ,
  `concursoID` INT(11) NULL DEFAULT NULL COMMENT 'Puede ser nulo si la clarificacion no se da en un concurso.' ,
  `publicable` TINYINT(1) NOT NULL DEFAULT FALSE COMMENT 'Sólo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. ' ,
  PRIMARY KEY (`clarificacionID`) ,
  INDEX `problemaID` (`problemaID` ASC) ,
  INDEX `concursoID` (`concursoID` ASC) ,
  INDEX `autorID` (`autorID` ASC) ,
  CONSTRAINT `problemaID`
    FOREIGN KEY (`problemaID` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `concursoID`
    FOREIGN KEY (`concursoID` )
    REFERENCES `omegaup`.`Concursos` (`concursoID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `autorID`
    FOREIGN KEY (`autorID` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Se guardan las clarificaciones.';


-- -----------------------------------------------------
-- Table `omegaup`.`Badges`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Badges` (
  `idBadges` INT(11) NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(45) NOT NULL DEFAULT 'MyBadge' ,
  `imgurl` VARCHAR(45) NOT NULL ,
  `descripcion` VARCHAR(500) NOT NULL COMMENT 'La descripcion habla de como se obtuvo el badge, de forma corta.' ,
  `tip` VARCHAR(100) NULL COMMENT 'Tip de como desbloquear el badge.' ,
  PRIMARY KEY (`idBadges`) )
ENGINE = MyISAM
COMMENT = 'Esta tabla guarda la informacion de cada uno de los badges.';


-- -----------------------------------------------------
-- Table `omegaup`.`BadgesProblemas`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`BadgesProblemas` (
  `idBadge` INT(11) NOT NULL ,
  `idProblema` INT(11) NOT NULL ,
  PRIMARY KEY (`idBadge`, `idProblema`) ,
  INDEX `idBadge` (`idBadge` ASC) ,
  INDEX `idProblema` (`idProblema` ASC) ,
  CONSTRAINT `idBadge`
    FOREIGN KEY (`idBadge` )
    REFERENCES `omegaup`.`Badges` (`idBadges` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `idProblema`
    FOREIGN KEY (`idProblema` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
COMMENT = 'Relación entre 1 badge y los problemas que lo desbloqueaan.';


-- -----------------------------------------------------
-- Table `omegaup`.`BadgesConcursantes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`BadgesConcursantes` (
  `idBadge` INT(11) NOT NULL ,
  `idConcursante` INT(11) NOT NULL ,
  `timestamp` TIMESTAMP NOT NULL ,
  `idUltimoProblema` INT(11) NOT NULL COMMENT 'Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun.' ,
  PRIMARY KEY (`idBadge`, `idConcursante`) ,
  INDEX `idBadge` (`idBadge` ASC) ,
  INDEX `idConcursante` (`idConcursante` ASC) ,
  INDEX `idUltimoProblema` (`idUltimoProblema` ASC) ,
  CONSTRAINT `idBadge`
    FOREIGN KEY (`idBadge` )
    REFERENCES `omegaup`.`Badges` (`idBadges` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `idConcursante`
    FOREIGN KEY (`idConcursante` )
    REFERENCES `omegaup`.`Usuarios` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `idUltimoProblema`
    FOREIGN KEY (`idUltimoProblema` )
    REFERENCES `omegaup`.`Problemas` (`problemaID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
COMMENT = 'Guarda los badges que han sido desbloqueados.';



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
