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
                . ' where c.alias = ? and a.id_course = c.course_id'
                . ' order by start_time;';

        $rs = $conn->Execute($sql, array($alias));

        $ar = array();
        foreach ($rs as $row) {
            unset($row['assignement_id']);
            unset($row['id_course']);
            unset($row['id_problemset']);
            $row['start_time'] =  strtotime($row['start_time']);
            $row['finish_time'] = strtotime($row['finish_time']);
            array_push($ar, $row);
        }

        return $ar;
    }

    public static function getCoursesForStudent($user) {
        global  $conn;
        // TODO(pablo): El link entre curso y grupo deberia ser por id y no alias.
        $sql = 'SELECT c.*
                FROM Courses c
                INNER JOIN (
                    SELECT alias
                    FROM Groups_Users gu
                    INNER JOIN Groups g ON g.group_id = gu.group_id
                    WHERE gu.user_id = ?
                ) gg
                ON c.alias = gg.alias;
               ';
        $rs = $conn->Execute($sql, $user);
        $courses = array();
        foreach ($rs as $row) {
            array_push($courses, $row);
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

        $sql = 'SELECT u.user_id, u.username, u.name, u.country_id
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
                    INNER JOIN Assignments a ON c.course_id = ? AND c.course_id = a.id_course
                    INNER JOIN Problemsets ps ON a.id_problemset = ps.problemset_id
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
            array_push($users, $row);
        }
        return $users;
    }
}
