<?php

include_once('base/Problem_Of_The_Week.dao.base.php');
include_once('base/Problem_Of_The_Week.vo.base.php');
/** ProblemOfTheWeek Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemOfTheWeek }.
  * @access public
  *
  */
class ProblemOfTheWeekDAO extends ProblemOfTheWeekDAOBase {
    final public static function getByDificulty($difficulty) {
        $sql = 'SELECT
                    *
                FROM
                    Problem_Of_The_Week
                WHERE
                    difficulty = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$difficulty]);

        $problems_of_the_week = [];
        foreach ($rs as $row) {
            array_push($problems_of_the_week, new ProblemOfTheWeek($row));
        }
        return $problems_of_the_week;
    }
}
