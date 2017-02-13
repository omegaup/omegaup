<?php

include('base/Courses.dao.base.php');
include('base/Courses.vo.base.php');
/** Courses Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Courses }.
  * @access public
  *
  */
class CoursesDAO extends CoursesDAOBase {
    public static function findByName($name) {
        global  $conn;

        $sql = "SELECT DISTINCT c.*
                FROM Courses c
                WHERE c.name
                LIKE CONCAT('%', ?, '%') LIMIT 10";

        $resultRows = $conn->Execute($sql, [$name]);
        $finalResult = [];

        foreach ($resultRows as $row) {
            array_push($finalResult, new Courses($row));
        }

        return $finalResult;
    }

    public static function findByAlias($alias) {
        global  $conn;

        $sql = 'SELECT c.* FROM Courses c WHERE c.alias  = ?';

        $rs = $conn->GetRow($sql, [$alias]);
        if (count($rs) == 0) {
            return null;
        }

        return new Courses($rs);
    }

    /**
      * Given a course alias, get all of its assignments
      *
      **/
    public static function getAllAssignments($alias) {
        global  $conn;

        $sql = 'select a.* from Courses c, Assignments a '
                . ' where c.alias = ? and a.course_id = c.course_id'
                . ' order by start_time;';

        $rs = $conn->Execute($sql, [$alias]);

        $ar = [];
        foreach ($rs as $row) {
            unset($row['assignment_id']);
            unset($row['course_id']);
            $row['start_time'] =  strtotime($row['start_time']);
            $row['finish_time'] = strtotime($row['finish_time']);
            array_push($ar, $row);
        }

        return $ar;
    }

    public static function getCoursesForStudent($user) {
        global  $conn;
        $sql = 'SELECT c.*
                FROM Courses c
                INNER JOIN (
                    SELECT g.group_id
                    FROM Groups_Users gu
                    INNER JOIN Groups g ON g.group_id = gu.group_id
                    WHERE gu.user_id = ?
                ) gg
                ON c.group_id = gg.group_id;
               ';
        $rs = $conn->Execute($sql, $user);
        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new Courses($row));
        }
        return $courses;
    }

    /**
     * Returns a list of students within a course
     * @param  int $course_id
     * @param  int $group_id
     * @return Array Students data
     */
    public static function getStudentsForCourseWithProgress($course_id, $group_id) {
        global  $conn;

        $sql = 'SELECT u.username, u.name, pr.assignment_id, pr.problem_id, pr.best_score
                FROM Groups g
                INNER JOIN Groups_Users gu
                ON g.group_id = ? AND g.group_id = gu.group_id
                INNER JOIN Users u
                ON u.user_id = gu.user_id
                LEFT JOIN (
                    SELECT a.assignment_id, a.name, psp.problem_id, r.user_id, max(r.score) as best_score
                    FROM Assignments a
                    INNER JOIN Problemsets ps
                        ON a.problemset_id = ps.problemset_id
                    INNER JOIN Problemset_Problems psp
                        ON psp.problemset_id = ps.problemset_id
                    INNER JOIN Runs r
                        ON r.problem_id = psp.problem_id
                        AND r.problemset_id = a.problemset_id
                    WHERE a.course_id = ?
                    GROUP BY a.assignment_id, a.name, psp.problem_id, r.user_id
                ) pr
                ON pr.user_id = u.user_id';

        $rs = $conn->Execute($sql, [$group_id, $course_id]);
        $users = [];
        foreach ($rs as $row) {
            /* @TODO: Remover count_homeworks_done, count_assignments_done y sacarlos del query anterior */
            $row['count_homeworks_done'] = 1;
            $row['count_tests_done'] = 1;
            array_push($users, $row);
        }
        return $users;
    }

    /**
     * Returns all courses that a user can manage.
     */
    final public static function getAllCoursesAdminedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*
            FROM
                Courses AS c
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = c.acl_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = c.acl_id
            LEFT JOIN
                Groups_Users gu ON gu.group_id = gr.group_id
            WHERE
                a.owner_id = ? OR
                (ur.role_id = ? AND ur.user_id = ?) OR
                (gr.role_id = ? AND gu.user_id = ?)
            ORDER BY
                c.course_id DESC
            LIMIT
                ?, ?';
        $params = [
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            $offset,
            $pageSize,
        ];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new Courses($row));
        }
        return $courses;
    }

    /**
     * Returns all courses owned by a user.
     */
    final public static function getAllCoursesOwnedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*
            FROM
                Courses AS c
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            WHERE
                a.owner_id = ?
            ORDER BY
                c.course_id DESC
            LIMIT
                ?, ?';
        $params = [
            $user_id,
            $offset,
            $pageSize,
        ];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new Courses($row));
        }
        return $courses;
    }

    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Courses WHERE (alias = ?) LIMIT 1;';
        $params = [$alias];

        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return new Courses($row);
    }
}
