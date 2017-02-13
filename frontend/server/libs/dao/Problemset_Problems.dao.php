<?php

include('base/Problemset_Problems.dao.base.php');
include('base/Problemset_Problems.vo.base.php');
/** ProblemsetProblems Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetProblems }.
  * @access public
  *
  */
class ProblemsetProblemsDAO extends ProblemsetProblemsDAOBase {
    final public static function getProblems($problemset_id) {
        // Build SQL statement
        $sql = 'SELECT p.title, p.alias, p.time_limit, p.overall_wall_time_limit, '.
               'p.memory_limit, p.languages, pp.points, pp.order ' .
               'FROM Problems p ' .
               'INNER JOIN Problemset_Problems pp ON pp.problem_id = p.problem_id ' .
               'WHERE pp.problemset_id = ? ' .
               'ORDER BY pp.`order` ASC;';
        $val = [$problemset_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    /*
     * Get number of problems in problemset.
     */
    final public static function countProblemsetProblems(Problemsets $problemset) {
        // Build SQL statement
        $sql = 'SELECT COUNT(pp.problem_id) ' .
               'FROM Problemset_Problems pp ' .
               'WHERE pp.problemset_id = ?';
        $val = [$problemset->problemset_id];
        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
     * Get problemset problems including problemset alias, points, and order
     */
    final public static function getProblemsetProblems(Problemsets $problemset) {
        // Build SQL statement
        $sql = 'SELECT p.problem_id, p.alias, pp.points, pp.order ' .
               'FROM Problems p ' .
               'INNER JOIN Problemset_Problems pp ON pp.problem_id = p.problem_id ' .
               'WHERE pp.problemset_id = ? ' .
               'ORDER BY pp.`order` ASC;';
        $val = [$problemset->problemset_id];
        global $conn;
        return $conn->GetAll($sql, $val);
    }

    /*
     *
     * Get relevant problems including problemset alias
     */
    final public static function getRelevantProblems(Problemsets $problemset) {
        // Build SQL statement
        $sql = '
            SELECT
                p.problem_id, p.alias
            FROM
                Problemset_Problems pp
            INNER JOIN
                Problems p ON p.problem_id = pp.problem_id
            WHERE
                pp.problemset_id = ?
            ORDER BY pp.`order` ASC;';
        $val = [$problemset->problemset_id];
        global $conn;
        $rs = $conn->Execute($sql, $val);
        $ar = [];
        foreach ($rs as $foo) {
            $bar =  new Problems($foo);
            array_push($ar, $bar);
        }
        return $ar;
    }
}
