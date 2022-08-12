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
     * @return list<array{contest_score: float, identity_id: int, penalty: int, problem_id: int, score: float, submission_count: int, type: string}>
     */
    final public static function getProblemsetRunsGroups(
        int $problemsetId,
        ?\OmegaUp\Timestamp $scoreboardTimeLimit = null,
        string $penaltyType = 'MAX'
    ): array {
        $penaltyQuery = 'MAX(mspg_name.penalty) AS penalty';
        $mainPenaltyQuery = 'MAX(mspg.penalty) AS penalty';
        $timeQuery = '';
        if ($penaltyType != 'MAX') {
            $penaltyQuery = 'SUM(mspg_name.penalty) AS penalty';
            $mainPenaltyQuery = 'MAX(mspg.penalty) AS penalty';
        }
        $params = [$problemsetId];
        if (!is_null($scoreboardTimeLimit)) {
            $timeQuery = 'AND r.`time` < ?';
            $params[] = $scoreboardTimeLimit->time;
        }
        $sql = "WITH mspg AS (
                    SELECT
                        rg.score,
                        r.penalty,
                        r.run_id,
                        rg.group_name,
                        s.problem_id,
                        s.identity_id,
                        s.`type`,
                        s.problemset_id,
                        r.`time`
                    FROM Runs_Groups rg
                    INNER JOIN Runs r ON rg.run_id = r.run_id
                    INNER JOIN Submissions s ON s.submission_id = r.submission_id
                    WHERE
                        s.problemset_id = ?
                        AND s.`status` = 'ready'
                        AND s.`type` = 'normal'
                        {$timeQuery}
                )
                SELECT
                    IFNULL(ROUND(mspg_identity.score, 2), 0.00) AS score,
                    IFNULL(ROUND(mspg_identity.score * pp.points, 2), 0.00) AS contest_score,
                    IFNULL(mspg_identity.penalty, 0) AS penalty,
                    mspg_identity.problem_id,
                    mspg_identity.identity_id,
                    IFNULL(mspg_identity.`type`, 'normal') AS `type`,
                    mspg_all.submission_count
                FROM
                    Problemset_Problems pp
                INNER JOIN
                    (
                        SELECT
                            COUNT(*) AS submission_count,
                            mspg_count.problem_id,
                            mspg_count.identity_id,
                            mspg_count.problemset_id
                        FROM
                            (
                            SELECT
                                mspg.problem_id,
                                mspg.identity_id,
                                mspg.run_id,
                                mspg.problemset_id
                            FROM
                                mspg
                            GROUP BY
                                problem_id, identity_id, run_id
                            ) AS mspg_count
                        GROUP BY problem_id, identity_id
                    ) AS mspg_all
                ON
                    mspg_all.problemset_id = pp.problemset_id
                    AND mspg_all.problem_id = pp.problem_id
                INNER JOIN
                    (
                        SELECT
                            SUM(mspg_name.score) AS score,
                            {$penaltyQuery},
                            mspg_name.problem_id,
                            mspg_name.identity_id,
                            mspg_name.problemset_id,
                            mspg_name.`type`
                        FROM (
                            SELECT
                                IFNULL(MAX(mspg.score), 0.00) AS score,
                                {$mainPenaltyQuery},
                                mspg.problem_id,
                                mspg.identity_id,
                                IFNULL(mspg.`type`, 'normal') AS `type`,
                                mspg.group_name,
                                mspg.problemset_id
                            FROM
                                mspg
                            GROUP BY
                                problem_id, identity_id, group_name
                        ) AS mspg_name
                        GROUP BY problem_id, identity_id
                    ) AS mspg_identity
                ON
                    mspg_identity.problemset_id = pp.problemset_id
                    AND mspg_identity.problem_id = pp.problem_id
                    AND mspg_all.identity_id = mspg_identity.identity_id;
        ";

        /** @var list<array{contest_score: float, identity_id: int, penalty: int, problem_id: int, score: float, submission_count: int, type: string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );

        return $result;
    }
}
