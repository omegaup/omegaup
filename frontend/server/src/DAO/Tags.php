<?php

namespace OmegaUp\DAO;

/**
 * Tags Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Tags}.
 * @access public
 * @package docs
 *
 * @psalm-type TagWithProblemCount=array { name: string, problemCount: int }
 */
class Tags extends \OmegaUp\DAO\Base\Tags {
    final public static function getByName(string $name): ?\OmegaUp\DAO\VO\Tags {
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Tags::FIELD_NAMES,
            'Tags'
        ) . ' FROM Tags WHERE name = ? LIMIT 1;';

        /** @var array{name: string, public: bool, tag_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Tags($row);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Tags>
     */
    public static function findByName(string $name): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Tags::FIELD_NAMES,
            'Tags'
        );
        $sql = "SELECT {$fields} FROM Tags WHERE name LIKE CONCAT('%', ?, '%') LIMIT 100";
        $args = [$name];

        $result = [];
        /** @var array{name: string, tag_id: int} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $args
            ) as $row
        ) {
            $result[] = new \OmegaUp\DAO\VO\Tags($row);
        }
        return $result;
    }

    /**
     * Finds all public tags beginning with a certain parameter
     *
     * @return list<string>
     */
    public static function findPublicTagsByPrefix(
        string $prefix
    ) {
        $sql = "
            SELECT
                name
            FROM
                Tags
            WHERE
                name LIKE CONCAT(?, '%') AND
                public = true;";

        $results = [];
        /** @var array{name: string} row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [ $prefix ]
            ) as $row
        ) {
            $results[] = $row['name'];
        }

        return $results;
    }

    /**
     * @return list<TagWithProblemCount>
     */
    public static function getPublicQualityTagsByLevel(
        string $problemLevel
    ) {
        $sql = '
            SELECT
                t.name AS name,
                COUNT(t.name) AS problemCount
            FROM
                Problems_Tags pt
            INNER JOIN
                Tags t ON t.tag_id = pt.tag_id
            INNER JOIN
	            Problems p ON p.problem_id = pt.problem_id
            WHERE
                pt.problem_id IN (
                    SELECT
                        problem_id
                    FROM
                        Problems_Tags
                    WHERE
                        tag_id = (
                            SELECT
                                tag_id
                            FROM
                                Tags
                            WHERE name = ?
                        )
                ) AND
                name LIKE "problemTag%"
            AND
                p.quality_seal = 1
            GROUP BY
                t.name
            ORDER BY
                COUNT(pt.problem_id)
            DESC
            ';

        /** @var list<array{name: string, problemCount: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                $problemLevel
            ]
        );
    }

    /**
     * @return list<TagWithProblemCount>
     */
    public static function getFrequentQualityTagsByLevel(
        string $problemLevel,
        int $rows
    ) {
        $sql = '
            SELECT
                t.name AS name,
                COUNT(t.name) AS problemCount
            FROM
                Problems_Tags pt
            INNER JOIN
                Tags t ON t.tag_id = pt.tag_id
            INNER JOIN
	            Problems p ON p.problem_id = pt.problem_id
            WHERE
                pt.problem_id
            IN (
                SELECT
                    problem_id
                FROM
                    Problems_Tags
                WHERE
                    tag_id = (
                        SELECT
                            tag_id
                        FROM
                            Tags
                        WHERE name = ? )
            ) AND
                name LIKE "problemTag%"
            AND
                p.quality_seal = 1
            GROUP BY
                t.name
            ORDER BY
                COUNT(pt.problem_id)
            DESC
            LIMIT ?
            ';

        /** @var list<array{name: string, problemCount: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                $problemLevel,
                $rows
            ]
        );
    }
}
