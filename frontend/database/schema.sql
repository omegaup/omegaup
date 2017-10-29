/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ACLs` (
  `acl_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL COMMENT 'El usuario que creó el objeto y que tiene un rol de administrador implícito',
  PRIMARY KEY (`acl_id`),
  KEY `fk_au_owner_id` (`owner_id`),
  CONSTRAINT `fk_au_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista de control de acceso.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Announcement` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del aviso',
  `user_id` int(11) NOT NULL COMMENT 'UserID del autor de este aviso',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este aviso',
  `description` text NOT NULL COMMENT 'Mensaje de texto del aviso',
  PRIMARY KEY (`announcement_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_au_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sistema de mensajería dentro del sitio.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `problemset_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL COMMENT 'La lista de control de acceso compartida con el curso',
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `publish_time_delay` int(11) DEFAULT NULL,
  `assignment_type` enum('homework','test') NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00',
  `max_points` double NOT NULL DEFAULT '0' COMMENT 'La cantidad total de puntos que se pueden obtener.',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Define el orden de aparición de los problemas/tareas',
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `assignment_alias` (`course_id`,`alias`),
  KEY `fk_ap_problemset_id` (`problemset_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_aa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ac_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ap_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Un alumno resuelve assignments durante su curso, por ahora pueden ser examenes o tareas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Auth_Tokens` (
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_atu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tokens de autorización para los logins.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Badges` (
  `badge_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT 'MyBadge',
  `image_url` varchar(45) NOT NULL,
  `description` varchar(500) NOT NULL COMMENT 'La descripcion habla de como se obtuvo el badge, de forma corta.',
  `hint` varchar(100) DEFAULT NULL COMMENT 'Tip de como desbloquear el badge.',
  PRIMARY KEY (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Esta tabla guarda la informacion de cada uno de los badges.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Clarifications` (
  `clarification_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL COMMENT 'Autor de la clarificación.',
  `message` text NOT NULL,
  `answer` text,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `problem_id` int(11) DEFAULT NULL COMMENT 'Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.',
  `problemset_id` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.',
  PRIMARY KEY (`clarification_id`),
  KEY `problem_id` (`problem_id`),
  KEY `author_id` (`author_id`),
  KEY `problemset_id` (`problemset_id`),
  CONSTRAINT `fk_cp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cu_author_id` FOREIGN KEY (`author_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Se guardan las clarificaciones.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Coder_Of_The_Month` (
  `coder_of_the_month_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `description` tinytext,
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'Fecha no es UNIQUE por si hay más de 1 coder de mes.',
  `interview_url` varchar(256) DEFAULT NULL COMMENT 'Para linekar a un post del blog con entrevistas.',
  PRIMARY KEY (`coder_of_the_month_id`),
  KEY `coder_of_the_month_id` (`coder_of_the_month_id`),
  KEY `fk_cotmu_user_id` (`user_id`),
  CONSTRAINT `fk_cotmu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guardar histórico de coders del mes de forma sencilla.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contests` (
  `contest_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador unico para cada concurso',
  `problemset_id` int(11) NOT NULL COMMENT 'La lista de problemas de este concurso',
  `acl_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL COMMENT 'El titulo que aparecera en cada concurso',
  `description` tinytext NOT NULL COMMENT 'Una breve descripcion de cada concurso.',
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este concurso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de finalizacion de este concurso',
  `window_length` int(11) DEFAULT NULL COMMENT 'Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso',
  `rerun_id` int(11) NOT NULL COMMENT 'Este campo es para las repeticiones de algún concurso',
  `public` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'False implica concurso cerrado, ver la tabla ConcursantesConcurso',
  `alias` varchar(32) NOT NULL COMMENT 'Almacenará el token necesario para acceder al concurso',
  `scoreboard` int(11) NOT NULL DEFAULT '1' COMMENT 'Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible',
  `points_decay_factor` double NOT NULL DEFAULT '0' COMMENT 'El factor de decaimiento de los puntos de este concurso. El default es 0 (no decae). TopCoder es 0.7',
  `partial_score` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos',
  `submissions_gap` int(11) NOT NULL DEFAULT '1' COMMENT 'Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro',
  `feedback` enum('no','yes','partial') NOT NULL,
  `penalty` int(11) NOT NULL DEFAULT '1' COMMENT 'Entero indicando el número de minutos con que se penaliza por recibir un no-accepted',
  `penalty_type` enum('contest_start','problem_open','runtime','none') NOT NULL COMMENT 'Indica la política de cálculo de penalty: minutos desde que inició el concurso, minutos desde que se abrió el problema, o tiempo de ejecución (en milisegundos).',
  `penalty_calc_policy` enum('sum','max') NOT NULL COMMENT 'Indica como afecta el penalty al score.',
  `show_scoreboard_after` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Mostrar el scoreboard automáticamente después del concurso',
  `scoreboard_url` varchar(30) DEFAULT NULL,
  `scoreboard_url_admin` varchar(30) DEFAULT NULL,
  `urgent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el concurso es de alta prioridad y requiere mejor QoS.',
  `contestant_must_register` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica que los participantes deben pre-registrarse antes de poder paticipar',
  `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar en un concurso',
  `recommended` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Mostrar el concurso en la lista de recomendados.',
  PRIMARY KEY (`contest_id`),
  UNIQUE KEY `contests_alias` (`alias`),
  KEY `rerun_id` (`contest_id`),
  KEY `idx_contest_public` (`public`),
  KEY `acl_id` (`acl_id`),
  KEY `fk_cop_problemset_id` (`problemset_id`),
  FULLTEXT KEY `title` (`title`,`description`),
  CONSTRAINT `fk_coa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cop_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Concursos que se llevan a cabo en el juez.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Countries` (
  `country_id` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catálogos para la normalización';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `group_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este curso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de finalizacion de este curso',
  `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True implica que cualquier usuario puede entrar al curso',
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_alias` (`alias`),
  KEY `fk_ca_acl_id` (`acl_id`),
  KEY `fk_cg_student_group_id` (`group_id`),
  CONSTRAINT `fk_ca_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cg_student_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Un curso/clase que un maestro da.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Emails` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Esta tabla permite tener varios emails por persona';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Favorites` (
  `user_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`problem_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_f_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_f_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Problemas favoritos de los usuarios';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_Roles` (
  `group_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`role_id`,`acl_id`),
  KEY `group_id` (`group_id`),
  KEY `role_id` (`role_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_gr_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_gr_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_gra_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Establece los roles que se pueden dar a los grupos.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `groups_alias` (`alias`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_g_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Groups_Scoreboards` (
  `group_scoreboard_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`group_scoreboard_id`),
  UNIQUE KEY `groups_scoreboards_alias` (`alias`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `fk_gs_user_id` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Groups_Scoreboards_Contests` (
  `group_scoreboard_id` int(11) NOT NULL,
  `contest_id` int(11) NOT NULL,
  `only_ac` tinyint(1) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_scoreboard_id`,`contest_id`),
  KEY `group_scoreboard_id` (`group_scoreboard_id`),
  KEY `contest_id` (`contest_id`),
  CONSTRAINT `fk_gsc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_gsc_group_scoreboard_id` FOREIGN KEY (`group_scoreboard_id`) REFERENCES `Groups_Scoreboards` (`group_scoreboard_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Groups_Users` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `fk_gu_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_gu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Interviews` (
  `interview_id` int(11) NOT NULL AUTO_INCREMENT,
  `problemset_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL COMMENT 'La lista de control de acceso del problema',
  `alias` varchar(32) NOT NULL COMMENT 'El alias de la entrevista',
  `title` varchar(256) NOT NULL COMMENT 'El titulo de la entrevista.',
  `description` tinytext NOT NULL COMMENT 'Una breve descripcion de la entrevista.',
  `window_length` int(11) NOT NULL COMMENT 'Indica el tiempo que tiene el usuario para envíar soluciones.',
  PRIMARY KEY (`interview_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_ia_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ip_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista de id_concuros que se usan para entrevista';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Languages` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `country_id` char(3) DEFAULT NULL COMMENT 'Se guarda la relación con el país para defaultear más rápido.',
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `nombre_UNIQUE` (`name`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `fk_l_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista de idiomas que potencialmente se soportarían.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` tinytext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`,`recipient_id`),
  KEY `fk_m_recipient_id` (`recipient_id`),
  CONSTRAINT `fk_m_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_m_sender_id` FOREIGN KEY (`sender_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sistema de mensajería dentro del sitio.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'El nombre corto del permiso.',
  `description` varchar(100) NOT NULL COMMENT 'La descripción humana del permiso.',
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Establece los permisos que se pueden dar a los roles.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problem_Viewed` (
  `problem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `view_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`problem_id`,`user_id`),
  KEY `problem_id` (`problem_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_pv_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pv_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabla de vistas de problemas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problems` (
  `problem_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_id` int(11) NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT '1' COMMENT '-1 banned, 0 private, 1 public, 2 recommended',
  `title` varchar(256) NOT NULL,
  `alias` varchar(32) NOT NULL,
  `validator` enum('token','token-caseless','token-numeric','custom','literal') NOT NULL DEFAULT 'token-numeric',
  `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') NOT NULL DEFAULT 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11,lua',
  `server` enum('uva','livearchive','pku','tju','spoj') DEFAULT NULL,
  `remote_id` varchar(10) DEFAULT NULL,
  `time_limit` int(11) NOT NULL DEFAULT '3000',
  `validator_time_limit` int(11) NOT NULL DEFAULT '3000',
  `overall_wall_time_limit` int(11) NOT NULL DEFAULT '60000',
  `extra_wall_time` int(11) NOT NULL DEFAULT '0',
  `memory_limit` int(11) NOT NULL DEFAULT '64',
  `output_limit` int(11) NOT NULL DEFAULT '10240',
  `stack_limit` int(11) NOT NULL DEFAULT '10485760',
  `visits` int(11) NOT NULL DEFAULT '0',
  `submissions` int(11) NOT NULL DEFAULT '0',
  `accepted` int(11) NOT NULL DEFAULT '0',
  `difficulty` double NOT NULL DEFAULT '0',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(256) DEFAULT NULL,
  `order` enum('normal','inverse') NOT NULL DEFAULT 'normal',
  `tolerance` double NOT NULL DEFAULT '0.000000001',
  `slow` tinyint(1) NOT NULL DEFAULT '0',
  `deprecated` tinyint(1) NOT NULL DEFAULT '0',
  `email_clarifications` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`problem_id`),
  UNIQUE KEY `problems_alias` (`alias`),
  KEY `acl_id` (`acl_id`),
  KEY `idx_problems_visibility` (`visibility`),
  CONSTRAINT `fk_pa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Se crea un registro por cada prob externo.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problems_Badges` (
  `badge_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  PRIMARY KEY (`badge_id`,`problem_id`),
  KEY `badge_id` (`badge_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_pb_badge_id` FOREIGN KEY (`badge_id`) REFERENCES `Badges` (`badge_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pb_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relación entre 1 badge y los problemas que lo desbloqueaan.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problems_Languages` (
  `problem_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  PRIMARY KEY (`problem_id`,`language_id`),
  KEY `problem_id` (`problem_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `fk_pl_language_id` FOREIGN KEY (`language_id`) REFERENCES `Languages` (`language_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pl_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Las traducciones viven en el filesystem y no en la bdd.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problems_Tags` (
  `problem_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`problem_id`,`tag_id`),
  KEY `problem_id` (`problem_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `fk_ptp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ptt_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `Tags` (`tag_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tags privados para los problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemset_Access_Log` (
  `problemset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `problemset_id` (`problemset_id`),
  KEY `fk_palu_user_id` (`user_id`),
  CONSTRAINT `fk_palc_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_palu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de acceso a listas de problemas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemset_Problem_Opened` (
  `problemset_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `open_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`problemset_id`,`problem_id`,`user_id`),
  KEY `problem_id` (`problem_id`),
  KEY `user_id` (`user_id`),
  KEY `problemset_id` (`problemset_id`),
  CONSTRAINT `fk_ppo_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ppo_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ppo_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Registro de primer acceso a problemas de un conjunto.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemset_Problems` (
  `problemset_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `points` double NOT NULL DEFAULT '1',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Define el orden de aparición de los problemas en una lista de problemas',
  PRIMARY KEY (`problemset_id`,`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `problem_id` (`problem_id`),
  KEY `idx_problemset_problems_ids` (`problem_id`,`problemset_id`),
  CONSTRAINT `fk_ppp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ppp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Los problemas de cada conjunto';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemset_User_Request` (
  `user_id` int(11) NOT NULL,
  `problemset_id` int(11) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NULL DEFAULT NULL,
  `accepted` tinyint(1) DEFAULT NULL,
  `extra_note` mediumtext,
  PRIMARY KEY (`user_id`,`problemset_id`),
  KEY `fk_purp_problemset_id` (`problemset_id`),
  CONSTRAINT `fk_purp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_puru_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used when contestant_must_register = 1';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemset_User_Request_History` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `problemset_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `accepted` tinyint(4) NOT NULL,
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `user_problemset_hist` (`user_id`,`problemset_id`),
  KEY `fk_purhp_problemset_id` (`problemset_id`),
  CONSTRAINT `fk_purhp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_purhu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemset_Users` (
  `user_id` int(11) NOT NULL,
  `problemset_id` int(11) NOT NULL,
  `access_time` datetime DEFAULT NULL COMMENT 'Hora a la que entró el usuario al concurso',
  `score` int(11) NOT NULL DEFAULT '1' COMMENT 'Indica el puntaje que obtuvo el usuario en el concurso',
  `time` int(11) NOT NULL DEFAULT '1' COMMENT 'Indica el tiempo que acumulo en usuario en el concurso',
  PRIMARY KEY (`user_id`,`problemset_id`),
  KEY `user_id` (`user_id`),
  KEY `problemset_id` (`problemset_id`),
  CONSTRAINT `fk_puc_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_puu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Concursantes que pueden interactuar con una lista de problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Problemsets` (
  `problemset_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador único para cada conjunto de problemas',
  `acl_id` int(11) NOT NULL COMMENT 'La lista de control de acceso compartida con su container',
  `access_mode` enum('private','public','registration') NOT NULL DEFAULT 'public' COMMENT 'La modalidad de acceso a este conjunto de problemas',
  `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas',
  PRIMARY KEY (`problemset_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_psa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Conjunto de problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `QualityNomination_Comments` (
  `qualitynomination_comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `qualitynomination_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'El usuario que emitió el comentario',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este comentario',
  `vote` tinyint(1) NOT NULL COMMENT 'El voto emitido en este comentario. En el rango de [-2, +2]',
  `contents` text NOT NULL COMMENT 'El contenido de el comentario',
  PRIMARY KEY (`qualitynomination_comment_id`),
  KEY `user_id` (`user_id`),
  KEY `qualitynomination_id` (`qualitynomination_id`),
  CONSTRAINT `fk_qnc_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_qnc_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Comentarios para una nominación';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `QualityNomination_Reviewers` (
  `qualitynomination_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'El revisor al que fue asignado esta nominación',
  PRIMARY KEY (`qualitynomination_id`,`user_id`),
  KEY `fk_qnr_user_id` (`user_id`),
  CONSTRAINT `fk_qnr_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_qnr_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La lista de revisores para cada nominación';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `QualityNominations` (
  `qualitynomination_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'El usuario que nominó el problema',
  `problem_id` int(11) NOT NULL COMMENT 'El problema que fue nominado',
  `nomination` enum('suggestion','promotion','demotion','dismissal') NOT NULL DEFAULT 'suggestion' COMMENT 'El tipo de nominación',
  `contents` text NOT NULL COMMENT 'Un blob json con el contenido de la nominación',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de esta nominación',
  `status` enum('open','approved','denied') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación',
  PRIMARY KEY (`qualitynomination_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_qn_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_qn_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La cola de nominación a promoción / democión de problemas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'El nombre corto del rol.',
  `description` varchar(100) NOT NULL COMMENT 'La descripción humana del rol.',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Establece los roles que se pueden dar a los usuarios.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Roles_Permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `fk_rp_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `Permissions` (`permission_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_rp_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Establece los roles que se pueden dar a los usuarios.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Run_Counts` (
  `date` date NOT NULL,
  `total` int(11) NOT NULL DEFAULT '0',
  `ac_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda la cantidad de runs que se han realizado hasta la fecha.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Runs` (
  `run_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `problemset_id` int(11) DEFAULT NULL,
  `guid` char(32) NOT NULL,
  `language` enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') NOT NULL,
  `status` enum('new','waiting','compiling','running','ready') NOT NULL DEFAULT 'new',
  `verdict` enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE') NOT NULL,
  `runtime` int(11) NOT NULL DEFAULT '0',
  `penalty` int(11) NOT NULL DEFAULT '0',
  `memory` int(11) NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `contest_score` double DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submit_delay` int(11) NOT NULL DEFAULT '0',
  `test` tinyint(1) NOT NULL DEFAULT '0',
  `judged_by` char(32) DEFAULT NULL,
  PRIMARY KEY (`run_id`),
  UNIQUE KEY `runs_alias` (`guid`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  CONSTRAINT `fk_r_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_r_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_r_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Estado de todas las ejecuciones.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ACUpdate` AFTER UPDATE ON  `Runs`
FOR EACH ROW BEGIN
	IF (OLD.verdict = 'AC' OR NEW.verdict = 'AC') THEN
		UPDATE  `Problems` SET  `Problems`.`accepted` = (
			SELECT COUNT( DISTINCT user_id )
				FROM  `Runs`
				WHERE  `Runs`.`verdict` =  'AC'
				AND NEW.`problem_id` =  `Runs`.`problem_id`
			)
		WHERE NEW.problem_id =  `Problems`.`problem_id`;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Schools` (
  `school_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`school_id`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`country_id`,`state_id`),
  CONSTRAINT `fk_scc_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ss_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catálogos para la normalización';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `States` (
  `country_id` char(3) NOT NULL,
  `state_id` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`country_id`,`state_id`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `fk_stc_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catálogos para la normalización';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Submission_Log` (
  `problemset_id` int(11) DEFAULT NULL,
  `run_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`run_id`),
  KEY `fk_slu_user_id` (`user_id`),
  KEY `problemset_id` (`problemset_id`),
  CONSTRAINT `fk_slp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_slr_run_id` FOREIGN KEY (`run_id`) REFERENCES `Runs` (`run_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_slu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de envíos';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tags privados para los problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User_Login_Log` (
  `user_id` int(11) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ullu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de inicios de sesión exitosos';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User_Rank` (
  `user_id` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `problems_solved_count` int(11) NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda el ranking de usuarios por problemas resueltos.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User_Roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`,`acl_id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_ur_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ur_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ura_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Establece los roles que se pueden dar a los usuarios.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `facebook_user_id` varchar(20) DEFAULT NULL COMMENT 'Facebook ID for this user.',
  `password` varchar(100) DEFAULT NULL,
  `main_email_id` int(11) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `solved` int(11) NOT NULL DEFAULT '0',
  `submissions` int(11) NOT NULL DEFAULT '0',
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `scholar_degree` varchar(64) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `graduation_date` date DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('female','male','other','decline') DEFAULT NULL,
  `last_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_id` varchar(50) DEFAULT NULL,
  `reset_digest` varchar(45) DEFAULT NULL,
  `reset_sent_at` datetime DEFAULT NULL,
  `recruitment_optin` tinyint(1) DEFAULT NULL COMMENT 'Determina si el usuario puede ser contactado con fines de reclutamiento.',
  `in_mailing_list` tinyint(1) NOT NULL DEFAULT '0',
  `preferred_language` enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') DEFAULT NULL COMMENT 'El lenguaje de programación preferencia de este usuario',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `country_id` (`country_id`),
  KEY `school_id` (`school_id`),
  KEY `fk_main_email_id` (`main_email_id`),
  KEY `state_id` (`country_id`,`state_id`),
  CONSTRAINT `fk_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_main_email_id` FOREIGN KEY (`main_email_id`) REFERENCES `Emails` (`email_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_us_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Usuarios registrados.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users_Badges` (
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_problem_id` int(11) NOT NULL COMMENT 'Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun.',
  PRIMARY KEY (`badge_id`,`user_id`),
  KEY `badge_id` (`badge_id`),
  KEY `user_id` (`user_id`),
  KEY `last_problem_id` (`last_problem_id`),
  CONSTRAINT `fk_ub_badge_id` FOREIGN KEY (`badge_id`) REFERENCES `Badges` (`badge_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ub_last_problem_id` FOREIGN KEY (`last_problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ub_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda los badges que han sido desbloqueados.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users_Experiments` (
  `user_id` int(11) NOT NULL,
  `experiment` varchar(256) NOT NULL,
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ueu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda los experimentos habilitados para un usuario.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Refresh_User_Rank`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
    DECLARE EXIT HANDLER FOR NOT FOUND ROLLBACK;
    DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;

    START TRANSACTION;
    TRUNCATE TABLE `User_Rank`;

    SET @prev_value = NULL;
    SET @rank_count = 0;
    SET @prev_value_ties = NULL;
    SET @prev_ties_count = 0;
    SET @ties_count = 0;

    INSERT INTO
        User_Rank (user_id, rank, problems_solved_count, score, username, name, country_id)
    SELECT
        user_id,
        rank,
        problems_solved_count,
        score,
        username,
        name,
        country_id
    FROM
    (
        SELECT
            problems_solved_count,
            username,
            score,
            name,
            country_id,
            user_id,
            @prev_ties_count := @ties_count as previous_ties_count,
        CASE
            WHEN @prev_value_ties = score THEN @ties_count := @ties_count + 1
            WHEN @prev_value_ties := score THEN @ties_count := 0
        END AS ties_count,
        CASE
            WHEN @prev_value = score THEN @rank_count
            WHEN @prev_value := score THEN @rank_count := @rank_count + 1 + @prev_ties_count
        END AS rank
        FROM
        (
            SELECT
                username,
                name,
                country_id,
                up.user_id,
                COUNT(ps.problem_id) problems_solved_count,
                SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)) score
            FROM
            (
                SELECT DISTINCT
                  r.user_id,
                  r.problem_id
                FROM
                  Runs r
                WHERE
                  r.verdict = 'AC' AND r.test = 0
            ) AS up
            INNER JOIN
                Problems ps ON ps.problem_id = up.problem_id AND ps.visibility > 0
            INNER JOIN
                Users u ON u.user_id = up.user_id
            GROUP BY
                user_id
            ORDER BY
                score DESC
        ) AS UsersProblemsSolved
    ) AS Rank;
    COMMIT;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

