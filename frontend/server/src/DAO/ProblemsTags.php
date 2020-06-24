<?php

namespace OmegaUp\DAO;

/**
 * ProblemsTags Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsTags}.
 *
 * @author alanboy
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
                t.name, pt.public
            FROM
                Problems_Tags pt
            INNER JOIN
                Tags t on t.tag_id = pt.tag_id
            WHERE
                pt.problem_id = ?';
        $params = [$problem->problem_id];
        if ($publicOnly) {
            $sql .= ' AND pt.public = 1';
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
                            `problem_id`,
                            `public`
                        )
                    VALUES
                        (?,?,1);';

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
