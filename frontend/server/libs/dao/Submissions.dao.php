<?php

include_once('base/Submissions.dao.base.php');
include_once('base/Submissions.vo.base.php');

/** Submissions Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Submissions }.
 *
 * @access public
 */
class SubmissionsDAO extends SubmissionsDAOBase {
    final public static function getByGuid(string $guid) : ?Submissions {
        $sql = 'SELECT * FROM Submissions WHERE (guid = ?) LIMIT 1;';
        $params = [$guid];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new Submissions($rs);
    }

    final public static function disqualify(string $guid) : void {
        $sql = '
            UPDATE
                Submissions s
            SET
                s.type = "disqualified"
            WHERE
                s.guid = ?;
        ';
        global $conn;
        $conn->Execute($sql, [$guid]);
    }

    /**
     * Gets the count of total submissions sent to a given problem
     */
    final public static function countTotalSubmissionsOfProblem(
        int $problemId
    ) : int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problem_id = ? AND s.`type` = "normal";
        ';
        $val = [$problemId];

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /**
     * Get the count of submissions of a problem in a given problemset
     *
     * @param int $problemId
     * @param int $problemsetId
     */
    final public static function countTotalRunsOfProblemInProblemset(
        int $problemId,
        int $problemsetId
    ) : int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions
            WHERE
                problem_id = ? AND problemset_id = ? AND `type` = "normal";
        ';
        $val = [$problemId, $problemsetId];

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /**
     * Gets the count of total runs sent to a given problemset
     */
    final public static function countTotalSubmissionsOfProblemset(
        int $problemsetId
    ) : int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions
            WHERE
                problemset_id = ? AND `type` = "normal";
        ';
        $val = [$problemsetId];

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /**
     * Get last submission time of a user.
     */
    final public static function getLastSubmissionTime(
        int $identityId,
        int $problemId,
        ?int $problemsetId
    ) : ?int {
        if (is_null($problemsetId)) {
            $sql = '
                SELECT
                    UNIX_TIMESTAMP(MAX(s.time)) AS time
                FROM
                    Submissions s
                WHERE
                    s.identity_id = ? AND s.problem_id = ?
                ORDER BY
                    s.time DESC
                LIMIT 1;
            ';
            $val = [$identityId, $problemId];
        } else {
            $sql = '
                SELECT
                    UNIX_TIMESTAMP(MAX(s.time)) AS time
                FROM
                    Submissions s
                WHERE
                    s.identity_id = ? AND s.problem_id = ? AND s.problemset_id = ?
                ORDER BY
                    s.time DESC
                LIMIT 1;
            ';
            $val = [$identityId, $problemId, $problemsetId];
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }
}
