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
        $sql = 'SELECT p.problem_id, p.title, p.alias, p.time_limit, p.overall_wall_time_limit, '.
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
     * Get problemset problems including problemset alias, points, and order
     */
    final public static function getByProblemset($problemset_id) {
        // Build SQL statement
        $sql = 'SELECT
                    *
                FROM
                    Problemset_Problems
                WHERE
                    problemset_id = ?
                ORDER BY
                    `order` ASC;';

        global $conn;
        $rs = $conn->Execute($sql, [$problemset_id]);

        $problemsetProblems = [];
        foreach ($rs as $row) {
            array_push($problemsetProblems, new ProblemsetProblems($row));
        }
        return $problemsetProblems;
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

    /**
     * Copy problemset problems from one problem set to the new problemset
     * @param Number, Number
     * @return void
     */
    public static function copyProblemset($new_problemset, $old_problemset) {
        $sql = '
            INSERT INTO
                Problemset_Problems (problemset_id, problem_id, points, `order`)
            SELECT
                ?, problem_id, points, `order`
            FROM
                Problemset_Problems
            WHERE
                Problemset_Problems.problemset_id = ?;
        ';
        global $conn;
        $params = [$new_problemset, $old_problemset];
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
      * Update problemset order.
      *
      * @return Affected Rows
      * @param ProblemsetProblems [$Problemset_Problems]
      */
    final public static function updateProblemsOrder(ProblemsetProblems $Problemset_Problems) {
        $sql = 'UPDATE `Problemset_Problems` SET `order` = ? WHERE `problemset_id` = ? AND `problem_id` = ?;';
        $params = [
            $Problemset_Problems->order,
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    final public static function getProblemIdsByProblemset($problemset_id) {
        $sql = 'SELECT
                    pp.problem_id
                FROM
                    Problemset_Problems pp
                WHERE
                    pp.problemset_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$problemset_id]);

        $problemIds = [];
        foreach ($rs as $problem) {
            array_push($problemIds, $problem['problem_id']);
        }
        return $problemIds;
    }

    final public static function getProblemsByProblemset($problemset_id) {
        $sql = 'SELECT
                    p.title,
                    p.alias,
                    p.validator,
                    p.time_limit,
                    p.overall_wall_time_limit,
                    p.extra_wall_time,
                    p.memory_limit,
                    p.visits,
                    p.submissions,
                    p.accepted,
                    p.difficulty,
                    p.order,
                    p.languages,
                    pp.points
                FROM
                    Problems p
                INNER JOIN
                    Problemset_Problems pp
                ON
                    p.problem_id = pp.problem_id
                WHERE
                  pp.problemset_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$problemset_id]);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, $row);
        }
        return $problems;
    }

    /*
     * Get max points posible for contest
     */
    final public static function getMaxPointsByProblemset($problemset_id) {
        // Build SQL statement
        $sql = 'SELECT
                    SUM(points) as max_points
                FROM
                    Problemset_Problems
                WHERE
                    problemset_id = ?;';

        global $conn;
        return $conn->GetOne($sql, [$problemset_id]);
    }
}
