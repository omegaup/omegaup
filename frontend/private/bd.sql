SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA `omegaup`;

CREATE SCHEMA IF NOT EXISTS `omegaup` DEFAULT CHARACTER SET utf8 ;
USE `omegaup` ;

-- -----------------------------------------------------
-- Table `omegaup`.`Countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Countries` (
  `country_id` CHAR(3) NOT NULL ,
  `name` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`country_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Catálogos para la normalización';


-- -----------------------------------------------------
-- Table `omegaup`.`States`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`States` (
  `state_id` INT(11) NOT NULL ,
  `country_id` CHAR(3) NOT NULL ,
  `name` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`state_id`) ,
  INDEX `country_id` (`country_id` ASC) ,
  CONSTRAINT `country_id`
    FOREIGN KEY (`country_id` )
    REFERENCES `omegaup`.`Countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Catálogos para la normalización';


-- -----------------------------------------------------
-- Table `omegaup`.`Schools`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Schools` (
  `school_id` INT(11) NOT NULL ,
  `state_id` INT(11) NOT NULL ,
  `name` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`school_id`) ,
  INDEX `state_id` (`state_id` ASC) ,
  CONSTRAINT `state_id`
    FOREIGN KEY (`state_id` )
    REFERENCES `omegaup`.`States` (`state_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Catálogos para la normalización';


-- -----------------------------------------------------
-- Table `omegaup`.`Users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(50) NOT NULL ,
  `password` CHAR(32) NULL DEFAULT NULL ,
  `email` VARCHAR(256) NULL DEFAULT NULL ,
  `name` VARCHAR(256) NULL DEFAULT NULL ,
  `solved` INT(11) NOT NULL DEFAULT '0' ,
  `submissions` INT(11) NOT NULL DEFAULT '0' ,
  `country_id` CHAR(3) NULL DEFAULT NULL ,
  `state_id` INT(11) NULL DEFAULT NULL ,
  `school_id` INT(11) NULL DEFAULT NULL ,
  `scholar_degree` VARCHAR(64) NULL DEFAULT NULL ,
  `graduation_date` DATE NULL DEFAULT NULL ,
  `birth_date` DATE NULL DEFAULT NULL ,
  `last_access` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP ,
  PRIMARY KEY (`user_id`) ,
  INDEX `country_id` (`country_id` ASC) ,
  INDEX `state_id` (`state_id` ASC) ,
  INDEX `school_id` (`school_id` ASC) ,
  CONSTRAINT `fk_country_id`
    FOREIGN KEY (`country_id` )
    REFERENCES `omegaup`.`Countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_state_id`
    FOREIGN KEY (`state_id` )
    REFERENCES `omegaup`.`States` (`state_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `school_id`
    FOREIGN KEY (`school_id` )
    REFERENCES `omegaup`.`Schools` (`school_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Usuarios registrados.';


-- -----------------------------------------------------
-- Table `omegaup`.`Auth_Tokens`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Auth_Tokens` (
  `user_id` INT(11) NOT NULL ,
  `token` VARCHAR(128) NOT NULL ,
  PRIMARY KEY (`token`) ,
  INDEX `user_id` (`user_id` ASC) ,
  CONSTRAINT `user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Tokens de autorización para los logins.';


-- -----------------------------------------------------
-- Table `omegaup`.`Announcement`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Announcement` (
  `announcement_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del aviso' ,
  `user_id` INT(11) NOT NULL COMMENT 'UserID del autor de este aviso' ,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este aviso' ,
  `description` TEXT NOT NULL COMMENT 'Mensaje de texto del aviso' ,
  PRIMARY KEY (`announcement_id`) ,
  INDEX `user_id` (`user_id` ASC) ,
  CONSTRAINT `fk_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Sistema de mensajería dentro del sitio.';


-- -----------------------------------------------------
-- Table `omegaup`.`Password_Change`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Password_Change` (
  `user_id` INT(11) NOT NULL COMMENT 'Identificador de a que usuario pertenece este token' ,
  `token` CHAR(64) NOT NULL COMMENT 'El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link' ,
  `ip` CHAR(15) NOT NULL COMMENT 'El ip desde donde se genero este reseteo de password' ,
  `expiration_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'La fecha en que vence este token' ,
  PRIMARY KEY (`user_id`) ,
  INDEX `user_id` (`user_id` ASC) ,
  CONSTRAINT `fk_pc_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `omegaup`.`Messages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Messages` (
  `message_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `read` TINYINT(1) NOT NULL DEFAULT '0' ,
  `sender_id` INT(11) NOT NULL ,
  `recipient_id` INT(11) NOT NULL ,
  `message` TINYTEXT NOT NULL ,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP ,
  PRIMARY KEY (`message_id`) ,
  INDEX `sender_id` (`sender_id` ASC, `recipient_id` ASC) ,
  CONSTRAINT `fk_m_sender_id`
    FOREIGN KEY (`sender_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION ,
  CONSTRAINT `fk_m_recipient_id`
    FOREIGN KEY (`recipient_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION )
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Sistema de mensajería dentro del sitio.';


-- -----------------------------------------------------
-- Table `omegaup`.`Problems`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Problems` (
  `problem_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `public` TINYINT(1) NOT NULL DEFAULT '1' ,
  `author_id` INT(11) NOT NULL ,
  `title` VARCHAR(256) NOT NULL ,
  `alias` VARCHAR(10) NULL DEFAULT NULL ,
  `validator` ENUM('remote','literal','token','token-caseless','token-numeric') NOT NULL DEFAULT 'token-numeric' ,
  `server` ENUM('uva','livearchive','pku','tju','spoj') NULL DEFAULT NULL ,
  `remote_id` VARCHAR(10) NULL DEFAULT NULL ,
  `time_limit` INT(11) NULL DEFAULT '3000' ,
  `memory_limit` INT(11) NULL DEFAULT '64' ,
  `visits` INT(11) NOT NULL DEFAULT '0' ,
  `submissions` INT(11) NOT NULL DEFAULT '0' ,
  `accepted` INT(11) NOT NULL DEFAULT '0' ,
  `difficulty` DOUBLE NOT NULL DEFAULT '0' ,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` VARCHAR(256) NULL DEFAULT NULL ,
  `order` ENUM('normal', 'inverse') NOT NULL DEFAULT 'normal', -- Orden normal: más puntos es mejor. Orden inverso: menos penalty es mejor.
  PRIMARY KEY (`problem_id`) ,
  INDEX `author_id` (`author_id` ASC) ,
  CONSTRAINT `author_id`
    FOREIGN KEY (`author_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Se crea un registro por cada prob externo.';



-- -----------------------------------------------------
-- Table `omegaup`.`Contests`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Contests` (
  `contest_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador unico para cada concurso' ,
  `title` VARCHAR(256) NOT NULL COMMENT 'El titulo que aparecera en cada concurso' ,
  `description` TINYTEXT NOT NULL COMMENT 'Una breve descripcion de cada concurso.' ,
  `start_time` TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora de inicio de este concurso' ,
  `finish_time` TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora de finalizacion de este concurso' ,
  `window_length` INT(11) COMMENT 'Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso' ,
  `director_id` INT(11) NOT NULL COMMENT 'el userID del usuario que creo este concurso' ,
  `rerun_id` INT(11) NOT NULL COMMENT 'Este campo es para las repeticiones de algún concurso' ,
  `public` TINYINT(1) NOT NULL DEFAULT TRUE COMMENT 'False implica concurso cerrado, ver la tabla ConcursantesConcurso' ,
  `token` VARCHAR(20) NOT NULL COMMENT 'Almacenará el token necesario para acceder al concurso',
  `scoreboard` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible',
  `partial_score` TINYINT(1) NOT NULL DEFAULT TRUE COMMENT 'Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos',
  `submissions_gap` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro',
  `feedback` ENUM('no','yes','partial') NOT NULL ,
  `penality` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Entero indicando el número de minutos con que se penaliza por recibir un no-accepted',
  `time_start` ENUM('contest', 'problem') NOT NULL COMMENT 'Indica el momento cuando se inicia a contar el timpo: cuando inicia el concurso o cuando se abre el problema',
  PRIMARY KEY (`contest_id`) ,
  INDEX `director_id` (`director_id` ASC) ,
  INDEX `rerun_id` (`contest_id` ASC) ,
  CONSTRAINT `director_id`
    FOREIGN KEY (`director_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `rerun_id`
    FOREIGN KEY (`contest_id` )
    REFERENCES `omegaup`.`Contests` (`contest_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Concursos que se llevan a cabo en el juez.';


-- -----------------------------------------------------
-- Table `omegaup`.`Contest_Problems`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Contest_Problems` (
  `contest_id` INT(11) NOT NULL ,
  `problem_id` INT(11) NOT NULL ,
  `points` DOUBLE NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`contest_id`, `problem_id`) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  CONSTRAINT `contest_id`
    FOREIGN KEY (`contest_id` )
    REFERENCES `omegaup`.`Contests` (`contest_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Relacion entre Concursos y los problemas que tiene este';


-- -----------------------------------------------------
-- Table `omegaup`.`Contest_Problem_Opened`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Contest_Problem_Opened` (
  `contest_id` INT(11) NOT NULL ,
  `problem_id` INT(11) NOT NULL ,
  `user_id` INT(11) NOT NULL ,
  `open_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contest_id`, `problem_id`, `user_id`) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  INDEX `user_id` (`user_id` ASC) ,
  CONSTRAINT `fk_cpo_contest_id`
    FOREIGN KEY (`contest_id` )
    REFERENCES `omegaup`.`Contests` (`contest_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cpo_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cpo_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Relacion entre Concursos y los problemas que tiene este';


-- -----------------------------------------------------
-- Table `omegaup`.`Runs`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Runs` (
  `run_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `problem_id` INT(11) NOT NULL ,
  `contest_id` INT(11) NULL DEFAULT NULL ,
  `guid` CHAR(32) NOT NULL ,
  `language` ENUM('c','cpp','java','py','rb','pl','cs','p') NOT NULL ,
  `status` ENUM('new','waiting','compiling','running','ready') NOT NULL DEFAULT 'new' ,
  `veredict` ENUM('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE') NOT NULL ,
  `runtime` INT(11) NOT NULL DEFAULT 0, -- tiempo de ejecución
  `memory` INT(11) NOT NULL DEFAULT 0,  -- total de memoria consumida
  `score` DOUBLE NOT NULL DEFAULT '0' , -- proporción (entre 0 y 1) de los casos resueltos correctamente
  `contest_score` DOUBLE NOT NULL DEFAULT '0' , -- puntuación ajustada según las reglas del concurso
  `ip` CHAR(15) NOT NULL ,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , -- tiempo en el que se realizó el envío
  PRIMARY KEY (`run_id`) ,
  INDEX `user_id` (`user_id` ASC) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  CONSTRAINT `fk_r_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_r_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_r_contest_id`
    FOREIGN KEY (`contest_id` )
    REFERENCES `omegaup`.`Contests` (`contest_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8
COMMENT = 'Estado de todas las ejecuciones.';


-- -----------------------------------------------------
-- Table `omegaup`.`Coder_of_the_Month`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Coder_of_the_Month` (
  `coder_of_the_month_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `description` TINYTEXT NULL DEFAULT NULL ,
  `time` DATE NOT NULL DEFAULT '2000/01/01' COMMENT 'Fecha no es UNIQUE por si hay más de 1 coder de mes.' ,
  `interview_url` VARCHAR(256) NULL DEFAULT NULL COMMENT 'Para linekar a un post del blog con entrevistas.' ,
  PRIMARY KEY (`coder_of_the_month_id`) ,
  INDEX `coder_of_the_month_id` (`coder_of_the_month_id` ASC) ,
  CONSTRAINT `coder_of_the_month_id`
    FOREIGN KEY (`coder_of_the_month_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Guardar histórico de coders del mes de forma sencilla.';


-- -----------------------------------------------------
-- Table `omegaup`.`Contests_Users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Contests_Users` (
  `user_id` INT(11) NOT NULL ,
  `contest_id` INT(11) NOT NULL ,
  `access_time` TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora a la que entró el usuario al concurso' ,
  `score` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Índica el puntaje que obtuvo el usuario en el concurso',
  `time` INT(11) NOT NULL DEFAULT TRUE COMMENT 'Índica el tiempo que acumulo en usuario en el concurso',  
  PRIMARY KEY (`user_id`, `contest_id`) ,
  INDEX `user_id` (`user_id` ASC) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  CONSTRAINT `fk_cu_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cu_contest_id`
    FOREIGN KEY (`contest_id` )
    REFERENCES `omegaup`.`Contests` (`contest_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Concursantes que pueden participar en concurso cerrado.';


-- -----------------------------------------------------
-- Table `omegaup`.`Favorites`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Favorites` (
  `user_id` INT(11) NOT NULL ,
  `problem_id` INT(11) NOT NULL ,
  PRIMARY KEY (`user_id`, `problem_id`) ,
  INDEX `user_id` (`user_id` ASC) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  CONSTRAINT `fk_f_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_f_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Problemas favoritos de los usuarios';


-- -----------------------------------------------------
-- Table `omegaup`.`Tags`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Tags` (
  `tag_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TINYTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`tag_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Guarda los tags para los problemas';


-- -----------------------------------------------------
-- Table `omegaup`.`Problems_Tags`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Problems_Tags` (
  `problem_id` INT(11) NOT NULL ,
  `tag_id` INT(11) NOT NULL ,
  PRIMARY KEY (`problem_id`, `tag_id`) ,
  INDEX `tag_id` (`tag_id` ASC) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  CONSTRAINT `fk_t_tag_id`
    FOREIGN KEY (`tag_id` )
    REFERENCES `omegaup`.`Tags` (`tag_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_t_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Guarda la relacion entre Problemas y sus Tags';


-- -----------------------------------------------------
-- Table `omegaup`.`Languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Languages` (
  `language_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `country_id` CHAR(3) NULL DEFAULT NULL COMMENT 'Se guarda la relación con el país para defaultear más rápido.' ,
  PRIMARY KEY (`language_id`) ,
  UNIQUE INDEX `nombre_UNIQUE` (`name` ASC) ,
  INDEX `country_id` (`country_id` ASC) ,
  CONSTRAINT `fk_l_country_id`
    FOREIGN KEY (`country_id` )
    REFERENCES `omegaup`.`Countries` (`country_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Lista de idiomas que potencialmente se soportarían.';


-- -----------------------------------------------------
-- Table `omegaup`.`Problems_Languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Problems_Languages` (
  `problem_id` INT(11) NOT NULL ,
  `language_id` INT(11) NOT NULL ,
  `translator_id` INT(11) NOT NULL ,
  PRIMARY KEY (`problem_id`, `language_id`) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  INDEX `language_id` (`language_id` ASC) ,
  INDEX `translator_id` (`translator_id` ASC) ,
  CONSTRAINT `fk_pl_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pl_language_id`
    FOREIGN KEY (`language_id` )
    REFERENCES `omegaup`.`Languages` (`language_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pl_translator_id`
    FOREIGN KEY (`translator_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Las traducciones viven en el filesystem y no en la bdd.';


-- -----------------------------------------------------
-- Table `omegaup`.`Clarifications`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Clarifications` (
  `clarification_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `author_id` INT(11) NOT NULL COMMENT 'Autor de la clarificación.' ,
  `message` TEXT NOT NULL ,
  `answer` TEXT NOT NULL ,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `problem_id` INT(11) NOT NULL COMMENT 'Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema.' ,
  `contest_id` INT(11) NULL DEFAULT NULL COMMENT 'Puede ser nulo si la clarificacion no se da en un concurso.' ,
  `public` TINYINT(1) NOT NULL DEFAULT FALSE COMMENT 'Sólo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. ' ,
  PRIMARY KEY (`clarification_id`) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  INDEX `author_id` (`author_id` ASC) ,
  CONSTRAINT `fk_c_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_c_contest_id`
    FOREIGN KEY (`contest_id` )
    REFERENCES `omegaup`.`Contests` (`contest_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_c_author_id`
    FOREIGN KEY (`author_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Se guardan las clarificaciones.';


-- -----------------------------------------------------
-- Table `omegaup`.`Badges`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Badges` (
  `badge_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL DEFAULT 'MyBadge' ,
  `image_url` VARCHAR(45) NOT NULL ,
  `description` VARCHAR(500) NOT NULL COMMENT 'La descripcion habla de como se obtuvo el badge, de forma corta.' ,
  `hint` VARCHAR(100) NULL COMMENT 'Tip de como desbloquear el badge.' ,
  PRIMARY KEY (`badge_id`) )
ENGINE = InnoDB
COMMENT = 'Esta tabla guarda la informacion de cada uno de los badges.';


-- -----------------------------------------------------
-- Table `omegaup`.`Problems_Badges`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Problems_Badges` (
  `badge_id` INT(11) NOT NULL ,
  `problem_id` INT(11) NOT NULL ,
  PRIMARY KEY (`badge_id`, `problem_id`) ,
  INDEX `badge_id` (`badge_id` ASC) ,
  INDEX `problem_id` (`problem_id` ASC) ,
  CONSTRAINT `fk_pb_badge_id`
    FOREIGN KEY (`badge_id` )
    REFERENCES `omegaup`.`Badges` (`badge_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pb_problem_id`
    FOREIGN KEY (`problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Relación entre 1 badge y los problemas que lo desbloqueaan.';


-- Originalmente era `users_badges`, pero creí que se referia a los badges desblequeados por los usuarios (no necesariamente relacionados con concursos)
-- -----------------------------------------------------
-- Table `omegaup`.`Users_Badges`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Users_Badges` (
  `badge_id` INT(11) NOT NULL ,
  `user_id` INT(11) NOT NULL ,
  `time` TIMESTAMP NOT NULL ,
  `last_problem_id` INT(11) NOT NULL COMMENT 'Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun.' ,
  PRIMARY KEY (`badge_id`, `user_id`) ,
  INDEX `badge_id` (`badge_id` ASC) ,
  INDEX `user_id` (`user_id` ASC) ,
  INDEX `last_problem_id` (`last_problem_id` ASC) ,
  CONSTRAINT `fk_ub_badge_id`
    FOREIGN KEY (`badge_id` )
    REFERENCES `omegaup`.`Badges` (`badge_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ub_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ub_last_problem_id`
    FOREIGN KEY (`last_problem_id` )
    REFERENCES `omegaup`.`Problems` (`problem_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Guarda los badges que han sido desbloqueados.';


-- Permisos


-- -----------------------------------------------------
-- Table `omegaup`.`Permissions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Permissions` (
  `permission_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(50) NOT NULL COMMENT 'El nombre corto del permiso.' ,
  `description` VARCHAR(100) NOT NULL COMMENT 'La descripción humana del permiso.' ,
  PRIMARY KEY (`permission_id`) )
ENGINE = InnoDB
COMMENT = 'Establece los permisos que se pueden dar a los roles.';


-- -----------------------------------------------------
-- Table `omegaup`.`Roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Roles` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(50) NOT NULL COMMENT 'El nombre corto del rol.' ,
  `description` VARCHAR(100) NOT NULL COMMENT 'La descripción humana del rol.' ,
  PRIMARY KEY (`role_id`) )
ENGINE = InnoDB
COMMENT = 'Establece los roles que se pueden dar a los usuarios.';


-- -----------------------------------------------------
-- Table `omegaup`.`Roles_Permissions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`Roles_Permissions` (
  `role_id` INT(11) NOT NULL ,
  `permission_id` INT(11) NOT NULL ,
  PRIMARY KEY (`role_id`, `permission_id`) ,
  INDEX `role_id` (`role_id` ASC) ,
  INDEX `permission_id` (`permission_id` ASC) ,
  CONSTRAINT `fk_rp_role_id`
    FOREIGN KEY (`role_id` )
    REFERENCES `omegaup`.`Roles` (`role_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rp_permission_id`
    FOREIGN KEY (`permission_id` )
    REFERENCES `omegaup`.`Permissions` (`permission_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION )
ENGINE = InnoDB
COMMENT = 'Establece los roles que se pueden dar a los usuarios.';


-- -----------------------------------------------------
-- Table `omegaup`.`User_Roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `omegaup`.`User_Roles` (
  `user_id` INT(11) NOT NULL ,
  `role_id` INT(11) NOT NULL ,
  PRIMARY KEY (`user_id`, `role_id`) ,
  INDEX `user_id` (`user_id` ASC) ,
  INDEX `role_id` (`role_id` ASC) ,
  CONSTRAINT `fk_ur_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `omegaup`.`Users` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ur_role_id`
    FOREIGN KEY (`role_id` )
    REFERENCES `omegaup`.`Roles` (`role_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION )
ENGINE = InnoDB
COMMENT = 'Establece los roles que se pueden dar a los usuarios.';



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
