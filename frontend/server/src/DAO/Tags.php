<?php

namespace OmegaUp\DAO;

/**
 * Tags Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Tags}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Tags extends \OmegaUp\DAO\Base\Tags {
    final public static function getByName(string $name): ?\OmegaUp\DAO\VO\Tags {
        $sql = 'SELECT * FROM Tags WHERE name = ? LIMIT 1;';

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
        $sql = "SELECT * FROM Tags WHERE name LIKE CONCAT('%', ?, '%') LIMIT 100";
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
     * @return list<string>
     */
    public static function getFrequentsByLevel(
        int $level
    ) {
        $sql = '
            SELECT
                t.name 
            FROM
                Problems_Tags pt
            INNER JOIN 
                Tags t ON t.tag_id = pt.tag_id
            WHERE 
                pt.problem_id 
            IN (
                SELECT 
                    problem_id
                FROM 
                    Problems_Tags 
                WHERE 
                    tag_id = ?
            ) AND 
                name LIKE "problemTag%"
            GROUP BY 
                t.name
            ORDER BY 
                COUNT(pt.problem_id)
            ';

        $results = [];
        /** @var array{name: string} row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [ $level ]
            ) as $row
        ) {
            $results[] = $row['name'];
        }
    
        return $results;
    }
}
