-- Rebuilds the permissions catalog from scratch (issue #10174).
-- The previous data had corrupted characters and is not used by the code.
-- Only the Admin (1) and Support (6) roles are linked to permissions.

DELETE FROM `Roles_Permissions`;
DELETE FROM `Permissions`;

INSERT INTO `Permissions` (`permission_id`, `name`, `description`) VALUES
-- Support only (1-19)
(1, '/api/admin/setMaintenanceMode/', 'Exige soporte: activa o cambia el mensaje de estado de la plataforma.'),
(2, '/api/admin/getMaintenanceMode/', 'Exige soporte: consulta el mensaje de estado de la plataforma.'),
(3, '/api/admin/updateSystemSettings/', 'Exige soporte: actualiza la configuración del sistema.'),
(4, '/admin/operations/', 'Exige soporte: abre el Operations Dashboard.'),
(5, '/admin/support/', 'Exige soporte: abre la página de soporte.'),
(6, '/admin/user/:username/', 'Exige soporte: abre el detalle administrativo de un usuario.'),
(7, '/api/reset/generateToken/', 'Exige soporte: genera un token para restablecer una contraseña.'),
(8, '/api/reset/create/', 'Extra de soporte: solicita el restablecimiento sin esperar el tiempo mínimo.'),
(9, '/api/contest/setRecommended/', 'Exige soporte: marca o desmarca un concurso como recomendado.'),
(10, '/api/user/statusVerified/', 'Exige soporte: consulta si un correo está verificado.'),
(11, '/api/user/extraInformation/', 'Exige soporte: consulta información extra de un usuario.'),
(12, '/api/user/addRole/', 'Exige soporte: asigna un rol a un usuario.'),
(13, '/api/user/removeRole/', 'Exige soporte: quita un rol a un usuario.'),
(14, '/api/user/addExperiment/', 'Exige soporte: activa un experimento a un usuario.'),
(15, '/api/user/removeExperiment/', 'Exige soporte: desactiva un experimento a un usuario.'),
(16, '/api/user/verifyEmail/', 'Extra de soporte: verifica el correo de otro usuario.'),
(17, '/api/user/updateMainEmail/', 'Extra de soporte: cambia el correo principal de otro usuario.'),
(18, '/contest/new/', 'Extra de soporte: puede marcar el concurso como recomendado al crearlo.'),
(19, '/contest/:alias/edit/', 'Extra de soporte: puede marcar el concurso como recomendado al editarlo.'),
-- Support and admin (20-33)
(20, '/api/carouselItems/create/', 'Soporte y admin: crea elementos del carrusel.'),
(21, '/api/carouselItems/delete/', 'Soporte y admin: elimina elementos del carrusel.'),
(22, '/api/carouselItems/update/', 'Soporte y admin: actualiza elementos del carrusel.'),
(23, '/api/carouselItems/list/', 'Soporte y admin: lista los elementos del carrusel.'),
(24, '/admin/carousel/', 'Soporte y admin: abre la administración del carrusel.'),
(25, '/api/contest/open/', 'Extra de soporte y admin: abre un concurso sin ser su admin.'),
(26, '/api/contest/details/', 'Extra de soporte y admin: ve los detalles de un concurso sin ser su admin.'),
(27, '/api/contest/adminDetails/', 'Extra de soporte y admin: ve los detalles de administración de un concurso.'),
(28, '/api/contest/activityReport/', 'Extra de soporte y admin: ve el reporte de actividad de un concurso.'),
(29, '/api/contest/scoreboardEvents/', 'Extra de soporte y admin: ve los eventos del scoreboard de un concurso.'),
(30, '/api/contest/role/', 'Extra de soporte y admin: consulta su rol en cualquier concurso.'),
(31, '/api/user/validateFilter/', 'Extra de soporte y admin: valida filtros de concurso sin ser su admin.'),
(32, '/api/contest/create/', 'Extra: soporte fija recomendado; admin tiene duración máxima ampliada.'),
(33, '/api/contest/update/', 'Extra: soporte fija recomendado; admin tiene duración máxima ampliada.'),
-- System admin only (34-57)
(34, '/api/admin/platformReportStats/', 'Exige admin: consulta las estadísticas de la plataforma.'),
(35, '/api/admin/getSystemSettings/', 'Exige admin: consulta la configuración del sistema.'),
(36, '/admin/settings/', 'Exige admin: abre la configuración global.'),
(37, '/api/admin/getCrons/', 'Exige admin: lista los trabajos programados.'),
(38, '/api/admin/getCronRun/', 'Exige admin: consulta una ejecución de un trabajo programado.'),
(39, '/api/admin/rerunCron/', 'Exige admin: vuelve a ejecutar un trabajo programado.'),
(40, '/admin/crons/', 'Exige admin: abre la página de trabajos programados.'),
(41, '/api/grader/status/', 'Exige admin: consulta el estado del grader.'),
(42, '/api/user/mailingListBackfill/', 'Exige admin: reenvía usuarios a la lista de correo.'),
(43, '/api/run/list/', 'Exige admin: lista envíos de todo el sistema.'),
(44, '/api/contest/list/', 'Extra de admin: ve todos los concursos.'),
(45, '/api/contest/adminList/', 'Extra de admin: lista todos los concursos con scoreboard.'),
(46, '/api/contest/removeProblem/', 'Extra de admin: quita un problema que ya tiene envíos.'),
(47, '/api/course/removeProblem/', 'Extra de admin: quita un problema de un curso con envíos.'),
(48, '/api/problem/list/', 'Extra de admin: ve todos los problemas.'),
(49, '/api/problem/adminList/', 'Extra de admin: lista todos los problemas.'),
(50, '/api/user/deleteRequest/', 'Extra de admin: solicita borrar la cuenta de otro usuario.'),
(51, '/api/user/deleteConfirm/', 'Extra de admin: confirma el borrado de la cuenta de otro usuario.'),
(52, '/api/user/profile/', 'Extra de admin: ve datos ocultos del perfil de otro usuario.'),
(53, '/api/user/stats/', 'Extra de admin: ve estadísticas ocultas de otro usuario.'),
(54, '/api/user/profileStatistics/', 'Extra de admin: ve estadísticas del perfil de otro usuario.'),
(55, '/api/certificate/getUserCertificates/', 'Extra de admin: ve los certificados de otro usuario.'),
(56, '/api/run/create/', 'Extra de admin: envía sin esperar el tiempo mínimo entre envíos.'),
(57, '/api/aiEditorial/generate/', 'Extra de admin: genera editoriales sin límite de uso.');

-- Support (6): permissions 1 to 33.
INSERT INTO `Roles_Permissions` (`role_id`, `permission_id`)
SELECT 6, `permission_id` FROM `Permissions` WHERE `permission_id` BETWEEN 1 AND 33 ORDER BY `permission_id`;

-- Admin (1): permissions 20 to 57 (shared with support, plus its own).
INSERT INTO `Roles_Permissions` (`role_id`, `permission_id`)
SELECT 1, `permission_id` FROM `Permissions` WHERE `permission_id` BETWEEN 20 AND 57 ORDER BY `permission_id`;