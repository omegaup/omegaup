<?php

require_once('base/Schools.dao.base.php');
require_once('base/Schools.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Schools Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Schools }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class SchoolsDAO extends SchoolsDAOBase {
    /**
     * Finds schools that cotains 'name'
     *
     * @global type $conn
     * @param string $name
     * @return array Schools
     */
    public static function findByName($name) {
        global  $conn;

        $sql = '
            SELECT
                s.*
            FROM
                Schools s
            WHERE
                s.name LIKE CONCAT(\'%\', ?, \'%\')
            LIMIT 10';
        $args = [$name];

        $result = [];
        foreach ($conn->GetAll($sql, $args) as $row) {
            $result[] = new Schools($row);
        }
        return $result;
    }

    /**
     * Returns rank of schools by # of distinct users with at least one AC and # of distinct problems solved.
     *
     * @param  int $startTime
     * @param  int $finishTime
     * @param  int $offset
     * @param  int $rowcount
     * @return array
     */
    public static function getRankByUsersAndProblemsWithAC(
        int $startDate,
        int $finishDate,
        int $offset,
        int $rowcount
    ) : array {
        global  $conn;

        $sql = '
            SELECT
              s.name,
              s.country_id,
              COUNT(DISTINCT i.identity_id) as distinct_users,
              COUNT(DISTINCT p.problem_id) AS distinct_problems
            FROM
              Identities i
            INNER JOIN
              Submissions su ON su.identity_id = i.identity_id
            INNER JOIN
              Runs r ON r.run_id = su.current_run_id
            INNER JOIN
              Schools s ON i.school_id = s.school_id
            INNER JOIN
              Problems p ON p.problem_id = su.problem_id
            WHERE
              r.verdict = "AC" AND p.visibility >= 1 AND
              su.time BETWEEN CAST(FROM_UNIXTIME(?) AS DATETIME) AND CAST(FROM_UNIXTIME(?) AS DATETIME)
            GROUP BY
              s.school_id
            ORDER BY
              distinct_users DESC,
              distinct_problems DESC
            LIMIT ?, ?;';

        $args = [$startDate, $finishDate, $offset, $rowcount];

        $result = [];
        foreach ($conn->GetAll($sql, $args) as $row) {
            $result[] = [
                'name' => $row['name'],
                'country_id' => $row['country_id'],
                'distinct_users' => $row['distinct_users'],
                'distinct_problems' => $row['distinct_problems'],
            ];
        }

        return $result;
    }
}
