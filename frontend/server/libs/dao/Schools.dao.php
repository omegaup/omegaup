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
        foreach ($conn->Execute($sql, $args) as $row) {
            $result[] = new Schools($row);
        }
        return $result;
    }

    /**
     * Returns rank of schools by # of distinct users with at least one AC and # of distinct problems solved.
     *
     * @param  DateTime $startDate
     * @param  int   $offset
     * @param  int   $rowcount
     * @return array
     */
    public static function getRankByUsersAndProblemsWithAC(DateTime $startDate, $offset, $rowcount) {
        global  $conn;

        $sql = '
        SELECT
          s.name,
          COUNT(DISTINCT u.user_id) as distinct_users,
          COUNT(DISTINCT p.problem_id) AS distinct_problems
        FROM
          Users u
        JOIN
          Runs r ON u.user_id = r.user_id
        JOIN
          Schools s ON u.school_id = s.school_id
        JOIN
          Problems p ON p.problem_id = r.problem_id
        WHERE
          u.school_id IS NOT NULL AND r.Verdict = "AC" AND p.public = "1" AND time > "?"
        GROUP BY
          u.school_id,
          s.name
        ORDER BY
          distinct_users DESC,
          distinct_problems DESC
        LIMIT ?, ?;
      ';

        $args = [$startDate->format('Y-m-d'), $offset, $rowcount];

        $result = [];
        foreach ($conn->Execute($sql, $args) as $row) {
            $result[] = [
            'name' => $row['name'],
            'distinct_users' => $row['distinct_users'],
            'distinct_problems' => $row['distinct_problems']
            ];
        }

        return $result;
    }
}
