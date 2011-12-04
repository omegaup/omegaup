/* Clean up Roles_Permissions */
DELETE FROM `Roles_Permissions`;

/* Cleanup Roles table */
DELETE from `Roles`;

/* Clean up* Permissions */
DELETE from `Permissions`;

/* Install Roles */
INSERT INTO `Roles` (`role_id`,`name`,`description`) VALUES (1,'Admin','Administrador del sistema. Acceso universal');
INSERT INTO `Roles` (`role_id`,`name`,`description`) VALUES (2,'Contestant','Concursante común, usuario principal de omegaup');
INSERT INTO `Roles` (`role_id`,`name`,`description`) VALUES (3,'Judge','Juez de concursos. Encargados de crear problemas y responder preguntas sobre los problemas');
INSERT INTO `Roles` (`role_id`,`name`,`description`) VALUES (4,'Visitor','Usuario no loggeado a omegaup');



/* Inserts all permissions */

INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (1,'/contests/','Lista (por default de los últimos 10 concursos) que el usuario "puede ver"');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (2,'/contests/new','Si el usuario tiene permisos de juez o admin, crea un nuevo concurso, sin problemas asociados.');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (3,'/contests/show','Si el usuario puede verlos, muestra los detalles del concurso :id');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (4,'/contests/ranking','Si el usuario puede verlo, Muestra el ranking completo del contest ID.');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (5,'/contests/:id/problem/:id','Si el usuario puede verlo, muestra el contenido del problema y referencias a las soluciones que ha e');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (6,'/contests/:id/problem/new','Si el usuario tiene permisos de juez o admin, crea un nuevo problema asociado al concurso :id');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (7,'/runs/new','Si el usuario tiene permiso, El usuario envía una solución. En los parámetros se envía el ID del pro');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (8,'/runs/show','Si el usuario tiene permiso, puede ver su solución y el estado de la misma (pending… grading… done… ');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (9,'/runs/problem','Si el usuario tiene permiso, regresa las referencias a las últimas 5 soluciones a un problema en par');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (10,'/clarifications/new','Si el usuario tiene permiso, envía una clarificación sobre un problema en particular. En los parámet');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (11,'/clarifications/problem','Regresa TODAS las clarificaciones de un problema en particular, a las cuales el usuario puede ver (e');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (12,'/clarifications/show','Si el usuario tiene permiso, la API regresa la pregunta y la respuesta asociada con la clarifiación ');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (13,'/noticitacions/contest','Si el usuario tiene permiso, la API regresa la lista de notificaciones que los jueces han mandado so');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (14,'/notifications/new','Si el usuario tiene permisos de juez o admin, crea una nueva notificación.');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (15,'/arena','Regresa el HTML de la arena. Si el usuario no esta loggeado, muestra el login. Si está loggeado, por');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (16,'/arena/:contest_alias','Si el usuario puede verlo, regresa el HTML asociado a un concurso, /contests/:id');
INSERT INTO `Permissions` (`permission_id`,`name`,`description`) VALUES (17,'/arena/:contest_alias/scoreboard','Si el usuario puede verlo, regresa el HTML asociado a un concurso, arreglando de forma bonita los co');



/* Install Roles_Permissions*/

/* Admin permissions */
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,1);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,2);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,3);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,4);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,5);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,6);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,7);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,8);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,9);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,10);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,11);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,12);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,13);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,14);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,15);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,16);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (1,17);

/* Contestant Permissions */

INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,1);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,3);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,4);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,5);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,7);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,8);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,10);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,11);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,12);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,13);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,15);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,16);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (2,17);

/* Judge permissions */

INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,1);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,3);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,4);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,5);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,6);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,7);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,8);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,9);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,10);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,11);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,12);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,13);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,14);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,15);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,16);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (3,17);

/* Visitor permisions */
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (4,1);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (4,4);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (4,15);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (4,16);
INSERT INTO `Roles_Permissions` (`role_id`,`permission_id`) VALUES (4,17);
