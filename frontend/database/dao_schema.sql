/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ACLs` (
  `acl_id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL COMMENT 'El usuario que creó el objeto y que tiene un rol de administrador implícito',
  PRIMARY KEY (`acl_id`),
  KEY `fk_au_owner_id` (`owner_id`),
  CONSTRAINT `fk_au_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de control de acceso.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `API_Tokens` (
  `apitoken_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de creación del token',
  `name` varchar(100) NOT NULL COMMENT 'Nombre que el usuario le asigna al token',
  `token` char(40) NOT NULL COMMENT 'Contenido del token',
  `last_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de último uso del token, redondeado a la última hora',
  `use_count` int NOT NULL DEFAULT '0' COMMENT 'Número de usos desde la última hora',
  PRIMARY KEY (`apitoken_id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `user_name` (`user_id`,`name`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_atu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tokens para el API';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Announcement` (
  `announcement_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador del aviso',
  `user_id` int NOT NULL COMMENT 'UserID del autor de este aviso',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este aviso',
  `description` text NOT NULL COMMENT 'Mensaje de texto del aviso',
  PRIMARY KEY (`announcement_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_au_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Sistema de mensajería dentro del sitio.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `problemset_id` int NOT NULL,
  `acl_id` int NOT NULL COMMENT 'La lista de control de acceso compartida con el curso',
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `publish_time_delay` int DEFAULT NULL,
  `assignment_type` enum('homework','lesson','test') NOT NULL DEFAULT 'homework' COMMENT 'Almacena el tipo de contenido que se va a dar de alta',
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00',
  `finish_time` timestamp NULL DEFAULT NULL,
  `max_points` double NOT NULL DEFAULT '0' COMMENT 'La cantidad total de puntos que se pueden obtener.',
  `order` int NOT NULL DEFAULT '1' COMMENT 'Define el orden de aparición de los problemas/tareas',
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `assignment_alias` (`course_id`,`alias`),
  KEY `fk_ap_problemset_id` (`problemset_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_aa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `fk_ac_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`),
  CONSTRAINT `fk_ap_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Un alumno resuelve assignments durante su curso, por ahora pueden ser examenes o tareas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Auth_Tokens` (
  `user_id` int DEFAULT NULL,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `acting_identity_id` int DEFAULT NULL COMMENT 'Identidad del usuario que indica que no está actuando como identidad principal',
  `token` varchar(128) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  KEY `identity_id` (`identity_id`),
  KEY `acting_identity_id` (`identity_id`),
  KEY `fk_ati_acting_identity_id` (`acting_identity_id`),
  CONSTRAINT `fk_ati_acting_identity_id` FOREIGN KEY (`acting_identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_ati_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tokens de autorización para los logins.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Carousel_Items` (
  `carousel_item_id` int NOT NULL AUTO_INCREMENT,
  `title` json NOT NULL,
  `excerpt` json NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `button_title` json DEFAULT NULL,
  `expiration_date` datetime DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`carousel_item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `Carousel_Items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabla para almacenar noticias en la plataforma';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Certificates` (
  `certificate_id` int NOT NULL AUTO_INCREMENT,
  `identity_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del otorgamiento del diploma',
  `certificate_type` enum('course','contest','coder_of_the_month','coder_of_the_month_female') NOT NULL COMMENT 'Tipo de diploma',
  `course_id` int DEFAULT NULL,
  `contest_id` int DEFAULT NULL,
  `coder_of_the_month_id` int DEFAULT NULL COMMENT 'Id del Coder del mes que obtuvo el certificado',
  `verification_code` varchar(10) NOT NULL COMMENT 'Código de verificación del diploma',
  `contest_place` int DEFAULT NULL COMMENT 'Se guarda el lugar en el que quedo un estudiante si es menor o igual a certificate_cutoff',
  PRIMARY KEY (`certificate_id`),
  UNIQUE KEY `verification_code` (`verification_code`),
  UNIQUE KEY `contest_identity_key` (`identity_id`,`contest_id`,`certificate_type`),
  UNIQUE KEY `course_identity_key` (`identity_id`,`course_id`,`certificate_type`),
  KEY `identity_id` (`identity_id`),
  KEY `course_id` (`course_id`),
  KEY `contest_id` (`contest_id`),
  KEY `coder_of_the_month_id` (`coder_of_the_month_id`),
  CONSTRAINT `fk_cc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `fk_cc_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`),
  CONSTRAINT `fk_ccotm_coder_of_the_month_id` FOREIGN KEY (`coder_of_the_month_id`) REFERENCES `Coder_Of_The_Month` (`coder_of_the_month_id`),
  CONSTRAINT `fk_ci_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Diplomas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Clarifications` (
  `clarification_id` int NOT NULL AUTO_INCREMENT,
  `author_id` int NOT NULL COMMENT 'Autor de la clarificación.',
  `receiver_id` int DEFAULT NULL COMMENT 'Usuario que recibirá el mensaje',
  `message` text NOT NULL,
  `answer` text,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `problem_id` int DEFAULT NULL COMMENT 'Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.',
  `problemset_id` int NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.',
  PRIMARY KEY (`clarification_id`),
  KEY `problem_id` (`problem_id`),
  KEY `author_id` (`author_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `fk_ci_author_id` FOREIGN KEY (`author_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_ci_receiver_id` FOREIGN KEY (`receiver_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_cp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_cp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Se guardan las clarificaciones.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Coder_Of_The_Month` (
  `coder_of_the_month_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `description` tinytext,
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'Fecha no es UNIQUE por si hay más de 1 coder de mes.',
  `interview_url` varchar(256) DEFAULT NULL COMMENT 'Para linekar a un post del blog con entrevistas.',
  `ranking` int NOT NULL COMMENT 'El lugar en el que el usuario estuvo durante ese mes',
  `selected_by` int DEFAULT NULL COMMENT 'Id de la identidad que seleccionó al coder.',
  `school_id` int DEFAULT NULL,
  `category` enum('all','female') NOT NULL DEFAULT 'all',
  `score` double NOT NULL DEFAULT '0',
  `problems_solved` int NOT NULL DEFAULT '0',
  `certificate_status` enum('uninitiated','queued','generated','retryable_error','fatal_error') NOT NULL DEFAULT 'uninitiated' COMMENT 'Estado de la petición de generar diplomas',
  PRIMARY KEY (`coder_of_the_month_id`),
  KEY `coder_of_the_month_id` (`coder_of_the_month_id`),
  KEY `fk_cotmu_user_id` (`user_id`),
  KEY `selected_by` (`selected_by`),
  KEY `school_id` (`school_id`),
  KEY `rank_time_category` (`category`,`ranking`,`time`),
  KEY `time_category` (`category`,`time`),
  CONSTRAINT `fk_coms_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`),
  CONSTRAINT `fk_cotmi_identity_id` FOREIGN KEY (`selected_by`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_cotmu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guardar histórico de coders del mes de forma sencilla.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Contest_Log` (
  `public_contest_id` int NOT NULL AUTO_INCREMENT,
  `contest_id` int NOT NULL,
  `user_id` int NOT NULL,
  `from_admission_mode` enum('private','registration','public') NOT NULL,
  `to_admission_mode` enum('private','registration','public') NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`public_contest_id`),
  KEY `contest_id` (`contest_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_cl_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `fk_cl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Esta tabla funcionará para poder ordenar los concursos que se vuelven públicos y no se pierdan entre el resto';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Contests` (
  `contest_id` int NOT NULL AUTO_INCREMENT COMMENT 'El identificador unico para cada concurso',
  `problemset_id` int NOT NULL COMMENT 'La lista de problemas de este concurso',
  `acl_id` int NOT NULL,
  `title` varchar(256) NOT NULL COMMENT 'El titulo que aparecera en cada concurso',
  `description` tinytext NOT NULL COMMENT 'Una breve descripcion de cada concurso.',
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este concurso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de finalizacion de este concurso',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Indica la hora en que se actualizó de privado a público un concurso o viceversa',
  `window_length` int DEFAULT NULL COMMENT 'Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso',
  `rerun_id` int DEFAULT NULL COMMENT 'Este campo es para las repeticiones de algún concurso, Contiene el id del concurso original o null en caso de ser un concurso original.',
  `admission_mode` enum('private','registration','public') NOT NULL DEFAULT 'private' COMMENT 'Modalidad en la que se registra un concurso.',
  `alias` varchar(32) NOT NULL COMMENT 'Almacenará el token necesario para acceder al concurso',
  `scoreboard` int NOT NULL DEFAULT '1' COMMENT 'Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible',
  `points_decay_factor` double NOT NULL DEFAULT '0' COMMENT 'El factor de decaimiento de los puntos de este concurso. El default es 0 (no decae). TopCoder es 0.7',
  `partial_score` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos',
  `submissions_gap` int NOT NULL DEFAULT '60' COMMENT 'Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro',
  `feedback` enum('none','summary','detailed') NOT NULL DEFAULT 'none' COMMENT 'Indica la cantidad de información que se mostrará en los detalles de un envío. "detailed" muestra el veredicto de la solución caso por caso. "summary" muestra porcentaje de casos que tuvo bien, así como el veredicto del caso con peor calificación. "none" oculta toda la información de los veredictos.',
  `penalty` int NOT NULL DEFAULT '1' COMMENT 'Entero indicando el número de minutos con que se penaliza por recibir un no-accepted',
  `penalty_type` enum('contest_start','problem_open','runtime','none') NOT NULL COMMENT 'Indica la política de cálculo de penalty: minutos desde que inició el concurso, minutos desde que se abrió el problema, o tiempo de ejecución (en milisegundos).',
  `penalty_calc_policy` enum('sum','max') NOT NULL COMMENT 'Indica como afecta el penalty al score.',
  `show_scoreboard_after` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Mostrar el scoreboard automáticamente después del concurso',
  `urgent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el concurso es de alta prioridad y requiere mejor QoS.',
  `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','cpp20-gcc','cpp20-clang','java','kt','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua','go','rs','js') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar en un concurso',
  `recommended` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Mostrar el concurso en la lista de recomendados.',
  `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el concurso ha sido archivado por el administrador.',
  `certificate_cutoff` int DEFAULT NULL COMMENT 'Número de concursantes a premiar con diplomas que mencionan su lugar en el ranking',
  `certificates_status` enum('uninitiated','queued','generated','retryable_error','fatal_error') NOT NULL DEFAULT 'uninitiated' COMMENT 'Estado de la petición de generar diplomas',
  `contest_for_teams` tinyint(1) DEFAULT '0' COMMENT 'Bandera que indica si el concurso es para equipos.',
  `default_show_all_contestants_in_scoreboard` tinyint(1) DEFAULT '0' COMMENT 'Bandera que indica si en el scoreboard se mostrarán todos los concursantes por defecto.',
  `score_mode` enum('partial','all_or_nothing','max_per_group') NOT NULL DEFAULT 'partial' COMMENT 'Indica el tipo de evaluación para el concurso',
  `plagiarism_threshold` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'El porcentaje mínimo permitido de similitud entre un par de envíos. Cuando plagio Seleccionado, será 90.',
  `check_plagiarism` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si se debe correr el detector de plagios.',
  PRIMARY KEY (`contest_id`),
  UNIQUE KEY `contests_alias` (`alias`),
  KEY `rerun_id` (`contest_id`),
  KEY `acl_id` (`acl_id`),
  KEY `fk_cop_problemset_id` (`problemset_id`),
  KEY `fk_cc_rerun_id` (`rerun_id`),
  FULLTEXT KEY `title` (`title`,`description`),
  CONSTRAINT `fk_cc_rerun_id` FOREIGN KEY (`rerun_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `fk_coa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `fk_cop_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Concursos que se llevan a cabo en el juez.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Countries` (
  `country_id` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Catálogos para la normalización';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Course_Clone_Log` (
  `course_clone_log_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador del intento de clonar curso',
  `ip` varchar(40) NOT NULL COMMENT 'Dirección IP desde la cual se intentó clonar el curso.',
  `course_id` int NOT NULL COMMENT 'ID del curso original',
  `new_course_id` int DEFAULT NULL COMMENT 'ID del curso nuevo, null si no se pudo colonar el curso',
  `token_payload` varchar(220) NOT NULL COMMENT 'Claims del token usado para intentar clonar, independientemente de si fue exitoso o no.',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora en la que el usuario intenta clonar el curso',
  `user_id` int NOT NULL COMMENT 'ID del usuario que intentó clonar.',
  `result` enum('unknown','success','token_expired','token_corrupted','token_invalid') NOT NULL DEFAULT 'success' COMMENT 'Resultado obtenido del intento de clonación de curso',
  PRIMARY KEY (`course_clone_log_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  KEY `new_course_id` (`new_course_id`),
  CONSTRAINT `fk_ccl_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`),
  CONSTRAINT `fk_ccl_new_course_id` FOREIGN KEY (`new_course_id`) REFERENCES `Courses` (`course_id`),
  CONSTRAINT `fk_ccl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bitácora de registro para cursos clonados';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Course_Identity_Request` (
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `course_id` int NOT NULL COMMENT 'Curso al cual se necesita un request para ingresar',
  `request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se realizó el request',
  `last_update` timestamp NULL DEFAULT NULL COMMENT 'Última fecha de actualización del request',
  `accepted` tinyint(1) DEFAULT NULL COMMENT 'Indica si la respuesta del request fue aceptada',
  `extra_note` mediumtext COMMENT 'Indica una descripción con el motivo de aceptar o rechazar un usuario al curso',
  `accept_teacher` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.',
  `share_user_information` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.',
  PRIMARY KEY (`identity_id`,`course_id`),
  KEY `course_id` (`course_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_circ_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`),
  CONSTRAINT `fk_ciri_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Usado cuando un curso se registra con admission_mode = registration';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Course_Identity_Request_History` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `course_id` int NOT NULL COMMENT 'Curso al cual se necesita un request para ingresar',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Hora en la que se realizó el request',
  `accepted` tinyint NOT NULL COMMENT 'Indica si la respuesta del request fue aceptada',
  `admin_id` int NOT NULL COMMENT 'Identidad que usuario aceptó / rechazo el request',
  PRIMARY KEY (`history_id`),
  KEY `course_id` (`course_id`),
  KEY `identity_course_hist` (`identity_id`,`course_id`),
  CONSTRAINT `fk_cirhc_curse_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`),
  CONSTRAINT `fk_cirhi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabla para almacenar la respuesta de cada una de las peticiones hechas al curso con admission_mode = registration';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `objective` text,
  `alias` varchar(32) NOT NULL,
  `group_id` int NOT NULL,
  `acl_id` int NOT NULL,
  `level` enum('introductory','intermediate','advanced') DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este curso',
  `finish_time` timestamp NULL DEFAULT NULL,
  `admission_mode` enum('private','registration','public') NOT NULL DEFAULT 'private' COMMENT 'Modalidad en la que se registra un curso.',
  `school_id` int DEFAULT NULL,
  `needs_basic_information` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un curso sólo si ya llenó su información de perfil',
  `requests_user_information` enum('no','optional','required') NOT NULL DEFAULT 'no' COMMENT 'Se solicita información de los participantes para contactarlos posteriormente.',
  `show_scoreboard` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Los estudiantes pueden visualizar el scoreboard de un curso.',
  `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','cpp20-gcc','cpp20-clang','java','kt','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua','go','rs','js') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar en un curso',
  `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el curso ha sido archivado por el administrador.',
  `minimum_progress_for_certificate` int DEFAULT NULL COMMENT 'Progreso mínimo que debe cumplir el estudiante para que se le otorgue el diploma del curso. NULL indica que el curso no da diplomas.',
  `certificates_status` enum('uninitiated','queued','generated','retryable_error','fatal_error') NOT NULL DEFAULT 'uninitiated' COMMENT 'Estado de la petición de generar diplomas',
  `recommended` tinyint NOT NULL DEFAULT '0' COMMENT 'Mostrar el curso en la lista de cursos públicos, los cursos que no tengan la bandera encendida pueden ser cursos públicos pero no se mostrarán en la lista.',
  `teaching_assistant_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el Asistente de enseñanza de IA está habilitado para este curso',
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_alias` (`alias`),
  KEY `fk_ca_acl_id` (`acl_id`),
  KEY `fk_cg_student_group_id` (`group_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `fk_ca_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `fk_cg_student_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups_` (`group_id`),
  CONSTRAINT `fk_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Un curso/clase que un maestro da.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Emails` (
  `email_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Esta tabla permite tener varios emails por persona';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Favorites` (
  `user_id` int NOT NULL,
  `problem_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`problem_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_f_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_f_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Problemas favoritos de los usuarios';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Group_Roles` (
  `group_id` int NOT NULL,
  `role_id` int NOT NULL,
  `acl_id` int NOT NULL,
  PRIMARY KEY (`group_id`,`role_id`,`acl_id`),
  KEY `group_id` (`group_id`),
  KEY `role_id` (`role_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_gr_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups_` (`group_id`),
  CONSTRAINT `fk_gr_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`),
  CONSTRAINT `fk_gra_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los roles que se pueden dar a los grupos.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Groups_` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `acl_id` int NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `groups_alias` (`alias`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_g_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Groups_Identities` (
  `group_id` int NOT NULL,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `share_user_information` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.',
  `privacystatement_consent_id` int DEFAULT NULL COMMENT 'Id del documento con el consentimiento de privacidad',
  `accept_teacher` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.',
  `is_invited` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad ingresará al curso por invitación o le fue compartido el link del curso abierto con registro',
  PRIMARY KEY (`identity_id`,`group_id`),
  KEY `group_id` (`group_id`),
  KEY `identity_id` (`identity_id`),
  KEY `fk_gipc_privacystatement_consent_id` (`privacystatement_consent_id`),
  CONSTRAINT `fk_gii_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_gipc_privacystatement_consent_id` FOREIGN KEY (`privacystatement_consent_id`) REFERENCES `PrivacyStatement_Consent_Log` (`privacystatement_consent_id`),
  CONSTRAINT `fk_gu_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups_` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Groups_Scoreboards` (
  `group_scoreboard_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`group_scoreboard_id`),
  UNIQUE KEY `groups_scoreboards_alias` (`alias`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `fk_gs_user_id` FOREIGN KEY (`group_id`) REFERENCES `Groups_` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Groups_Scoreboards_Problemsets` (
  `group_scoreboard_id` int NOT NULL,
  `problemset_id` int NOT NULL COMMENT 'Conjunto de problemas del scoreboard',
  `only_ac` tinyint(1) NOT NULL DEFAULT '0',
  `weight` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_scoreboard_id`,`problemset_id`),
  KEY `group_scoreboard_id` (`group_scoreboard_id`),
  KEY `problemset_id` (`problemset_id`),
  CONSTRAINT `fk_gsc_group_scoreboard_id` FOREIGN KEY (`group_scoreboard_id`) REFERENCES `Groups_Scoreboards` (`group_scoreboard_id`),
  CONSTRAINT `fk_gsp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Identities` (
  `identity_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(128) DEFAULT NULL COMMENT 'Contraseña del usuario, usando Argon2i o Blowfish',
  `name` varchar(256) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `gender` enum('female','male','other','decline') DEFAULT NULL COMMENT 'Género de la identidad',
  `current_identity_school_id` int DEFAULT NULL,
  PRIMARY KEY (`identity_id`),
  UNIQUE KEY `username` (`username`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`state_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_is_state_id` (`country_id`,`state_id`),
  KEY `language_id` (`language_id`),
  KEY `current_identity_school_id` (`current_identity_school_id`),
  FULLTEXT KEY `ft_user_username` (`username`,`name`),
  CONSTRAINT `fk_ic_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`),
  CONSTRAINT `fk_iis_current_identity_school_id` FOREIGN KEY (`current_identity_school_id`) REFERENCES `Identities_Schools` (`identity_school_id`),
  CONSTRAINT `fk_il_language_id` FOREIGN KEY (`language_id`) REFERENCES `Languages` (`language_id`),
  CONSTRAINT `fk_is_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`),
  CONSTRAINT `fk_iu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Identidades registradas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Identities_Schools` (
  `identity_school_id` int NOT NULL AUTO_INCREMENT,
  `identity_id` int NOT NULL,
  `school_id` int NOT NULL,
  `graduation_date` date DEFAULT NULL,
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`identity_school_id`),
  KEY `identity_id` (`identity_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `fk_isi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_iss_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Todas las escuelas por las que un usuario ha estudiado desde que se unió a omegaUp';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Identity_Login_Log` (
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `ip` int unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_illi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bitácora de inicios de sesión exitosos';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Interviews` (
  `interview_id` int NOT NULL AUTO_INCREMENT,
  `problemset_id` int NOT NULL,
  `acl_id` int NOT NULL COMMENT 'La lista de control de acceso del problema',
  `alias` varchar(32) NOT NULL COMMENT 'El alias de la entrevista',
  `title` varchar(256) NOT NULL COMMENT 'El titulo de la entrevista.',
  `description` tinytext NOT NULL COMMENT 'Una breve descripcion de la entrevista.',
  `window_length` int NOT NULL COMMENT 'Indica el tiempo que tiene el usuario para envíar soluciones.',
  PRIMARY KEY (`interview_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_ia_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `fk_ip_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de id_concuros que se usan para entrevista';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Languages` (
  `language_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `country_id` char(3) DEFAULT NULL COMMENT 'Se guarda la relación con el país para defaultear más rápido.',
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `nombre_UNIQUE` (`name`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `fk_l_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de idiomas que potencialmente se soportarían.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `message` tinytext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`,`recipient_id`),
  KEY `fk_m_recipient_id` (`recipient_id`),
  CONSTRAINT `fk_m_recipient_id` FOREIGN KEY (`recipient_id`) REFERENCES `Users` (`user_id`),
  CONSTRAINT `fk_m_sender_id` FOREIGN KEY (`sender_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Sistema de mensajería dentro del sitio.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'Identificador de usuario',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `contents` text NOT NULL COMMENT 'JSON con el contenido de la notificación',
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_nu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Notificaciones';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Permissions` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'El nombre corto del permiso.',
  `description` varchar(100) NOT NULL COMMENT 'La descripción humana del permiso.',
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los permisos que se pueden dar a los roles.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Plagiarisms` (
  `plagiarism_id` int NOT NULL AUTO_INCREMENT COMMENT 'El identificador único para cada potencial caso de plagio',
  `contest_id` int NOT NULL,
  `submission_id_1` int NOT NULL COMMENT 'El identificador del envío del primer código plagiado',
  `submission_id_2` int NOT NULL COMMENT 'El identificador del envío del segundo código plagiado',
  `score_1` int NOT NULL COMMENT 'porcentaje de plagio encontrado usando copydetect en el envío 1',
  `score_2` int NOT NULL COMMENT 'porcentaje de plagio encontrado usando copydetect en el envío 2',
  `contents` text NOT NULL COMMENT 'Almacena los rangos de números de línea de las similitudes',
  PRIMARY KEY (`plagiarism_id`),
  KEY `fk_pc_contest_id` (`contest_id`),
  KEY `fk_ps_submission_id_1` (`submission_id_1`),
  KEY `fk_ps_submission_id_2` (`submission_id_2`),
  CONSTRAINT `fk_pc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `fk_ps_submission_id_1` FOREIGN KEY (`submission_id_1`) REFERENCES `Submissions` (`submission_id`),
  CONSTRAINT `fk_ps_submission_id_2` FOREIGN KEY (`submission_id_2`) REFERENCES `Submissions` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Los casos potenciales de plagio encontrados por el algoritmo de detección.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PrivacyStatement_Consent_Log` (
  `privacystatement_consent_id` int NOT NULL AUTO_INCREMENT COMMENT 'Id del consentimiento de privacidad almacenado en el log',
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `privacystatement_id` int NOT NULL COMMENT 'Id del documento de privacidad',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora en la que el usuario acepta las nuevas políticas',
  PRIMARY KEY (`privacystatement_consent_id`),
  UNIQUE KEY `identity_privacy` (`identity_id`,`privacystatement_id`),
  KEY `fk_pcp_privacystatement_id` (`privacystatement_id`),
  CONSTRAINT `fk_pci_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_pcp_privacystatement_id` FOREIGN KEY (`privacystatement_id`) REFERENCES `PrivacyStatements` (`privacystatement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Log para auditar las identidades que han aceptado los documentos de privacidad de omegaUp.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PrivacyStatements` (
  `privacystatement_id` int NOT NULL AUTO_INCREMENT COMMENT 'Id del documento de privacidad',
  `git_object_id` varchar(50) NOT NULL COMMENT 'Id de la versión del documento en el que se almacena la nueva política',
  `type` enum('privacy_policy','contest_optional_consent','contest_required_consent','course_optional_consent','course_required_consent','accept_teacher') NOT NULL DEFAULT 'privacy_policy' COMMENT 'Tipo de documento de privacidad',
  PRIMARY KEY (`privacystatement_id`),
  UNIQUE KEY `type_git_object_id` (`type`,`git_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabla encargada de almacenar cada una de las versiones en git de los documentos de privacidad.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problem_Of_The_Week` (
  `problem_of_the_week_id` int NOT NULL AUTO_INCREMENT,
  `problem_id` int NOT NULL COMMENT 'El id del problema escogido como problema de la semana.',
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'El inicio de la semana de la cual este problema fue elegido como el mejor de la semana.',
  `difficulty` enum('easy','hard') NOT NULL COMMENT 'En algún momento tendremos un problema fácil y uno difícil.',
  PRIMARY KEY (`problem_of_the_week_id`),
  UNIQUE KEY `idx_time_difficulty` (`time`,`difficulty`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de problemas de la semana.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problem_Viewed` (
  `problem_id` int NOT NULL,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `view_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`problem_id`,`identity_id`),
  KEY `problem_id` (`problem_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_pv_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_pvi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabla de vistas de problemas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problems` (
  `problem_id` int NOT NULL AUTO_INCREMENT,
  `acl_id` int NOT NULL,
  `visibility` int NOT NULL DEFAULT '1' COMMENT '-1 banned, 0 private, 1 public, 2 recommended',
  `title` varchar(256) NOT NULL,
  `alias` varchar(32) NOT NULL,
  `commit` char(40) NOT NULL DEFAULT 'published' COMMENT 'El hash SHA1 del commit en la rama master del problema.',
  `current_version` char(40) NOT NULL COMMENT 'El hash SHA1 del árbol de la rama private.',
  `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','cpp20-gcc','cpp20-clang','java','kt','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua','go','rs','js') NOT NULL DEFAULT 'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js',
  `input_limit` int NOT NULL DEFAULT '10240',
  `visits` int NOT NULL DEFAULT '0',
  `submissions` int NOT NULL DEFAULT '0',
  `accepted` int NOT NULL DEFAULT '0',
  `difficulty` double DEFAULT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(256) DEFAULT NULL,
  `order` enum('normal','inverse') NOT NULL DEFAULT 'normal',
  `deprecated` tinyint(1) NOT NULL DEFAULT '0',
  `email_clarifications` tinyint(1) NOT NULL DEFAULT '0',
  `quality` double DEFAULT NULL,
  `quality_histogram` text COMMENT 'Valores del histograma de calidad del problema.',
  `difficulty_histogram` text COMMENT 'Valores del histograma de dificultad del problema.',
  `quality_seal` tinyint(1) NOT NULL DEFAULT '0',
  `show_diff` enum('none','examples','all') NOT NULL DEFAULT 'none' COMMENT 'Determina si el problema es educativo y debe mostrar diferencias en casos de ejemplos, en todos o en ninguno.',
  `allow_user_add_tags` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Bandera que sirve para indicar si un problema puede permitir que los usuarios agreguen tags.',
  PRIMARY KEY (`problem_id`),
  UNIQUE KEY `problems_alias` (`alias`),
  KEY `acl_id` (`acl_id`),
  KEY `idx_problems_visibility` (`visibility`),
  KEY `idx_quality_seal` (`quality_seal`),
  FULLTEXT KEY `ft_alias_title` (`alias`,`title`),
  CONSTRAINT `fk_pa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Se crea un registro por cada prob externo.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problems_Forfeited` (
  `user_id` int NOT NULL COMMENT 'Identificador de usuario',
  `problem_id` int NOT NULL,
  `forfeited_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`problem_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_pfp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_pfu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Problemas que no cuentan para el ranking';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problems_Languages` (
  `problem_id` int NOT NULL,
  `language_id` int NOT NULL,
  PRIMARY KEY (`problem_id`,`language_id`),
  KEY `problem_id` (`problem_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `fk_pl_language_id` FOREIGN KEY (`language_id`) REFERENCES `Languages` (`language_id`),
  CONSTRAINT `fk_pl_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Las traducciones viven en el filesystem y no en la bdd.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problems_Tags` (
  `problem_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `source` enum('owner','voted','quality') NOT NULL DEFAULT 'owner' COMMENT 'El origen del tag: elegido por el autor, elegido por los usuarios o elegido por un revisor.',
  PRIMARY KEY (`problem_id`,`tag_id`),
  KEY `problem_id` (`problem_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `fk_ptp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_ptt_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `Tags` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tags privados para los problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemset_Access_Log` (
  `problemset_id` int NOT NULL,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `ip` int unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_palc_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`),
  CONSTRAINT `fk_pali_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bitácora de acceso a listas de problemas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemset_Identities` (
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `problemset_id` int NOT NULL,
  `access_time` datetime DEFAULT NULL COMMENT 'Hora a la que entró el usuario al concurso',
  `end_time` datetime DEFAULT NULL COMMENT 'Hora en la que finaliza un concurso para el usuario cuando se habilita la opción de inicios diferentes',
  `score` int NOT NULL DEFAULT '1' COMMENT 'Indica el puntaje que obtuvo el usuario en el concurso',
  `time` int NOT NULL DEFAULT '1' COMMENT 'Indica el tiempo que acumulo en usuario en el concurso',
  `share_user_information` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un concurso si está de acuerdo en divulgar su información.',
  `privacystatement_consent_id` int DEFAULT NULL COMMENT 'Id del documento con el consentimiento de privacidad',
  `is_invited` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad ingresará al concurso por invitación o lo encontró en el listado de concursos públicos',
  PRIMARY KEY (`identity_id`,`problemset_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  KEY `fk_pipc_privacystatement_consent_id` (`privacystatement_consent_id`),
  CONSTRAINT `fk_pii_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_pipc_privacystatement_consent_id` FOREIGN KEY (`privacystatement_consent_id`) REFERENCES `PrivacyStatement_Consent_Log` (`privacystatement_consent_id`),
  CONSTRAINT `fk_puc_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Concursantes que pueden interactuar con una lista de problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemset_Identity_Request` (
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `problemset_id` int NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NULL DEFAULT NULL,
  `accepted` tinyint(1) DEFAULT NULL,
  `extra_note` mediumtext,
  PRIMARY KEY (`identity_id`,`problemset_id`),
  KEY `fk_purp_problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_piri_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_purp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Used when admission_mode = registration';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemset_Identity_Request_History` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `problemset_id` int NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `accepted` tinyint NOT NULL,
  `admin_id` int NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `fk_purhp_problemset_id` (`problemset_id`),
  KEY `identity_problemset_hist` (`identity_id`,`problemset_id`),
  CONSTRAINT `fk_pirhi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_purhp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemset_Problem_Opened` (
  `problemset_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `open_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`problemset_id`,`problem_id`,`identity_id`),
  KEY `problem_id` (`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_ppo_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_ppo_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`),
  CONSTRAINT `fk_ppoi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registro de primer acceso a problemas de un conjunto.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemset_Problems` (
  `problemset_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `commit` char(40) NOT NULL DEFAULT 'published' COMMENT 'El hash SHA1 del commit en la rama master del problema.',
  `version` char(40) NOT NULL COMMENT 'El hash SHA1 del árbol de la rama private.',
  `points` double NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '1' COMMENT 'Define el orden de aparición de los problemas en una lista de problemas',
  `is_extra_problem` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`problemset_id`,`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `problem_id` (`problem_id`),
  KEY `idx_problemset_problems_ids` (`problem_id`,`problemset_id`),
  CONSTRAINT `fk_ppp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_ppp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Los problemas de cada conjunto';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Problemsets` (
  `problemset_id` int NOT NULL AUTO_INCREMENT COMMENT 'El identificador único para cada conjunto de problemas',
  `acl_id` int NOT NULL COMMENT 'La lista de control de acceso compartida con su container',
  `access_mode` enum('private','public','registration') NOT NULL DEFAULT 'public' COMMENT 'La modalidad de acceso a este conjunto de problemas',
  `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','cpp20-gcc','cpp20-clang','java','kt','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua','go','rs','js') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas',
  `needs_basic_information` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un concurso sólo si ya llenó su información de perfil',
  `requests_user_information` enum('no','optional','required') NOT NULL DEFAULT 'no' COMMENT 'Se solicita información de los participantes para contactarlos posteriormente.',
  `scoreboard_url` varchar(30) NOT NULL COMMENT 'Token para la url del scoreboard en problemsets',
  `scoreboard_url_admin` varchar(30) NOT NULL COMMENT 'Token para la url del scoreboard de admin en problemsets',
  `type` enum('Contest','Assignment','Interview') NOT NULL DEFAULT 'Contest' COMMENT 'Almacena el tipo de problemset que se ha creado',
  `contest_id` int DEFAULT NULL COMMENT 'Id del concurso',
  `assignment_id` int DEFAULT NULL COMMENT 'Id del curso',
  `interview_id` int DEFAULT NULL COMMENT 'Id de la entrevista',
  PRIMARY KEY (`problemset_id`),
  UNIQUE KEY `problemset_id` (`problemset_id`,`contest_id`,`assignment_id`,`interview_id`),
  KEY `acl_id` (`acl_id`),
  KEY `contest_id` (`contest_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `interview_id` (`interview_id`),
  CONSTRAINT `fk_psa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `Problemsets_ibfk_1` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `Problemsets_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `Assignments` (`assignment_id`),
  CONSTRAINT `Problemsets_ibfk_3` FOREIGN KEY (`interview_id`) REFERENCES `Interviews` (`interview_id`),
  CONSTRAINT `Problemsets_chk_1` CHECK ((((cast((`contest_id` is not null) as unsigned) + cast((`assignment_id` is not null) as unsigned)) + cast((`interview_id` is not null) as unsigned)) <= 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Conjunto de problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `QualityNomination_Comments` (
  `qualitynomination_comment_id` int NOT NULL AUTO_INCREMENT,
  `qualitynomination_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'El usuario que emitió el comentario',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este comentario',
  `vote` int NOT NULL COMMENT 'El voto emitido en este comentario. En el rango de [-2, +2]',
  `contents` text NOT NULL COMMENT 'El contenido de el comentario',
  PRIMARY KEY (`qualitynomination_comment_id`),
  KEY `user_id` (`user_id`),
  KEY `qualitynomination_id` (`qualitynomination_id`),
  CONSTRAINT `fk_qnc_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`),
  CONSTRAINT `fk_qnc_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Comentarios para una nominación';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `QualityNomination_Log` (
  `qualitynomination_log_id` int NOT NULL AUTO_INCREMENT,
  `qualitynomination_id` int NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `from_status` enum('open','warning','resolved','banned') NOT NULL DEFAULT 'open',
  `to_status` enum('open','warning','resolved','banned') NOT NULL DEFAULT 'open',
  `rationale` text,
  PRIMARY KEY (`qualitynomination_log_id`),
  KEY `user_id` (`user_id`),
  KEY `qualitynomination_id` (`qualitynomination_id`),
  CONSTRAINT `fk_qnl_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`),
  CONSTRAINT `fk_qnl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bitácora de cambios a nominaciones';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `QualityNomination_Reviewers` (
  `qualitynomination_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'El revisor al que fue asignado esta nominación',
  PRIMARY KEY (`qualitynomination_id`,`user_id`),
  KEY `fk_qnr_user_id` (`user_id`),
  CONSTRAINT `fk_qnr_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`),
  CONSTRAINT `fk_qnr_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='La lista de revisores para cada nominación';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `QualityNominations` (
  `qualitynomination_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'El usuario que nominó el problema',
  `problem_id` int NOT NULL COMMENT 'El problema que fue nominado',
  `nomination` enum('suggestion','quality_tag','promotion','demotion','dismissal') NOT NULL DEFAULT 'suggestion' COMMENT 'El tipo de nominación',
  `contents` text NOT NULL COMMENT 'Un blob json con el contenido de la nominación',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de esta nominación',
  `status` enum('open','warning','resolved','banned') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación',
  PRIMARY KEY (`qualitynomination_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`),
  KEY `idx_nomination` (`nomination`),
  KEY `idx_nomination_problem` (`nomination`,`problem_id`),
  CONSTRAINT `fk_qn_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_qn_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='La cola de nominación a promoción / democión de problemas';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'El nombre corto del rol.',
  `description` varchar(100) NOT NULL COMMENT 'La descripción humana del rol.',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los roles que se pueden dar a los usuarios.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Roles_Permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `fk_rp_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `Permissions` (`permission_id`),
  CONSTRAINT `fk_rp_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los roles que se pueden dar a los usuarios.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Run_Counts` (
  `date` date NOT NULL,
  `total` int NOT NULL DEFAULT '0',
  `ac_count` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda la cantidad de runs que se han realizado hasta la fecha.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Runs` (
  `run_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL COMMENT 'El envío',
  `version` char(40) NOT NULL COMMENT 'El hash SHA1 del árbol de la rama private.',
  `commit` char(40) NOT NULL COMMENT 'El hash SHA1 del commit en la rama master del problema con el que se realizó el envío.',
  `status` enum('new','waiting','compiling','running','ready','uploading') NOT NULL DEFAULT 'new',
  `verdict` enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE','VE') NOT NULL,
  `runtime` int NOT NULL DEFAULT '0',
  `penalty` int NOT NULL DEFAULT '0',
  `memory` int NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `contest_score` double DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `judged_by` char(32) DEFAULT NULL,
  PRIMARY KEY (`run_id`),
  UNIQUE KEY `runs_versions` (`submission_id`,`version`),
  KEY `submission_id` (`submission_id`),
  KEY `status_submission_id` (`status`,`submission_id`),
  CONSTRAINT `fk_r_submission_id` FOREIGN KEY (`submission_id`) REFERENCES `Submissions` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Estado de todas las ejecuciones.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Runs_Groups` (
  `case_run_id` int NOT NULL AUTO_INCREMENT,
  `run_id` int NOT NULL,
  `group_name` char(40) NOT NULL,
  `score` double NOT NULL DEFAULT '0',
  `verdict` enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE','VE') NOT NULL,
  PRIMARY KEY (`case_run_id`),
  UNIQUE KEY `run_id` (`run_id`,`group_name`),
  CONSTRAINT `Runs_Groups_ibfk_1` FOREIGN KEY (`run_id`) REFERENCES `Runs` (`run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda los grupos de runs.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `School_Of_The_Month` (
  `school_of_the_month_id` int NOT NULL AUTO_INCREMENT,
  `school_id` int NOT NULL,
  `time` date NOT NULL DEFAULT '2000-01-01',
  `ranking` int NOT NULL COMMENT 'El lugar que tuvo la escuela en el mes.',
  `selected_by` int DEFAULT NULL COMMENT 'Identidad que seleccionó a la escuela.',
  `score` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`school_of_the_month_id`),
  UNIQUE KEY `rank_time` (`ranking`,`time`),
  KEY `school_of_the_month_id` (`school_of_the_month_id`),
  KEY `school_id` (`school_id`),
  KEY `selected_by` (`selected_by`),
  KEY `idx_time` (`time`),
  CONSTRAINT `fk_sotmi_identity_id` FOREIGN KEY (`selected_by`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_sotms_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Escuelas del Mes';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Schools` (
  `school_id` int NOT NULL AUTO_INCREMENT,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `ranking` int DEFAULT NULL,
  `score` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`school_id`),
  UNIQUE KEY `name_country_id_state_id` (`name`,`country_id`,`state_id`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`country_id`,`state_id`),
  KEY `idx_schools_name` (`name`),
  KEY `idx_schools_score` (`score`),
  CONSTRAINT `fk_scc_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`),
  CONSTRAINT `fk_ss_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Catálogos para la normalización';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Schools_Problems_Solved_Per_Month` (
  `school_pspm_id` int NOT NULL AUTO_INCREMENT,
  `school_id` int NOT NULL COMMENT 'Identificador de escuela',
  `time` date NOT NULL,
  `problems_solved` int NOT NULL,
  PRIMARY KEY (`school_pspm_id`),
  UNIQUE KEY `school_time` (`school_id`,`time`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `fk_spspms_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='La cantidad de problemas que resolvieron los usuarios de una escuela en un mes y año determinados.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `States` (
  `country_id` char(3) NOT NULL,
  `state_id` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`country_id`,`state_id`),
  KEY `country_id` (`country_id`),
  CONSTRAINT `fk_stc_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Catálogos para la normalización';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Submission_Feedback` (
  `submission_feedback_id` int NOT NULL AUTO_INCREMENT,
  `identity_id` int NOT NULL COMMENT 'Identidad de quien envió el feedback',
  `submission_id` int NOT NULL COMMENT 'Identificador del envío asociado',
  `feedback` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se envió el feedback',
  `range_bytes_start` int DEFAULT NULL COMMENT 'Inicio de la subcadena seleccionada (en bytes) para agregarle el comentario',
  `range_bytes_end` int DEFAULT NULL COMMENT 'Fin de la subcadena seleccionada (en bytes) para agregarle el comentario',
  PRIMARY KEY (`submission_feedback_id`),
  KEY `fk_sfi_identity_id` (`identity_id`),
  KEY `fk_sfs_submission_id` (`submission_id`),
  CONSTRAINT `fk_sfi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_sfs_submission_id` FOREIGN KEY (`submission_id`) REFERENCES `Submissions` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Almacena el feedback dejado por los profesores para los envíos de los estudiantes.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Submission_Feedback_Thread` (
  `submission_feedback_thread_id` int NOT NULL AUTO_INCREMENT,
  `submission_feedback_id` int NOT NULL COMMENT 'Identificador del comentario asociado',
  `identity_id` int NOT NULL COMMENT 'Identidad de quien envió el feedback',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se envió el feedback',
  `contents` text NOT NULL,
  PRIMARY KEY (`submission_feedback_thread_id`),
  KEY `fk_sfs_submission_feedback_id` (`submission_feedback_id`),
  KEY `fk_sfti_identity_id` (`identity_id`),
  CONSTRAINT `fk_sfs_submission_feedback_id` FOREIGN KEY (`submission_feedback_id`) REFERENCES `Submission_Feedback` (`submission_feedback_id`),
  CONSTRAINT `fk_sfti_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Darle seguimiento a un comentario';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Submission_Log` (
  `problemset_id` int DEFAULT NULL,
  `submission_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `ip` int unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`submission_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_sli_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_slp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`),
  CONSTRAINT `fk_slr_submission_id` FOREIGN KEY (`submission_id`) REFERENCES `Submissions` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bitácora de envíos';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `current_run_id` int DEFAULT NULL COMMENT 'La evaluación actual del envío',
  `identity_id` int NOT NULL COMMENT 'Identidad del usuario',
  `problem_id` int NOT NULL,
  `problemset_id` int DEFAULT NULL,
  `guid` char(32) NOT NULL,
  `language` enum('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','cpp20-gcc','cpp20-clang','java','kt','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua','go','rs','js') NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('new','waiting','compiling','running','ready','uploading') NOT NULL DEFAULT 'new',
  `verdict` enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE','VE') NOT NULL,
  `submit_delay` int NOT NULL DEFAULT '0',
  `type` enum('normal','test','disqualified') DEFAULT 'normal',
  `school_id` int DEFAULT NULL,
  PRIMARY KEY (`submission_id`),
  UNIQUE KEY `submissions_guid` (`guid`),
  KEY `problem_id` (`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  KEY `fk_s_current_run_id` (`current_run_id`),
  KEY `school_id` (`school_id`),
  KEY `school_id_problem_id` (`school_id`,`problem_id`),
  KEY `verdict_type_time` (`verdict`,`type`,`time`),
  KEY `idx_time_status` (`time`,`status`),
  CONSTRAINT `fk_s_current_run_id` FOREIGN KEY (`current_run_id`) REFERENCES `Runs` (`run_id`),
  CONSTRAINT `fk_s_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_s_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_s_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`),
  CONSTRAINT `fk_ss_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Envíos';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el tag es público o no. Los usuarios solo pueden agregar tags privados',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tags privados para los problemas.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Team_Groups` (
  `team_group_id` int NOT NULL AUTO_INCREMENT,
  `acl_id` int NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `number_of_contestants` int NOT NULL DEFAULT '3' COMMENT 'Número de concursantes para los equipos del grupo',
  PRIMARY KEY (`team_group_id`),
  UNIQUE KEY `team_group_alias` (`alias`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_tg_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Team_Users` (
  `team_id` int NOT NULL,
  `identity_id` int NOT NULL COMMENT 'Id de la identidad que pertenece al equipo',
  PRIMARY KEY (`team_id`,`identity_id`),
  KEY `team_id` (`team_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_tui_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_tut_team_id` FOREIGN KEY (`team_id`) REFERENCES `Teams` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Teams` (
  `team_id` int NOT NULL AUTO_INCREMENT,
  `team_group_id` int NOT NULL COMMENT 'Id del grupo de equipos',
  `identity_id` int NOT NULL COMMENT 'La identidad asociada al equipo',
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `team_group_identity` (`team_group_id`,`identity_id`),
  KEY `identity_id` (`identity_id`),
  KEY `team_group_id` (`team_group_id`),
  CONSTRAINT `fk_ti_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_ttg_team_group_id` FOREIGN KEY (`team_group_id`) REFERENCES `Team_Groups` (`team_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Teams_Group_Roles` (
  `team_group_id` int NOT NULL,
  `role_id` int NOT NULL,
  `acl_id` int NOT NULL,
  PRIMARY KEY (`team_group_id`,`role_id`,`acl_id`),
  KEY `team_group_id` (`team_group_id`),
  KEY `role_id` (`role_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_tgra_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `fk_tgrg_team_group_id` FOREIGN KEY (`team_group_id`) REFERENCES `Team_Groups` (`team_group_id`),
  CONSTRAINT `fk_tgrr_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los roles que se pueden dar a los grupos de equipos.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User_Rank` (
  `user_id` int NOT NULL,
  `ranking` int DEFAULT NULL,
  `problems_solved_count` int NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `school_id` int DEFAULT NULL,
  `author_score` double NOT NULL DEFAULT '0',
  `author_ranking` int DEFAULT NULL,
  `classname` varchar(50) DEFAULT NULL COMMENT 'Almacena la clase precalculada para no tener que determinarla en tiempo de ejecucion.',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualiza el rank de usuario',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `rank` (`ranking`),
  KEY `fk_ur_state_id` (`country_id`,`state_id`),
  KEY `fk_ur_school_id` (`school_id`),
  CONSTRAINT `fk_ur_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`),
  CONSTRAINT `fk_ur_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`),
  CONSTRAINT `fk_ur_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda el ranking de usuarios por problemas resueltos.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User_Rank_Cutoffs` (
  `score` double NOT NULL,
  `percentile` double NOT NULL,
  `classname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda los valores del ranking para los cuales hay un cambio de color.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User_Roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `acl_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`,`acl_id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_ur_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`),
  CONSTRAINT `fk_ur_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  CONSTRAINT `fk_ura_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los roles que se pueden dar a los usuarios.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `facebook_user_id` varchar(20) DEFAULT NULL COMMENT 'Facebook ID for this user.',
  `git_token` varchar(128) DEFAULT NULL COMMENT 'Token de acceso para git, usando Argon2i',
  `main_email_id` int DEFAULT NULL,
  `main_identity_id` int DEFAULT NULL COMMENT 'Identidad principal del usuario',
  `has_learning_objective` tinyint(1) DEFAULT NULL COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para aprender.',
  `has_teaching_objective` tinyint(1) DEFAULT NULL COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para enseñar.',
  `has_scholar_objective` tinyint(1) DEFAULT NULL COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para la escuela.',
  `has_competitive_objective` tinyint(1) DEFAULT NULL COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para programación competitiva.',
  `scholar_degree` enum('none','early_childhood','pre_primary','primary','lower_secondary','upper_secondary','post_secondary','tertiary','bachelors','master','doctorate') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_id` varchar(50) DEFAULT NULL,
  `deletion_token` varchar(50) DEFAULT NULL,
  `reset_digest` varchar(45) DEFAULT NULL,
  `reset_sent_at` datetime DEFAULT NULL,
  `hide_problem_tags` tinyint(1) DEFAULT NULL COMMENT 'Determina si el usuario quiere ocultar las etiquetas de los problemas',
  `in_mailing_list` tinyint(1) NOT NULL DEFAULT '0',
  `is_private` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Determina si el usuario eligió no compartir su información de manera pública',
  `preferred_language` enum('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','cpp20-gcc','cpp20-clang','java','kt','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua','go','rs','js') DEFAULT NULL COMMENT 'El lenguaje de programación de preferencia de este usuario',
  `parent_verified` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del padre cuando este verifica la cuenta de su hijo',
  `creation_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se creó la cuenta de usuario',
  `parental_verification_token` varchar(25) DEFAULT NULL COMMENT 'Token que se generará para los usuarios menores de 13 años al momento de registrar su cuenta, el cuál será enviado por correo electrónico al padre',
  `parent_email_verification_initial` timestamp NULL DEFAULT NULL COMMENT 'Almacena la hora en que se envió el correo electrónico de verificación',
  `parent_email_verification_deadline` timestamp NULL DEFAULT NULL COMMENT 'Almacena la hora y fecha límite que tienen los padres para verificar la cuenta de su hijo menor a 13 años',
  `parent_email_id` int DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `fk_main_email_id` (`main_email_id`),
  KEY `fk_main_identity_id` (`main_identity_id`),
  KEY `fk_parent_email_id` (`parent_email_id`),
  KEY `verification_id` (`verification_id`),
  KEY `idx_is_private` (`is_private`),
  CONSTRAINT `fk_main_email_id` FOREIGN KEY (`main_email_id`) REFERENCES `Emails` (`email_id`),
  CONSTRAINT `fk_main_identity_id` FOREIGN KEY (`main_identity_id`) REFERENCES `Identities` (`identity_id`),
  CONSTRAINT `fk_parent_email_id` FOREIGN KEY (`parent_email_id`) REFERENCES `Emails` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Usuarios registrados.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users_Badges` (
  `user_badge_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'Identificador de usuario',
  `badge_alias` varchar(32) NOT NULL COMMENT 'Identificador de badge',
  `assignation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_badge_id`),
  KEY `user_id` (`user_id`),
  KEY `user_badge` (`badge_alias`),
  CONSTRAINT `fk_ubu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Badges de Usuario';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users_Experiments` (
  `user_id` int NOT NULL,
  `experiment` varchar(256) NOT NULL,
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ueu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda los experimentos habilitados para un usuario.';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

