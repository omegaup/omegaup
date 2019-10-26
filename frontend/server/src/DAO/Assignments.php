<?php

namespace OmegaUp\DAO;

/**
 * Assignments Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Assignments}.
 *
 * @access public
 * @return \OmegaUp\DAO\VO\Problemsets|null
 */
class Assignments extends \OmegaUp\DAO\Base\Assignments {
    public static function getProblemset(
        int $courseId,
        string $assignmentAlias
    ): ?\OmegaUp\DAO\VO\Problemsets {
        $sql = 'SELECT
                    p.*
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets p
                ON
                    a.problemset_id = p.problemset_id
                WHERE
                    a.course_id = ?
                    AND a.alias = ?';
        $params = [$courseId, $assignmentAlias];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Problemsets($rs);
    }

    /**
     * @return array<string, int>
     */
    public static function getAssignmentCountsForCourse(int $courseId) {
        $sql = 'SELECT a.assignment_type, COUNT(*) AS count
                FROM Assignments a
                WHERE a.course_id = ?
                GROUP BY a.assignment_type;';
        /** @var list<array{assignment_type: string, count: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
        $counts = [];
        foreach ($rs as $row) {
            $counts[$row['assignment_type']] = intval($row['count']);
        }
        return $counts;
    }

    public static function getAssignmentForProblemset($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }

        return self::getByProblemset($problemset_id);
    }

    final public static function getByProblemset($problemset_id) {
        $sql = 'SELECT * FROM Assignments WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemset_id];

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    final public static function getByAliasAndCourse(
        string $assignmentAlias,
        int $courseId
    ): ?\OmegaUp\DAO\VO\Assignments {
        $sql = 'SELECT
                    *
                FROM
                    Assignments
                WHERE
                    course_id = ?
                AND
                    alias = ?
                LIMIT 1;';

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$courseId, $assignmentAlias]
        );
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    /**
     * @return null|array{scoreboard_url: string, scoreboard_url_admin: string}
     */
    final public static function getByIdWithScoreboardUrls(int $assignmentId) {
        $sql = '
                SELECT
                   ps.scoreboard_url,
                   ps.scoreboard_url_admin
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets ps
                ON
                    ps.problemset_id = a.problemset_id
                WHERE
                    a.assignment_id = ? LIMIT 1;';
        /** @var null|array{scoreboard_url: string, scoreboard_url_admin: string} */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$assignmentId]
        );
        if (empty($rs)) {
            return null;
        }
        return $rs;
    }

    /**
      * Update assignments order.
      *
      * @return Affected Rows
      */
    final public static function updateAssignmentsOrder(
        $assignment_id,
        $order
    ) {
        $sql = 'UPDATE `Assignments` SET `order` = ? WHERE `assignment_id` = ?;';
        $params = [
            $order,
            $assignment_id,
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Get the course assigments sorted by order and start_time
     *
     * @return list<array{problemset_id: int, name: string, description: string, alias: string, assignment_type: string, start_time: int, finish_time: int, order: int, scoreboard_url: string, scoreboard_url_admin: string}>
     */
    final public static function getSortedCourseAssignments(
        int $courseId
    ): array {
        $sql = 'SELECT
                   `a`.`problemset_id`,
                   `a`.`name`,
                   `a`.`description`,
                   `a`.`alias`,
                   `a`.`assignment_type`,
                   UNIX_TIMESTAMP(`a`.`start_time`) AS `start_time`,
                   UNIX_TIMESTAMP(`a`.`finish_time`) AS `finish_time`,
                   `a`.`order`,
                   `ps`.`scoreboard_url`,
                   `ps`.`scoreboard_url_admin`
                FROM
                    `Assignments` `a`
                INNER JOIN
                    `Problemsets` `ps`
                ON
                    `ps`.`problemset_id` = `a`.`problemset_id`
                WHERE
                    course_id = ?
                ORDER BY
                    `order` ASC, `start_time` ASC';

        /** @var list<array{problemset_id: int, name: string, description: string, alias: string, assignment_type: string, start_time: int, finish_time: int, order: int, scoreboard_url: string, scoreboard_url_admin: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = $row;
        }
        return $ar;
    }
}
