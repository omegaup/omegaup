CREATE TABLE IF NOT EXISTS `Announcement` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del aviso',
  `user_id` int(11) NOT NULL COMMENT 'UserID del autor de este aviso',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este aviso',
  `description` text NOT NULL COMMENT 'Mensaje de texto del aviso',
  PRIMARY KEY (`announcement_id`)
);

CREATE TABLE IF NOT EXISTS `Auth_Tokens` (
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  PRIMARY KEY (`token`)
);

CREATE TABLE IF NOT EXISTS `Badges` (
  `badge_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT 'MyBadge',
  `image_url` varchar(45) NOT NULL,
  `description` varchar(500) NOT NULL COMMENT 'La descripcion habla de como se obtuvo el badge, de forma corta.',
  `hint` varchar(100) DEFAULT NULL COMMENT 'Tip de como desbloquear el badge.',
  PRIMARY KEY (`badge_id`)
);

CREATE TABLE IF NOT EXISTS `Clarifications` (
  `clarification_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL COMMENT 'Autor de la clarificación.',
  `message` text NOT NULL,
  `answer` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `problem_id` int(11) NOT NULL COMMENT 'Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema.',
  `contest_id` int(11) DEFAULT NULL COMMENT 'Puede ser nulo si la clarificacion no se da en un concurso.',
  `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sólo las clarificaciones que el problemsetter marque como publicacbles apareceran en la lista que toda la banda puede ver. Sino, solo al usuario. ',
  PRIMARY KEY (`clarification_id`)
);

CREATE TABLE IF NOT EXISTS `Coder_of_the_Month` (
  `coder_of_the_month_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` tinytext,
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'Fecha no es UNIQUE por si hay más de 1 coder de mes.',
  `interview_url` varchar(256) DEFAULT NULL COMMENT 'Para linekar a un post del blog con entrevistas.',
  PRIMARY KEY (`coder_of_the_month_id`)
);

CREATE TABLE IF NOT EXISTS `Contests` (
  `contest_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador unico para cada concurso',
  `title` varchar(256) NOT NULL COMMENT 'El titulo que aparecera en cada concurso',
  `description` tinytext NOT NULL COMMENT 'Una breve descripcion de cada concurso.',
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora de inicio de este concurso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora de finalizacion de este concurso',
  `window_length` int(11) DEFAULT NULL COMMENT 'Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso',
  `director_id` int(11) NOT NULL COMMENT 'el userID del usuario que creo este concurso',
  `rerun_id` int(11) NOT NULL COMMENT 'Este campo es para las repeticiones de algún concurso',
  `public` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'False implica concurso cerrado, ver la tabla ConcursantesConcurso',
  `alias` varchar(20) NOT NULL COMMENT 'Almacenará el token necesario para acceder al concurso',
  `scoreboard` int(11) NOT NULL DEFAULT '1' COMMENT 'Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible',
  `points_decay_factor` double NOT NULL DEFAULT 0 COMMENT 'Valor de 0 a 1, indicando la tasa de decaimiento de los puntos',
  `partial_score` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos',
  `submissions_gap` int(11) NOT NULL DEFAULT '1' COMMENT 'Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro',
  `feedback` varchar(10) NOT NULL,
  `penalty` int(11) NOT NULL DEFAULT '1' COMMENT 'Entero indicando el número de minutos con que se penaliza por recibir un no-accepted',
  `penalty_time_start` varchar(10) NOT NULL COMMENT 'Indica el momento cuando se inicia a contar el timpo: cuando inicia el concurso o cuando se abre el problema',
  `penalty_calc_policy` varchar(3) DEFAULT 'sum' NOT NULL COMMENT 'Indica como afecta el penalty al score.',
  `urgent` tinyint(1) DEFAULT '0' NOT NULL COMMENT 'Indica si el concurso es de alta prioridad y requiere mejor QoS.',
  PRIMARY KEY (`contest_id`)
);

CREATE TABLE IF NOT EXISTS `Contests_Users` (
  `user_id` int(11) NOT NULL,
  `contest_id` int(11) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'Hora a la que entró el usuario al concurso',
  `score` int(11) NOT NULL DEFAULT '1' COMMENT 'Índica el puntaje que obtuvo el usuario en el concurso',
  `time` int(11) NOT NULL DEFAULT '1' COMMENT 'Índica el tiempo que acumulo en usuario en el concurso',
  PRIMARY KEY (`user_id`,`contest_id`)
);

CREATE TABLE IF NOT EXISTS `Contest_Problems` (
  `contest_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `points` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contest_id`,`problem_id`)
);

