<?php

namespace OmegaUp\DAO;

/**
 * ProblemRecommendations Data Access Object (DAO).
 *
 * Esta clase abstrae el acceso a la tabla `Problem_Recommendations`, que
 * contiene las mejores recomendaciones por problema resuelto sincronizadas
 * desde el último modelo de recomendación publicado.
 *
 * @access public
 */
class ProblemRecommendations extends \OmegaUp\DAO\Base\ProblemRecommendations {
    /**
     * Returns the top recommended problems for an identity.
     *
     * The recommendations are derived from the identity's most recently
     * solved problems: each of them is looked up in
     * `Problem_Recommendations`, problems the identity already solved are
     * filtered out, and the remaining candidates are ranked by score. When
     * the same problem is recommended from more than one solved problem,
     * only the highest-scored occurrence is kept.
     *
     * @return list<array{alias: string, difficulty: float|null, quality: float|null, score: float, solved_problem_alias: string, solved_problem_title: string, title: string}>
     */
    final public static function getRecommendedProblems(
        int $identityId,
        int $numRecentSolvedProblems,
        int $limit
    ): array {
        $sql = '
            SELECT
                p.alias,
                p.title,
                p.difficulty,
                p.quality,
                pr.score,
                sp.alias AS solved_problem_alias,
                sp.title AS solved_problem_title
            FROM
                (
                    SELECT
                        s.problem_id,
                        MIN(s.time) AS first_solved
                    FROM
                        Submissions s
                    WHERE
                        s.identity_id = ? AND
                        s.verdict = "AC" AND
                        s.type = "normal" AND
                        s.status = "ready" AND
                        s.problemset_id IS NULL
                    GROUP BY
                        s.problem_id
                    ORDER BY
                        first_solved DESC
                    LIMIT ?
                ) recent
            INNER JOIN
                Problem_Recommendations pr ON pr.solved_problem_id = recent.problem_id
            INNER JOIN
                Problems p ON p.problem_id = pr.recommended_problem_id
            INNER JOIN
                Problems sp ON sp.problem_id = recent.problem_id
            WHERE
                p.visibility >= ? AND
                NOT EXISTS (
                    SELECT
                        1
                    FROM
                        Submissions solved
                    WHERE
                        solved.identity_id = ? AND
                        solved.problem_id = pr.recommended_problem_id AND
                        solved.verdict = "AC" AND
                        solved.type = "normal" AND
                        solved.status = "ready"
                )
            ORDER BY
                pr.score DESC,
                p.problem_id ASC;
        ';
        $params = [
            $identityId,
            $numRecentSolvedProblems,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            $identityId,
        ];

        $recommendations = [];
        $seenAliases = [];
        /** @var array{alias: string, difficulty: float|null, quality: float|null, score: float, solved_problem_alias: string, solved_problem_title: string, title: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $row
        ) {
            if (isset($seenAliases[$row['alias']])) {
                continue;
            }
            $seenAliases[$row['alias']] = true;
            $recommendations[] = $row;
            if (count($recommendations) >= $limit) {
                break;
            }
        }
        return $recommendations;
    }
}
