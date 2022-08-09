<?php

namespace OmegaUp\DAO;

/**
 * ProblemsetAccessLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetAccessLog}.
 *
 * @access public
 */
class ProblemsetAccessLog extends \OmegaUp\DAO\Base\ProblemsetAccessLog {
    /**
     * @return list<\OmegaUp\DAO\VO\ProblemsetAccessLog>
     */
    final public static function getByProblemsetIdentityId(
        int $problemsetId,
        int $identityId
    ): array {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\ProblemsetAccessLog::FIELD_NAMES,
            'Problemset_Access_Log'
        ) . '
                FROM
                    Problemset_Access_Log
                WHERE
                    problemset_id = ?
                AND
                    identity_id = ?;';

        /** @var list<array{identity_id: int, ip: int, problemset_id: int, time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId, $identityId]
        );

        $problemsetAccessLog = [];
        foreach ($rs as $row) {
            $problemsetAccessLog[] = new \OmegaUp\DAO\VO\ProblemsetAccessLog(
                $row
            );
        }
        return $problemsetAccessLog;
    }

    final public static function removeAccessLogFromProblemset(
        int $problemsetId
    ): int {
        $sql = '
            DELETE FROM
                `Problemset_Access_Log`
            WHERE
                `problemset_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problemsetId]);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
