<?php

namespace OmegaUp\DAO;

/**
 * CronRuns Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CronRuns}.
 */
class CronRuns extends \OmegaUp\DAO\Base\CronRuns {
    /**
     * Returns the most recent runs across all cron jobs.
     *
     * @return list<\OmegaUp\DAO\VO\CronRuns>
     */
    public static function getRecent(int $limit = 50): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronRuns::FIELD_NAMES,
            'Cron_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Runs
                ORDER BY started_at DESC
                LIMIT ?;";
        /** @var list<array{created_at: \OmegaUp\Timestamp, duration_seconds: float|null, error_text: null|string, finished_at: \OmegaUp\Timestamp|null, hostname: null|string, name: string, phases: null|string, rows_affected: int|null, run_id: int, started_at: \OmegaUp\Timestamp, status: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$limit]
        );
        $runs = [];
        foreach ($rs as $row) {
            $runs[] = new \OmegaUp\DAO\VO\CronRuns($row);
        }
        return $runs;
    }

    /**
     * Returns the most recent runs for a single cron job.
     *
     * @return list<\OmegaUp\DAO\VO\CronRuns>
     */
    public static function getRecentByName(
        string $name,
        int $limit = 50
    ): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronRuns::FIELD_NAMES,
            'Cron_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Runs
                WHERE name = ?
                ORDER BY started_at DESC
                LIMIT ?;";
        /** @var list<array{created_at: \OmegaUp\Timestamp, duration_seconds: float|null, error_text: null|string, finished_at: \OmegaUp\Timestamp|null, hostname: null|string, name: string, phases: null|string, rows_affected: int|null, run_id: int, started_at: \OmegaUp\Timestamp, status: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$name, $limit]
        );
        $runs = [];
        foreach ($rs as $row) {
            $runs[] = new \OmegaUp\DAO\VO\CronRuns($row);
        }
        return $runs;
    }

    /**
     * Returns the latest run for a single cron job, regardless of status.
     */
    public static function getLatestByName(
        string $name
    ): ?\OmegaUp\DAO\VO\CronRuns {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronRuns::FIELD_NAMES,
            'Cron_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Runs
                WHERE name = ?
                ORDER BY started_at DESC
                LIMIT 1;";
        /** @var array{created_at: \OmegaUp\Timestamp, duration_seconds: float|null, error_text: null|string, finished_at: \OmegaUp\Timestamp|null, hostname: null|string, name: string, phases: null|string, rows_affected: int|null, run_id: int, started_at: \OmegaUp\Timestamp, status: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CronRuns($row);
    }

    /**
     * Returns the latest successful run for a single cron job. Used to report
     * how fresh the data produced by the job is.
     */
    public static function getLatestSuccessfulByName(
        string $name
    ): ?\OmegaUp\DAO\VO\CronRuns {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronRuns::FIELD_NAMES,
            'Cron_Runs'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Runs
                WHERE name = ? AND status = 'success'
                ORDER BY started_at DESC
                LIMIT 1;";
        /** @var array{created_at: \OmegaUp\Timestamp, duration_seconds: float|null, error_text: null|string, finished_at: \OmegaUp\Timestamp|null, hostname: null|string, name: string, phases: null|string, rows_affected: int|null, run_id: int, started_at: \OmegaUp\Timestamp, status: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CronRuns($row);
    }
}