CREATE TABLE IF NOT EXISTS `Contest_Problem_Opened` (
  `contest_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `open_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contest_id`,`problem_id`,`user_id`)
);

CREATE TABLE IF NOT EXISTS `Countries` (
  `country_id` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`country_id`)
);

CREATE TABLE IF NOT EXISTS `Favorites` (
  `user_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`problem_id`)
);

CREATE TABLE IF NOT EXISTS `Languages` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `country_id` char(3) DEFAULT NULL COMMENT 'Se guarda la relación con el país para defaultear más rápido.',
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `nombre_UNIQUE` (`name`)
);

CREATE TABLE IF NOT EXISTS `Messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` tinytext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
);

CREATE TABLE IF NOT EXISTS `Password_Change` (
  `user_id` int(11) NOT NULL COMMENT 'Identificador de a que usuario pertenece este token',
  `token` char(64) NOT NULL COMMENT 'El token que se genera aleatoriamente para luego comparar cuando el usuario haga click en el link',
  `ip` char(15) NOT NULL COMMENT 'El ip desde donde se genero este reseteo de password',
  `expiration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'La fecha en que vence este token',
  PRIMARY KEY (`user_id`)
);

CREATE TABLE IF NOT EXISTS `Permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'El nombre corto del permiso.',
  `description` varchar(100) NOT NULL COMMENT 'La descripción humana del permiso.',
  PRIMARY KEY (`permission_id`)
);

CREATE TABLE IF NOT EXISTS `Problems` (
  `problem_id` int(11) NOT NULL AUTO_INCREMENT,
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `author_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `alias` varchar(10) DEFAULT NULL,
  `validator` varchar(15) NOT NULL DEFAULT 'token-numeric',
  `server` varchar(15) DEFAULT NULL,
  `remote_id` varchar(10) DEFAULT NULL,
  `time_limit` int(11) DEFAULT '3000',
  `memory_limit` int(11) DEFAULT '64',
  `output_limit` int(11) NOT NULL DEFAULT '10240',
  `visits` int(11) NOT NULL DEFAULT '0',
  `submissions` int(11) NOT NULL DEFAULT '0',
  `accepted` int(11) NOT NULL DEFAULT '0',
  `difficulty` double NOT NULL DEFAULT '0',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(256) DEFAULT NULL,
  `order` varchar(10) NOT NULL DEFAULT 'normal',
  `slow` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`problem_id`)
);

CREATE TABLE IF NOT EXISTS `Problems_Badges` (
  `badge_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  PRIMARY KEY (`badge_id`,`problem_id`)
);

CREATE TABLE IF NOT EXISTS `Problems_Languages` (
  `problem_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  PRIMARY KEY (`problem_id`,`language_id`)
);

CREATE TABLE IF NOT EXISTS `Problems_Tags` (
  `problem_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`problem_id`,`tag_id`)
);

CREATE TABLE IF NOT EXISTS `Roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'El nombre corto del rol.',
  `description` varchar(100) NOT NULL COMMENT 'La descripción humana del rol.',
  PRIMARY KEY (`role_id`)
);

CREATE TABLE IF NOT EXISTS `Roles_Permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`)
);

CREATE TABLE IF NOT EXISTS `Runs` (
  `run_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `contest_id` int(11) DEFAULT NULL,
  `guid` char(32) NOT NULL,
  `language` varchar(5) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'new',
  `veredict` varchar(5) NOT NULL,
  `runtime` int(11) NOT NULL DEFAULT '0',
  `memory` int(11) NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `contest_score` double NULL DEFAULT NULL,
  `ip` char(15) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submit_delay` int(11) NOT NULL DEFAULT '0',
  `test` tinyint(1) NOT NULL DEFAULT '0',
  `judged_by` char(32) NULL DEFAULT NULL,
  PRIMARY KEY (`run_id`)
);

CREATE TABLE IF NOT EXISTS `Schools` (
  `school_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`school_id`)
);

CREATE TABLE IF NOT EXISTS `States` (
  `state_id` int(11) NOT NULL,
  `country_id` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`state_id`)
);

CREATE TABLE IF NOT EXISTS `Tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` tinytext,
  PRIMARY KEY (`tag_id`)
);

CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` char(32) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `solved` int(11) NOT NULL DEFAULT '0',
  `submissions` int(11) NOT NULL DEFAULT '0',
  `country_id` char(3) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `scholar_degree` varchar(64) DEFAULT NULL,
  `graduation_date` date DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `last_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
);

CREATE TABLE IF NOT EXISTS `Users_Badges` (
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_problem_id` int(11) NOT NULL COMMENT 'Este campo guarda el ultimo problema que logro que se desbloqueara el badge, just for fun.',
  PRIMARY KEY (`badge_id`,`user_id`)
);

CREATE TABLE IF NOT EXISTS `User_Roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`)
);
