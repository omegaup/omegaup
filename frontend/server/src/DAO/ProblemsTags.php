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
            $sql .= ' AND pt.source = "voted"';
        }
        $sql .= ';';

        /** @var list<array{name: string, public: bool}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
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
}
