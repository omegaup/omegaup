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
     * @return list<array{contest_score: float|null, identity_id: int, penalty: int, problem_id: int, score: float, type: null|string}>
     */
    final public static function getProblemsetRunsGroups(
        int $problemsetId,
        string $penaltyType = 'MAX'
    ): array {
        $penaltyQuery = 'MAX(r.penalty) AS penalty';
        if ($penaltyType != 'MAX') {
            $penaltyQuery = 'SUM(r.penalty) AS penalty';
        }
        $sql = "SELECT
                    IFNULL(SUM(mspg.score), 0.0) AS score,
                    SUM(mspg.score) * pp.points AS contest_score,
                    IFNULL(mspg.penalty, 0) AS penalty,
                    mspg.problem_id,
                    mspg.identity_id,
                    mspg.`type`
                FROM
                    Problemset_Problems pp
                INNER JOIN (
                        SELECT
                            MAX(rg.score) AS score,
                            {$penaltyQuery},
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
                    mspg.problem_id = pp.problem_id AND
                    mspg.problemset_id = pp.problemset_id
                WHERE
                    pp.problemset_id = ?
                GROUP BY
                    problem_id,
                    identity_id,
                    `penalty`,
                    `type`;";

        /** @var list<array{contest_score: float|null, identity_id: int, penalty: int, problem_id: int, score: float, type: null|string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId, $problemsetId]
        );

        return $result;
    }
}
