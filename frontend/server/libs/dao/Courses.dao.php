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
}
