<?php

namespace OmegaUp\DAO;

/**
 * ProblemsTags Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsTags}.
 * @access public
 * @package docs
 */
class ProblemsTags extends \OmegaUp\DAO\Base\ProblemsTags {
    /**
     * @return list<array{name: string, public: bool}>
     */
    public static function getProblemTags(
        \OmegaUp\DAO\VO\Problems $problem,
        bool $publicOnly,
        bool $includeVoted = false
    ): array {
        $sql = '
            SELECT
                t.name, t.public
            FROM
                Problems_Tags pt
            INNER JOIN
                Tags t on t.tag_id = pt.tag_id
            WHERE
                pt.problem_id = ?';
        $params = [$problem->problem_id];
        if ($publicOnly) {
            $sql .= ' AND t.public = 1';
        }
        if (!$includeVoted) {
            $sql .= ' AND pt.source != "voted"';
        }
        $sql .= ';';

        /** @var list<array{name: string, public: bool}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Returns the list of tags of a problem. That list will contain
     * either public or private tags.
     *
     * @return list<string>
     */
    public static function getTagsForProblem(
        \OmegaUp\DAO\VO\Problems $problem,
        bool $public
    ): array {
        $sql = "
            SELECT
                `t`.`name`
            FROM
                `Problems_Tags` AS `pt`
            INNER JOIN
                `Tags` `t` on `t`.`tag_id` = `pt`.`tag_id`
            WHERE
                `pt`.`problem_id` = ? AND
                `t`.`name` NOT LIKE 'problemRestricted%' AND
                `t`.`name` NOT LIKE 'problemLevel%' AND
                `t`.`public` = ?;";

        $results = [];
        /** @var array{name: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [ $problem->problem_id, $public ]
            ) as $row
        ) {
            $results[] = $row['name'];
        }
        return $results;
    }

    /**
     * Get tag distribution for problems solved by an identity.
     * Returns tags sorted by count descending.
     * If there are more than $maxTags, the remaining tags are grouped under 'Others'.
     *
     * @return list<array{name: string, count: int}>
     */
    public static function getTagsDistributionForSolvedProblems(
        int $identityId,
        int $maxTags = 10
    ): array {
        $sql = "
            SELECT
                t.name,
                COUNT(DISTINCT p.problem_id) AS count
            FROM
                Tags t
            INNER JOIN
                Problems_Tags pt ON pt.tag_id = t.tag_id
            INNER JOIN
                Problems p ON p.problem_id = pt.problem_id
            INNER JOIN
                Submissions s ON s.problem_id = p.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                Problems_Forfeited pf ON pf.problem_id = p.problem_id
                    AND pf.user_id = i.user_id
                    AND i.user_id IS NOT NULL
            LEFT JOIN
                ACLs a ON a.acl_id = p.acl_id
                    AND a.owner_id = i.user_id
                    AND i.user_id IS NOT NULL
            WHERE
                s.verdict = 'AC' AND s.type = 'normal' AND s.identity_id = ?
                AND t.public = 1
                AND t.name NOT LIKE 'problemRestricted%'
                AND t.name NOT LIKE 'problemLevel%'
                AND pf.problem_id IS NULL
                AND a.acl_id IS NULL
            GROUP BY
                t.name
            ORDER BY
                count DESC, t.name ASC;
        ";

        /** @var list<array{count: int, name: string}> */
        $allTags = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId]
        );

        // If there are fewer or equal tags than the limit, return as is
        if (count($allTags) <= $maxTags) {
            return $allTags;
        }

        // Take the top N tags and group the rest under 'Others'
        $topTags = array_slice($allTags, 0, $maxTags);
        $remainingTags = array_slice($allTags, $maxTags);

        $othersCount = 0;
        foreach ($remainingTags as $tag) {
            $othersCount += $tag['count'];
        }

        if ($othersCount > 0) {
            $topTags[] = [
                'name' => 'Others',
                'count' => $othersCount,
            ];
        }

        return $topTags;
    }

    public static function clearRestrictedTags(\OmegaUp\DAO\VO\Problems $problem): void {
        $placeholders = join(
            ',',
            array_fill(
                0,
                count(
                    \OmegaUp\Controllers\Problem::RESTRICTED_TAG_NAMES
                ),
                '?'
            )
        );
        $params = array_merge(
            \OmegaUp\Controllers\Problem::RESTRICTED_TAG_NAMES,
            [$problem->problem_id]
        );
        $sql = "
            DELETE FROM
                `Problems_Tags`
            WHERE
                tag_id IN (
                    SELECT
                        tag_id
                    FROM
                        Tags
                    WHERE
                        name IN ($placeholders)
                ) AND problem_id = ?;";
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
    }

    public static function getProblemLevel(
        \OmegaUp\DAO\VO\Problems $problem
    ): ?string {
        $sql = "
            SELECT
                `t`.`name`
            FROM
                `Problems_Tags` AS `pt`
            INNER JOIN
                `Tags` AS `t`
            ON
                `t`.`tag_id` = `pt`.`tag_id`
            WHERE
                `t`.`name` LIKE 'problemLevel%' AND
                `pt`.`problem_id` = ?;";

        /** @var string|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [ $problem->problem_id ],
        );
    }

    public static function updateProblemLevel(
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Tags $tag
    ): void {
        try {
            \OmegaUp\DAO\DAO::transBegin();
            // Delete old tag
            $sql = "
                DELETE
                    `pt`
                FROM
                    `Problems_Tags` AS `pt`
                INNER JOIN
                    `Tags` AS `t`
                ON
                    `t`.`tag_id` = `pt`.`tag_id`
                WHERE
                    `pt`.`problem_id` = ? AND
                    `t`.`name` LIKE 'problemLevel%';";
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                $sql,
                [ $problem->problem_id ],
            );

            if ($tag) {
                $sql = '
                    INSERT INTO
                        `Problems_Tags` (
                            `tag_id`,
                            `problem_id`
                        )
                    VALUES
                        (?,?);';

                \OmegaUp\MySQLConnection::getInstance()->Execute(
                    $sql,
                    [ $tag->tag_id, $problem->problem_id ],
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
    }
}
