<?php

include("base/Courses.dao.base.php");
include("base/Courses.vo.base.php");
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

        foreach ($resultRows as $row)
            array_push($finalResult, new Courses($row));

        return $finalResult;
    }
}
