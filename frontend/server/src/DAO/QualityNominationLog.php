<?php

namespace OmegaUp\DAO;

/**
 * QualityNominationLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\QualityNominationLog}.
 *
 * @access public
 */
class QualityNominationLog extends \OmegaUp\DAO\Base\QualityNominationLog {

    /**
     * This function gets contents of QualityNomination table
     *
     * @return list<\OmegaUp\DAO\VO\QualityNominationLog>
     */
    public static function getAllLogsForNomination(int $qualityNominationId): array {
        $sql = '
            SELECT
                *
            FROM
                QualityNomination_Log
            WHERE
                qualitynomination_id = ?;
        ';

        /** @var list<array{qualitynomination_log_id: int, qualitynomination_id: int, to_status: string, from_status: string, time: \OmegaUp\Timestamp, user_id: int, rationale: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$qualityNominationId]
        );

        $logs = [];
        foreach ($rs as $row) {
            $logs[] = new \OmegaUp\DAO\VO\QualityNominationLog($row);
        }
        return $logs;
    }
}
