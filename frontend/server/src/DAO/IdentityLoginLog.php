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
    /**
     * @return list<\OmegaUp\DAO\VO\IdentityLoginLog>
     */
    final public static function getByIdentity(
        int $identityId
    ): array {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\IdentityLoginLog::FIELD_NAMES,
            'Identity_Login_Log'
        ) . '
                FROM
                    Identity_Login_Log
                WHERE
                    identity_id = ?;';

        /** @var list<array{identity_id: int, ip: int, time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId]
        );

        $identityLoginLogs = [];
        foreach ($rs as $row) {
            $identityLoginLogs[] = new \OmegaUp\DAO\VO\IdentityLoginLog(
                $row
            );
        }
        return $identityLoginLogs;
    }
}
