<?php

namespace OmegaUp\DAO;

/**
 * CronJobs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CronJobs}.
 */
class CronJobs extends \OmegaUp\DAO\Base\CronJobs {
    /**
     * Returns a cron job from the registry by its name.
     */
    public static function getByName(
        string $name
    ): ?\OmegaUp\DAO\VO\CronJobs {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronJobs::FIELD_NAMES,
            'Cron_Jobs'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Jobs
                WHERE name = ?
                LIMIT 1;";
        /** @var array{created_at: \OmegaUp\Timestamp, description: null|string, enabled: bool, job_id: int, name: string, schedule: null|string, updated_at: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CronJobs($row);
    }

    /**
     * Returns every registered cron job ordered by name.
     *
     * @return list<\OmegaUp\DAO\VO\CronJobs>
     */
    public static function getAllOrdered(): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CronJobs::FIELD_NAMES,
            'Cron_Jobs'
        );
        $sql = "SELECT {$fields}
                FROM Cron_Jobs
                ORDER BY name ASC;";
        /** @var list<array{created_at: \OmegaUp\Timestamp, description: null|string, enabled: bool, job_id: int, name: string, schedule: null|string, updated_at: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
        $jobs = [];
        foreach ($rs as $row) {
            $jobs[] = new \OmegaUp\DAO\VO\CronJobs($row);
        }
        return $jobs;
    }
}
