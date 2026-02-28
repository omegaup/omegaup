<?php

namespace OmegaUp\DAO;

/**
 * UserReadmeReportLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserReadmeReportLog}.
 *
 * @access public
 */
class UserReadmeReportLog extends \OmegaUp\DAO\Base\UserReadmeReportLog {
    /**
     * Verificar si un usuario ya reportó un README determinado.
     *
     * @param int $readmeId    El ID del README
     * @param int $reporterUserId El ID del usuario que reporta
     * @return bool True si ya existe un reporte del usuario para ese README
     */
    final public static function hasAlreadyReported(
        int $readmeId,
        int $reporterUserId
    ): bool {
        return self::existsByPK($readmeId, $reporterUserId);
    }
}
