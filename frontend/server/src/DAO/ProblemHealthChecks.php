<?php

namespace OmegaUp\DAO;

/**
 * ProblemHealthChecks Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemHealthChecks}.
 */
class ProblemHealthChecks extends \OmegaUp\DAO\Base\ProblemHealthChecks {
    /**
     * Returns the open findings, worst and newest first, with their problem.
     *
     * Newest rather than oldest because the caller takes a limited number, and
     * oldest first would pin the list to findings nobody has acted on.
     *
     * @return list<array{alias: string, check_type: string, detail: null|string, first_detected_at: \OmegaUp\Timestamp, problem_id: int, severity: string, title: string}>
     */
    public static function getOpenFindings(int $limit = 100): array {
        $sql = 'SELECT
                    phc.`problem_id`,
                    phc.`check_type`,
                    phc.`severity`,
                    phc.`detail`,
                    phc.`first_detected_at`,
                    p.`alias`,
                    p.`title`
                FROM Problem_Health_Checks phc
                INNER JOIN Problems p ON p.problem_id = phc.problem_id
                WHERE phc.`resolved_at` IS NULL
                ORDER BY
                    FIELD(phc.`severity`, ?, ?),
                    phc.`first_detected_at` DESC
                LIMIT ?;';
        /** @var list<array{alias: string, check_type: string, detail: null|string, first_detected_at: \OmegaUp\Timestamp, problem_id: int, severity: string, title: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                \OmegaUp\ProblemHealthSeverity::Error->value,
                \OmegaUp\ProblemHealthSeverity::Warning->value,
                $limit,
            ]
        );
    }
}
