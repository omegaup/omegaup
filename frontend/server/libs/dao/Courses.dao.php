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
class CoursesDAO extends CoursesDAOBase
{
    public static function findByName($name) {
        global  $conn;

        $sql = "SELECT DISTINCT c.*
                FROM Courses c
                WHERE c.name
                LIKE CONCAT('%', ?, '%') LIMIT 10";

        $resultRows = $conn->Execute($sql, array($name));
        $finalResult = array();

        foreach ($resultRows as $row) {
            array_push($finalResult, new Courses($row));
        }

        return $finalResult;
    }

    public static function findByAlias($alias) {
        global  $conn;

        $sql = 'SELECT c.* FROM Courses c WHERE c.alias  = ?';

        $rs = $conn->GetRow($sql, array($alias));
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

        $rs = $conn->Execute($sql, array($alias));

        $ar = array();
        foreach ($rs as $row) {
            unset($row['assignment_id']);
            unset($row['course_id']);
            unset($row['problemset_id']);
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
        $courses = array();
        foreach ($rs as $row) {
            array_push($courses, new Courses($row));
        }
        return $courses;
    }

    /**
     * Returns a list of students within a course
     * @param  string $courseAlias
     * @param  int $courseId
     * @return Array              Students data
     */
    public static function getStudentsForCourseWithProgress($courseAlias, $courseId) {
        global  $conn;

        $sql = 'SELECT u.username, u.name, u.country_id
                FROM Groups g
                INNER JOIN Groups_Users gu
                ON g.alias = ? AND g.group_id = gu.group_id
                INNER JOIN Users u
                ON u.user_id = gu.user_id
               ';

                /*
                @TODO: Jalar el progreso del estudiante con esta hermosa consulta y pasar $courseId como parametro.
                        Runs necesita soportar Problemsets.
                INNER JOIN (
                    SELECT a.assignment_id, a.name, p.problem_id, p.alias, p.name, max(r.score) as best_score
                    FROM Courses c
                    INNER JOIN Assignments a ON c.course_id = ? AND c.course_id = a.course_id
                    INNER JOIN Problemsets ps ON a.problemset_id = ps.problemset_id
                    INNER JOIN Problemset_Problems psp ON psp.problemset_id = ps.problemset_id
                    INNER JOIN Problems p ON p.problem_id = psp.problemset_id
                    INNER JOIN Runs r ON r.problem_id = p.problem_id AND r.contest_id (!!!)
                    GROUP BY a.assignment_id, a.name, p.problem_id, p.alias, p.name
                ) pr
                ON pr.user_id = u.user_id
                */

        $rs = $conn->Execute($sql, $courseAlias);
        $users = array();
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
        $params = array(
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            $offset,
            $pageSize,
        );

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $courses = array();
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
        $params = array(
            $user_id,
            $offset,
            $pageSize,
        );

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $courses = array();
        foreach ($rs as $row) {
            array_push($courses, new Courses($row));
        }
        return $courses;
    }
}
