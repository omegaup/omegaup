-- Rebuilds the permissions catalog from scratch (issue #10174).
-- The previous rows had corrupted characters (mojibake) and are not used by
-- any code. Only the Admin (1) and Support (6) roles are related for now.

DELETE FROM `Roles_Permissions`;
DELETE FROM `Permissions`;

-- The type of resource that each permission protects. New types can be added
-- to the enum in the future.
ALTER TABLE `Permissions`
  ADD COLUMN `resource_type` ENUM('entrypoint', 'endpoint', 'capability') NOT NULL
  COMMENT 'Tipo de recurso que protege el permiso: página, API o capacidad.'
  AFTER `name`;

INSERT INTO `Permissions` (`permission_id`, `name`, `resource_type`, `description`) VALUES
-- Entrypoints (pages)
(1, '/admin/operations/', 'entrypoint', 'Abre el Operations Dashboard.'),
(2, '/admin/support/', 'entrypoint', 'Abre la página de soporte.'),
(3, '/admin/user/:username/', 'entrypoint', 'Abre el detalle administrativo de un usuario.'),
(4, '/admin/carousel/', 'entrypoint', 'Abre la administración del carrusel.'),
(5, '/admin/settings/', 'entrypoint', 'Abre la configuración global.'),
(6, '/admin/crons/', 'entrypoint', 'Abre la página de trabajos programados.'),
-- Endpoints (APIs)
(7, '/api/admin/setMaintenanceMode/', 'endpoint', 'Activa o cambia el mensaje de estado de la plataforma.'),
(8, '/api/admin/getMaintenanceMode/', 'endpoint', 'Consulta el mensaje de estado de la plataforma.'),
(9, '/api/admin/updateSystemSettings/', 'endpoint', 'Actualiza la configuración del sistema.'),
(10, '/api/reset/generateToken/', 'endpoint', 'Genera un token para restablecer una contraseña.'),
(11, '/api/contest/setRecommended/', 'endpoint', 'Marca o desmarca un concurso como recomendado.'),
(12, '/api/user/statusVerified/', 'endpoint', 'Consulta si un correo está verificado.'),
(13, '/api/user/extraInformation/', 'endpoint', 'Consulta información extra de un usuario.'),
(14, '/api/user/addRole/', 'endpoint', 'Asigna un rol a un usuario.'),
(15, '/api/user/removeRole/', 'endpoint', 'Quita un rol a un usuario.'),
(16, '/api/user/addExperiment/', 'endpoint', 'Activa un experimento a un usuario.'),
(17, '/api/user/removeExperiment/', 'endpoint', 'Desactiva un experimento a un usuario.'),
(18, '/api/carouselItems/create/', 'endpoint', 'Crea elementos del carrusel.'),
(19, '/api/carouselItems/delete/', 'endpoint', 'Elimina elementos del carrusel.'),
(20, '/api/carouselItems/update/', 'endpoint', 'Actualiza elementos del carrusel.'),
(21, '/api/carouselItems/list/', 'endpoint', 'Lista los elementos del carrusel.'),
(22, '/api/role/list/', 'endpoint', 'Lista los roles disponibles con sus conteos de asignaciones.'),
(23, '/api/role/details/', 'endpoint', 'Consulta un rol y los permisos que incluye.'),
(24, '/api/role/assignments/', 'endpoint', 'Lista los usuarios o grupos que tienen un rol asignado.'),
(25, '/api/admin/platformReportStats/', 'endpoint', 'Consulta las estadísticas de la plataforma.'),
(26, '/api/admin/getSystemSettings/', 'endpoint', 'Consulta la configuración del sistema.'),
(27, '/api/admin/getCrons/', 'endpoint', 'Lista los trabajos programados.'),
(28, '/api/admin/getCronRun/', 'endpoint', 'Consulta una ejecución de un trabajo programado.'),
(29, '/api/admin/rerunCron/', 'endpoint', 'Vuelve a ejecutar un trabajo programado.'),
(30, '/api/admin/setCronJobEnabled/', 'endpoint', 'Activa o desactiva un trabajo programado.'),
(31, '/api/grader/status/', 'endpoint', 'Consulta el estado del grader.'),
(32, '/api/user/mailingListBackfill/', 'endpoint', 'Reenvía usuarios a la lista de correo.'),
(33, '/api/run/list/', 'endpoint', 'Lista los envíos de todo el sistema.'),
-- Capabilities
(34, 'contest.set_recommended', 'capability', 'Marcar un concurso como recomendado al crearlo o editarlo.'),
(35, 'contest.duration_limit', 'capability', 'Crear o editar concursos con una duración máxima ampliada.'),
(36, 'contest.privileged_access', 'capability', 'Abrir y consultar cualquier concurso sin ser su administrador.'),
(37, 'contest.global_role', 'capability', 'Consultar el rol propio en cualquier concurso.'),
(38, 'contest.skip_admin_requirement', 'capability', 'Omitir el requisito de ser administrador del concurso al validar filtros.'),
(39, 'contest.view_all', 'capability', 'Ver todos los concursos.'),
(40, 'contest.remove_problem', 'capability', 'Quitar de un concurso un problema que ya tiene envíos.'),
(41, 'course.remove_problem', 'capability', 'Quitar de un curso un problema que ya tiene envíos.'),
(42, 'problem.view_all', 'capability', 'Ver todos los problemas.'),
(43, 'user.verify_email', 'capability', 'Verificar el correo de otro usuario.'),
(44, 'user.update_main_email', 'capability', 'Cambiar el correo principal de otro usuario.'),
(45, 'user.request_deletion', 'capability', 'Solicitar el borrado de la cuenta de otro usuario.'),
(46, 'user.confirm_deletion', 'capability', 'Confirmar el borrado de la cuenta de otro usuario.'),
(47, 'user.view_hidden_data', 'capability', 'Ver datos ocultos de otro usuario: perfil, estadísticas y certificados.'),
(48, 'reset.skip_wait', 'capability', 'Solicitar un restablecimiento de contraseña sin esperar el tiempo mínimo.'),
(49, 'run.skip_wait', 'capability', 'Enviar soluciones sin esperar el tiempo mínimo entre envíos.'),
(50, 'ai_editorial.skip_quota', 'capability', 'Generar editoriales sin límite de cuota.');

-- Support (6): permissions 1, 2, 3, 4, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 34, 36, 37, 38, 43, 44, 48.
INSERT INTO `Roles_Permissions` (`role_id`, `permission_id`)
SELECT 6, `permission_id` FROM `Permissions`
WHERE `permission_id` IN (1, 2, 3, 4, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 34, 36, 37, 38, 43, 44, 48)
ORDER BY `permission_id`;

-- Admin (1): permissions 4, 5, 6, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 35, 36, 37, 38, 39, 40, 41, 42, 45, 46, 47, 49, 50.
INSERT INTO `Roles_Permissions` (`role_id`, `permission_id`)
SELECT 1, `permission_id` FROM `Permissions`
WHERE `permission_id` IN (4, 5, 6, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 35, 36, 37, 38, 39, 40, 41, 42, 45, 46, 47, 49, 50)
ORDER BY `permission_id`;