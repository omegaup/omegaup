ALTER TABLE `Problemsets`
	MODIFY COLUMN `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas';

ALTER TABLE `Contests`
  MODIFY COLUMN `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar en un concurso';

ALTER TABLE `Problems`
  MODIFY COLUMN `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') NOT NULL DEFAULT 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11,lua';

ALTER TABLE `Runs`
  MODIFY COLUMN `language` enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') NOT NULL;
