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
 */
class Assignments extends \OmegaUp\DAO\Base\Assignments {
    public static function getProblemset($courseId, $assignmentAlias = null) {
        $sql = 'SELECT
                    p.*
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets p
                ON
                    a.problemset_id = p.problemset_id
                WHERE
                    a.course_id = ?';
        $params = [$courseId];
        if (is_null($assignmentAlias)) {
            return \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            );
        }
        $sql .= ' AND a.alias = ?';
        $params[] = $assignmentAlias;

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Problemsets($rs);
    }

    public static function getAssignmentCountsForCourse($course_id) {
        $sql = 'SELECT a.assignment_type, COUNT(*) AS count
                FROM Assignments a
                WHERE a.course_id = ?
                GROUP BY a.assignment_type;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$course_id]
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

    /**
     * @return null|\OmegaUp\DAO\VO\Assignments
     */
    final public static function getByAliasAndCourse(
        string $assignmentAlias,
        int $courseId
    ) {
        $sql = 'SELECT
                    *
                FROM
                    Assignments
                WHERE
                    course_id = ?
                AND
                    alias = ?
                LIMIT 1;';

        /** @var null|array{assignment_id: int, course_id: int, problemset_id: int, acl_id: int, name: string, description: string, alias: string, publish_time_delay: null|int, assignment_type: string, start_time: int, finish_time: int, max_points: float, order: int} */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$courseId, $assignmentAlias]
        );
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    final public static function getByIdWithScoreboardUrls($assignmentId) {
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
     */
    final public static function getSortedCourseAssignments($courseId) {
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
