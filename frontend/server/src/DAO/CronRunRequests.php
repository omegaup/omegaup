<?php

namespace OmegaUp\DAO;

/**
 * CronRunRequests Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CronRunRequests}.
 */
class CronRunRequests extends \OmegaUp\DAO\Base\CronRunRequests {
    /**
     * Returns the request for a job that is still waiting to be processed or
     * currently being processed, if any. Used to avoid queueing duplicate
     * rerun requests for the same job.
     */
    public static function getActiveByName(
        string $name
    ): ?\OmegaUp\DAO\VO\CronRunRequests {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronRunRequests::FIELD_NAMES,
            'Cron_Run_Requests'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Run_Requests
                WHERE name = ? AND status IN ('pending', 'picked')
                ORDER BY requested_at DESC
                LIMIT 1;";
        /** @var array{error_text: null|string, finished_at: \OmegaUp\Timestamp|null, name: string, picked_at: \OmegaUp\Timestamp|null, request_id: int, requested_at: \OmegaUp\Timestamp, requested_by: int|null, run_id: int|null, status: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CronRunRequests($row);
    }
}
