<?php

namespace OmegaUp\DAO;

/**
 * IdentityLoginLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\IdentityLoginLog}.
 *
 * @access public
 */
class IdentityLoginLog extends \OmegaUp\DAO\Base\IdentityLoginLog {
    final public static function getByIdentity($identityId) {
        $sql = 'SELECT
                    *
                FROM
                    Identity_Login_Log
                WHERE
                    identity_id = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$identityId]);

        $identityLoginLogs = [];
        foreach ($rs as $row) {
            array_push($identityLoginLogs, new \OmegaUp\DAO\VO\IdentityLoginLog($row));
        }
        return $identityLoginLogs;
    }
}
