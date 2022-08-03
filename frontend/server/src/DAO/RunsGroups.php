<?php

namespace OmegaUp\DAO;

/**
 * RunsGroups Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\RunsGroups}.
 *
 * @access public
 */
class RunsGroups extends \OmegaUp\DAO\Base\RunsGroups {
    /**
     * @return list<array{contest_score: float|null, guid: string, identity_id: int, penalty: int, problem_id: int, score: float, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string}>
     */
    final public static function getProblemsetRunsGroups(
        int $problemsetId
    ): array {
        $sql = "SELECT
                    IFNULL(SUM(mspg.score), 0.0) AS score,
                    SUM(mspg.score) * pp.points AS contest_score,
                    0.0 AS penalty,
                    mspg.problem_id,
                    mspg.identity_id,
                    mspg.`type`,
                    NOW() AS `time`,
                    0 AS `submit_delay`,
                    'x' AS `guid`
                FROM
                    Problemset_Problems pp
                INNER JOIN (
                        SELECT
                            MAX(rg.score) score,
                            rg.group_name,
                            s.problem_id,
                            s.identity_id,
                            s.type,
                            s.problemset_id
                        FROM
                            Runs_Groups rg
                        INNER JOIN
                            Runs r
                        ON
                            rg.run_id = r.run_id
                        INNER JOIN
                            Submissions s
                        ON
                            s.submission_id = r.submission_id
                        WHERE
                            s.problemset_id = ? AND
                            s.status = 'ready' AND
                            s.type = 'normal'
                        GROUP BY
                            problem_id,
                            identity_id,
                            group_name
                ) AS mspg
                ON
                    pp.problem_id = mspg.problem_id AND
                    mspg.problemset_id = pp.problemset_id
                WHERE
                    pp.problemset_id = ?
                GROUP BY
                    problem_id,
                    identity_id,
                    `penalty`,
                    `type`,
                    `time`,
                    `submit_delay`,
                    `guid`;";

        /** @var list<array{contest_score: float|null, guid: string, identity_id: int, penalty: int, problem_id: int, score: float, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId, $problemsetId]
        );

        return $result;
    }
}
