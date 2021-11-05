-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `languages` set('c','c11-gcc','c11-clang','cpp','cpp11','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py','py2','py3','rb','pl','cs','pas','kp','kj','cat','hs','lua') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar en un curso';
