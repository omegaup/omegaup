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
     * @return list<array{contest_score: float, identity_id: int, penalty: float, problem_id: int, score: float, submission_count: int, type: string}>
     */
    final public static function getProblemsetRunsGroups(
        int $problemsetId,
        ?\OmegaUp\Timestamp $scoreboardTimeLimit = null,
        string $penaltyType = 'MAX'
    ) {
        $penaltyQuery = 'MAX(mspg.penalty) AS penalty';
        $timeQuery = 'TRUE';
        if ($penaltyType != 'MAX') {
            $penaltyQuery = 'SUM(mspg.penalty) AS penalty';
        }
        $params = [$problemsetId];
        if (!is_null($scoreboardTimeLimit)) {
            $timeQuery = 'mspg.`time` < ?';
            $params[] = $scoreboardTimeLimit;
        }
        $sql = "WITH mspg AS (
                    SELECT
                        rg.score,
                        r.penalty,
                        rg.group_name,
                        s.problem_id,
                        s.identity_id,
                        s.type,
                        r.time,
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
                )
                SELECT
                    IFNULL(ROUND((SUM(mspg_limit.score)), 2), 0.00) AS score,
                    IFNULL(ROUND(SUM(mspg_limit.score) * pp.points, 2), 0.00) AS contest_score,
                    IFNULL(SUM(mspg_limit.penalty), 0) AS penalty,
                    mspg_all.problem_id,
                    mspg_all.identity_id,
                    IFNULL(mspg_all.`type`, 'normal') AS `type`,
                    IFNULL(MAX(mspg_all.submission_count), 0) AS submission_count
                FROM
                    Problemset_Problems pp
                INNER JOIN (
                    SELECT
                        COUNT(*) AS submission_count,
                        MAX(mspg.score) AS score,
                        {$penaltyQuery},
                        mspg.group_name,
                        mspg.problem_id,
                        mspg.identity_id,
                        mspg.type,
                        mspg.problemset_id
                    FROM
                        mspg
                    GROUP BY
                        problem_id,
                        identity_id,
                        group_name
                ) AS mspg_all
                ON
                    pp.problemset_id = mspg_all.problemset_id
                    AND pp.problem_id = mspg_all.problem_id
                INNER JOIN (
                    SELECT
                        MAX(mspg.score) AS score,
                        {$penaltyQuery},
                        mspg.group_name,
                        mspg.problem_id,
                        mspg.identity_id,
                        mspg.type,
                        mspg.problemset_id
                    FROM
                        mspg
                    WHERE
                        {$timeQuery}
                    GROUP BY
                        problem_id,
                        identity_id,
                        group_name
                ) AS mspg_limit
                ON
                    pp.problemset_id = mspg_limit.problemset_id
                    AND pp.problem_id = mspg_limit.problem_id
                    AND mspg_limit.identity_id = mspg_all.identity_id
                    AND mspg_limit.group_name = mspg_all.group_name
                GROUP BY
                    problem_id,
                    identity_id
        ";

        /** @var list<array{contest_score: float, identity_id: int, penalty: float, problem_id: int, score: float, submission_count: int, type: string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );

        return $result;
    }
}
