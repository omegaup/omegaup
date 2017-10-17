--
-- Arregla un problema de codificación del comentario.
--
ALTER TABLE `Assignments` MODIFY COLUMN `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Define el orden de aparición de los problemas/tareas';

--
-- Arregla un problema de codificación del comentario.
--
ALTER TABLE `Clarifications` MODIFY COLUMN `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.';

--
-- Elimina un índice innecesario.
--
ALTER TABLE `User_Roles` DROP KEY `contest_id`;

--
-- Corrige la codificación de estas tablas.
--
ALTER TABLE `Problemset_User_Request` CONVERT TO CHARACTER SET utf8;
ALTER TABLE `Problemset_User_Request` DEFAULT CHARACTER SET utf8;
ALTER TABLE `Problemset_User_Request_History` CONVERT TO CHARACTER SET utf8;
ALTER TABLE `Problemset_User_Request_History` DEFAULT CHARACTER SET utf8;
