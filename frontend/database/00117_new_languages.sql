-- Submissions
ALTER TABLE `Submissions`
  MODIFY COLUMN `language` enum('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua') NOT NULL;

-- Users
ALTER TABLE `Users`
  MODIFY COLUMN `preferred_language` enum('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua') DEFAULT NULL COMMENT 'El lenguaje de programación de preferencia de este usuario';

-- Contests
ALTER TABLE `Contests`
  MODIFY COLUMN `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar en un concurso';

-- Problems
ALTER TABLE `Problems`
  MODIFY COLUMN `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua') NOT NULL DEFAULT 'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cs,hs,java,lua,pas,py2,py3,rb';

-- Problemsets
ALTER TABLE `Problemsets`
  MODIFY COLUMN `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas';
