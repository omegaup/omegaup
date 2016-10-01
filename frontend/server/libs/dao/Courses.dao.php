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

        $sql = "select DISTINCT c.* from Courses c where c.name LIKE  CONCAT('%', ?, '%') LIMIT 10";

        $resultRows = $conn->Execute($sql, array($name));
        $finalResult = array();

        foreach ($resultRows as $row) {
            array_push($finalResult, new Courses($row));
        }

        return $finalResult;
    }

    public static function findByAlias($alias) {
        global  $conn;

        $sql = "select c.* from Courses c where c.alias  = ?";

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

        $sql = "select a.* from Courses c, Assignments a "
                . " where c.alias = ? and a.id_course = c.course_id"
                . " order by start_time;";

        $rs = $conn->Execute($sql, array($alias));

        $ar = array();
        foreach ($rs as $row) {
            unset($row['assignement_id']);
            unset($row['id_course']);
            unset($row['id_problemset']);
            array_push($ar, $row);
        }

        return $ar;
    }
}
