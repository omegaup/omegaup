<?php

namespace OmegaUp\DAO;

/**
 * ContestProblemChangeLog Data Access Object (DAO).
 *
 * @access public
 */
class ContestProblemChangeLog extends \OmegaUp\DAO\Base\ContestProblemChangeLog {
    /**
     * Returns all problem change log entries for a given contest,
     * ordered by timestamp ascending.
     *
     * Uses the composite index `idx_contest_timestamp` for an
     * efficient index seek + ordered scan.
     *
    * @return list<array{change_type: string, changed_by: string, problem_alias: string, timestamp: \OmegaUp\Timestamp}>
     */
    public static function getByContestId(int $contestId): array {
        $sql = '
            SELECT
                cl.change_type,
                p.alias AS problem_alias,
                i.username AS changed_by,
                cl.timestamp
            FROM
                Contest_Problem_Change_Log cl
            INNER JOIN
                Problems p ON p.problem_id = cl.problem_id
            INNER JOIN
                Users u ON u.user_id = cl.user_id
            INNER JOIN
                Identities i ON i.user_id = u.user_id AND i.identity_id = u.main_identity_id
            WHERE
                cl.contest_id = ?
            ORDER BY
                cl.timestamp ASC;
        ';

        /** @var list<array{change_type: string, changed_by: string, problem_alias: string, timestamp: \OmegaUp\Timestamp}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$contestId]
        );
    }
}
