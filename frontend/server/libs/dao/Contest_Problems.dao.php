<?php

require_once('base/Contest_Problems.dao.base.php');
require_once('base/Contest_Problems.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** ContestProblems Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestProblems }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class ContestProblemsDAO extends ContestProblemsDAOBase
{
    /*
	 * Get number of problems in contest.
	 */
    final public static function countContestProblems(Contests $contest) {
        // Build SQL statement
        $sql = 'SELECT COUNT(cp.problem_id) ' .
               'FROM Contest_Problems cp ' .
               'WHERE cp.contest_id = ?';
        $val = array($contest->contest_id);

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Get contest problems including contest alias, points, and order
	 */
    final public static function getContestProblems(Contests $contest) {
        // Build SQL statement
        $sql = 'SELECT p.problem_id, p.alias, cp.points, cp.order ' .
               'FROM Problems p ' .
               'INNER JOIN Contest_Problems cp ON cp.problem_id = p.problem_id ' .
               'WHERE cp.contest_id = ? ' .
               'ORDER BY cp.`order` ASC;';
        $val = array($contest->contest_id);

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    /*
	 *
	 * Get relevant problems including contest alias
	 */
    final public static function getRelevantProblems(Contests $contest)
    {
        // Build SQL statement
        $sql = '
            SELECT
                p.problem_id, p.alias
            FROM
                Contest_Problems cp
            INNER JOIN
                Problems p ON p.problem_id = cp.problem_id
            WHERE
                cp.contest_id = ?
            ORDER BY cp.`order` ASC;';
        $val = array($contest->contest_id);

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = array();
        foreach ($rs as $foo) {
            $bar =  new Problems($foo);
            array_push($ar, $bar);
        }

        return $ar;
    }
}
