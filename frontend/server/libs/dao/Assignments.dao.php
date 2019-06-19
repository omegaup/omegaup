<?php

include('base/Assignments.dao.base.php');
include('base/Assignments.vo.base.php');

/** Assignments Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Assignments }.
  * @access public
  *
  */
class AssignmentsDAO extends AssignmentsDAOBase {
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
        global $conn;
        $params = [$courseId];
        if (is_null($assignmentAlias)) {
            return $conn->GetAll($sql, $params);
        }
        $sql .= ' AND a.alias = ?';
        $params[] = $assignmentAlias;

        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new Problemsets($rs);
    }

    public static function getAssignmentCountsForCourse($course_id) {
        global $conn;

        $sql = 'SELECT a.assignment_type, COUNT(*) AS count
                FROM Assignments a
                WHERE a.course_id = ?
                GROUP BY a.assignment_type;';
        $rs = $conn->GetAll($sql, [$course_id]);
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

        try {
            $assignment = self::getByProblemset($problemset_id);
            if (!is_null($assignment)) {
                return $assignment;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return null;
    }

    final public static function getByProblemset($problemset_id) {
        $sql = 'SELECT * FROM Assignments WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemset_id];

        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return new Assignments($row);
    }

    final public static function getByAliasAndCourse($assignment_alias, $course_id) {
        $sql = 'SELECT
                    *
                FROM
                    Assignments
                WHERE
                    course_id = ?
                AND
                    alias = ?
                LIMIT 1;';

        global $conn;
        $row = $conn->GetRow($sql, [$course_id, $assignment_alias]);
        if (empty($row)) {
            return null;
        }

        return new Assignments($row);
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

        global $conn;
        $rs = $conn->GetRow($sql, [$assignmentId]);
        if (empty($rs)) {
            return null;
        }
        return $rs;
    }

    /**
      * Update assignments order.
      *
      * @return Affected Rows
      * @param Assignments [$Assignments]
      */
    final public static function updateAssignmentsOrder($assignment_id, $order) {
        $sql = 'UPDATE `Assignments` SET `order` = ? WHERE `assignment_id` = ?;';
        $params = [
            $order,
            $assignment_id,
        ];

        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
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

        global $conn;
        $rs = $conn->GetAll($sql, [$courseId]);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = $row;
        }
        return $ar;
    }
}
