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

    /**
      * Given a course alias, get all of its assignments. Hides any assignments
      * that have not started, if not an admin.
      **/
    public static function getAllAssignments($alias, $isAdmin) {
        global  $conn;

        // Non-admins should not be able to see assignments that have not
        // started.
        $timeCondition = $isAdmin ? '' : 'AND a.start_time <= CURRENT_TIMESTAMP';
        $sql = "
            SELECT
                a.*
            FROM
                Courses c
            INNER JOIN
                Assignments a
            ON
                a.course_id = c.course_id
            WHERE
                c.alias = ? $timeCondition
            ORDER BY
                start_time;";

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
    public static function getStudentsInCourseWithProgressPerAssignment($course_id, $group_id) {
        global  $conn;

        $sql = 'SELECT u.username, u.name, pr.alias as assignment_alias, pr.assignment_score
                FROM Groups g
                INNER JOIN Groups_Users gu
                    ON g.group_id = ? AND g.group_id = gu.group_id
                INNER JOIN Users u
                    ON u.user_id = gu.user_id
                LEFT JOIN (
                    SELECT bpr.alias, bpr.user_id, sum(best_score_of_problem) as assignment_score
                    FROM (
                        SELECT a.alias, a.assignment_id, psp.problem_id, r.user_id, max(r.contest_score) as best_score_of_problem
                        FROM Assignments a
                        INNER JOIN Problemsets ps
                            ON a.problemset_id = ps.problemset_id
                        INNER JOIN Problemset_Problems psp
                            ON psp.problemset_id = ps.problemset_id
                        INNER JOIN Runs r
                            ON r.problem_id = psp.problem_id
                            AND r.problemset_id = a.problemset_id
                        WHERE a.course_id = ?
                        GROUP BY a.assignment_id, psp.problem_id, r.user_id
                    ) bpr
                    GROUP BY bpr.assignment_id, bpr.user_id
                ) pr
                ON pr.user_id = u.user_id';

        $rs = $conn->Execute($sql, [$group_id, $course_id]);
        $progress = [];
        foreach ($rs as $row) {
            $username = $row['username'];
            if (!isset($progress[$username])) {
                $progress[$username] = [
                    'name' => $row['name'],
                    'progress' => [],
                    'username' => $username,
                ];
            }

            if (!is_null($row['assignment_score'])) {
                $progress[$username]['progress'][$row['assignment_alias']] = $row['assignment_score'];
            }
        }
        usort($progress, function ($a, $b) {
            return strcasecmp(
                !empty($a['name']) ? $a['name'] : $a['username'],
                !empty($b['name']) ? $b['name'] : $b['username']
            );
        });
        return $progress;
    }

    /**
     * Returns the score per assignment of a user, as well as the maximum score attainable
     * @param  int $course_id
     * @param  int $user_id
     * @return Array Students data
     */
    public static function getAssignmentsProgress($course_id, $user_id) {
        global  $conn;

        $sql = 'SELECT ps.alias as assignment, IFNULL(pr.total_score, 0) as score, ps.max_score as max_score
                FROM (
                    SELECT a.alias, a.assignment_id, sum(psp.points) as max_score
                    FROM Assignments a
                    INNER JOIN Problemsets ps
                        ON a.problemset_id = ps.problemset_id
                    INNER JOIN Problemset_Problems psp
                        ON psp.problemset_id = ps.problemset_id
                    WHERE a.course_id = ?
                    GROUP BY a.assignment_id
                ) ps
                LEFT JOIN (
                    SELECT bpr.alias, sum(best_score_of_problem) as total_score
                    FROM (
                        SELECT a.alias, a.assignment_id, psp.problem_id, r.user_id, max(r.contest_score) as best_score_of_problem
                        FROM Assignments a
                        INNER JOIN Problemsets ps
                            ON a.problemset_id = ps.problemset_id
                        INNER JOIN Problemset_Problems psp
                            ON psp.problemset_id = ps.problemset_id
                        INNER JOIN Runs r
                            ON r.problem_id = psp.problem_id
                            AND r.problemset_id = a.problemset_id
                        WHERE a.course_id = ? AND r.user_id = ?
                        GROUP BY a.assignment_id, psp.problem_id, r.user_id
                    ) bpr
                    GROUP BY bpr.assignment_id, bpr.user_id
                ) pr
                ON ps.alias = pr.alias';

        $rs = $conn->Execute($sql, [$course_id, $course_id, $user_id]);

        $progress = [];
        foreach ($rs as $row) {
            $assignment = $row['assignment'];
            $progress[$assignment] = [
                'score' => intval($row['score']),
                'max_score' => intval($row['max_score']),
            ];
        }

        return $progress;
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
            GROUP BY
                c.course_id
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

    final public static function getAssignmentByAlias(Courses $course, $assignment_alias) {
        $sql = 'SELECT * FROM Assignments WHERE (alias = ? AND course_id = ?) LIMIT 1;';
        $params = [$assignment_alias, $course->course_id];

        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return new Assignments($row);
    }
}
