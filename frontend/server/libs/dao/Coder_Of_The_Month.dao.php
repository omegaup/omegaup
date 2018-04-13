<?php

require_once('base/Coder_Of_The_Month.dao.base.php');
require_once('base/Coder_Of_The_Month.vo.base.php');
/** Page-level DocBlock .
 *
 * @author alanboy
 * @package docs
 *
 */

/** CoderOfTheMonth Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link CoderOfTheMonth }.
 * @author alanboy
 * @access public
 * @package docs
 *
 */
class CoderOfTheMonthDAO extends CoderOfTheMonthDAOBase {
    /**
     * Gets the user that solved more problems during last month
     *
     * @global type $conn
     * @param string (date) $firstDay
     * @return null|Users
     */
    public static function calculateCoderOfTheMonth($firstDay) {
        $endTime = $firstDay;
        $startTime = null;
        $date = explode('-', $firstDay);
        $year = $date[0];
        $month = $date[1];

        $lastMonth = $month - 1;

        if ($lastMonth === 0) {
            // First month of the year, we need to check into last month of last year.
            $lastYear = $year - 1;
            $startTime = date($lastYear . '-12-01');
        } else {
            $startTime = date($year . '-' . $lastMonth . '-01');
        }

        $sql = "
			SELECT DISTINCT
				username, name, up.user_id, COUNT(ps.problem_id) ProblemsSolved, SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)) score
			FROM
				(
					SELECT DISTINCT
						r.user_id, r.problem_id
					FROM
						Runs r
					WHERE
						r.verdict = 'AC' AND r.test = 0 AND
						r.time >= ? AND
						r.time <= ?
				) AS up
			INNER JOIN
				Problems ps ON ps.problem_id = up.problem_id and ps.visibility >= 1
			INNER JOIN
				Users u ON u.user_id = up.user_id
			LEFT JOIN
				Coder_Of_The_Month cm on u.user_id = cm.user_id
			WHERE
				cm.user_id IS NULL OR (DATE_ADD(cm.time, INTERVAL 1 YEAR) < ? AND cm.rank = 1)
			GROUP BY
				user_id
			ORDER BY
                score DESC
			LIMIT 100
		";

        $val = [$startTime, $endTime, $endTime];

        global $conn;
        $results = $conn->getAll($sql, $val);
        if (count($results) == 0) {
            return null;
        }

        return $results;
    }

    /**
     * Get all first coders of the month
     *
     * @static
     * @return Array
     */
    final public static function getCodersOfTheMonth() {
        $sql = '
          SELECT
            cm.time, u.username, u.country_id, e.email
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          LEFT JOIN
            Emails e ON e.user_id = u.user_id
          WHERE
            cm.rank = 1
          ORDER BY
            cm.time DESC
        ';

        global $conn;
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = $row;
        }
        return $allData;
    }

    /**
     * Get all coder of the months based on month
     * 
     * @params string (date) $firstDay
     * @return Users
     */
    final public static function getMonthlyList($firstDay) {
        $date = date('Y-m-01', strtotime($firstDay));
        $sql = '
          SELECT
            cm.time, u.username, u.country_id, e.email, u.user_id
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          LEFT JOIN
            Emails e ON e.user_id = u.user_id
          WHERE
            cm.time = ?
          ORDER BY
            cm.time DESC
          LIMIT 100
        ';
        global $conn;
        $results = $conn->getAll($sql, [$date]);
        if (count($results) == 0) {
            return null;
        }

        return $results;
    }

    /**
     * Get true whether user is the last Coder of the month
     *
     * @static
     * @return Array
     */
    final public static function isLastCoderOfTheMonth($username) {
        $sql = '
          SELECT
            u.username
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          WHERE
            cm.rank = 1
          ORDER BY
            cm.time DESC
          LIMIT 1
        ';

        global $conn;
        $rs = $conn->GetRow($sql, []);
        if (count($rs) == 0) {
            return false;
        }
        return $username == $rs['username'];
    }
}
