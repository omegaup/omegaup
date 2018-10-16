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
     * Gets the users that solved the most problems during the provided
     * time period.
     *
     * @global type $conn
     * @param string (date) $startTime
     * @param string (date) $endTime
     * @return null|Users
     */
    public static function calculateCoderOfTheMonth($startTime, $endTime) {
        $sql = "
          SELECT DISTINCT
            i.user_id,
            i.username,
            i.country_id,
            COUNT(ps.problem_id) ProblemsSolved,
            SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)) score,
            (SELECT urc.classname FROM
                User_Rank_Cutoffs urc
            WHERE
                urc.score <= (
                        SELECT
                            ur.score
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = i.user_id
                    )
            ORDER BY
                urc.percentile ASC
            LIMIT
                1) classname
          FROM
            (
              SELECT DISTINCT
                r.identity_id, r.problem_id
              FROM
                Runs r
              WHERE
                r.verdict = 'AC' AND r.type= 'normal' AND
                r.time >= ? AND
                r.time <= ?
            ) AS up
          INNER JOIN
            Problems ps ON ps.problem_id = up.problem_id and ps.visibility >= 1
          INNER JOIN
            Identities i ON i.identity_id = up.identity_id
          LEFT JOIN
            (
              SELECT
                user_id,
                MAX(time) latest_time,
                rank
              FROM
                Coder_Of_The_Month
              WHERE
                rank = 1
              GROUP BY
                user_id,
                rank
            ) AS cm on i.user_id = cm.user_id
          WHERE
            cm.user_id IS NULL
            OR DATE_ADD(cm.latest_time, INTERVAL 1 YEAR) < ?
          GROUP BY
            up.identity_id
          ORDER BY
            score DESC
          LIMIT 100
        ";

        $val = [$startTime, $endTime, $endTime, $endTime];

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
        return $conn->getAll($sql, [$date]);
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

    final public static function getByTimeAndSelected($time) {
        $sql = 'SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ?
                AND
                    `selected_by` IS NOT NULL;';

        global $conn;
        $rs = $conn->Execute($sql, [$time]);

        $coders = [];
        foreach ($rs as $row) {
            array_push($coders, new CoderOfTheMonth($row));
        }
        return $coders;
    }

    final public static function getByTime($time) {
        $sql = 'SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$time]);

        $coders = [];
        foreach ($rs as $row) {
            array_push($coders, new CoderOfTheMonth($row));
        }
        return $coders;
    }

    /**
     * One coder is selected, when parameters are not received
     * means that coder was selected by default
     *
     * @param $username
     * @param identity_id
     * @return Affected_Rows
     */
    public static function selectCoder($username = null, $identity_id = null) {
        $curdate = date('Y-m-d', Time::get());
        if (is_null($username) && is_null($identity_id)) {
            $identity_clause = '(SELECT identity_id FROM Identities WHERE username = \'admin\' OR username = \'admintest\')';
            $clause = '`rank` = 1';
            $params = [$curdate];
        } else {
            $identity_clause = '?';
            $clause = '`user_id` = (SELECT `user_id` FROM `Identities` WHERE `username` = ? LIMIT 1)';
            $params = [$identity_id, $curdate, $username];
        }
        $sql = '
          UPDATE
            `Coder_Of_The_Month`
          SET
            `selected_by` = ' . $identity_clause . '
          WHERE
            `time` = LAST_DAY(? - INTERVAL 1 MONTH) + INTERVAL 1 DAY
            AND ' . $clause . ';';

        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();

    public static function calculateCoderOfLastMonth($currentDate) {
        $date = new DateTime($currentDate);
        $firstDayOfLastMonth = $date->modify('first day of last month');
        $startTime = $firstDayOfLastMonth->format('Y-m-d');
        $firstDayOfCurrentMonth = $date->modify('first day of next month');
        $endTime = $firstDayOfCurrentMonth->format('Y-m-d');
        return self::calculateCoderOfTheMonth($startTime, $endTime);
    }

    public static function calculateCoderOfCurrentMonth($currentDate) {
        $date = new DateTime($currentDate);
        $firstDayOfCurrentMonth = $date->modify('first day of this month');
        $startTime = $firstDayOfCurrentMonth->format('Y-m-d');
        $firstDayOfNextMonth = $date->modify('first day of next month');
        $endTime = $firstDayOfNextMonth->format('Y-m-d');
        return self::calculateCoderOfTheMonth($startTime, $endTime);
    }
}
