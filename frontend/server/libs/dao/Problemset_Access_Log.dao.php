<?php

include('base/Problemset_Access_Log.dao.base.php');
include('base/Problemset_Access_Log.vo.base.php');
/** ProblemsetAccessLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetAccessLog }.
  * @access public
  *
  */
class ProblemsetAccessLogDAO extends ProblemsetAccessLogDAOBase {
    public static function GetAccessForProblemset($problemset_id) {
        $sql = 'SELECT
                    i.username,
                    pal.ip,
                    UNIX_TIMESTAMP(pal.time) AS `time`,
                    (SELECT `urc`.classname FROM
                        `User_Rank_Cutoffs` urc
                    WHERE
                        `urc`.score <= (
                                SELECT
                                    `ur`.`score`
                                FROM
                                    `User_Rank` `ur`
                                WHERE
                                    `ur`.user_id = `i`.`user_id`
                            )
                    ORDER BY
                        `urc`.percentile ASC
                    LIMIT
                        1) `classname`
                FROM
                    Problemset_Access_Log pal
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = pal.identity_id
                WHERE
                    pal.problemset_id = ?
                ORDER BY `time`;';
        $val = [$problemset_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    final public static function GetAccessForCourse($course_id) {
        $sql = 'SELECT
                    i.username,
                    pal.ip,
                    UNIX_TIMESTAMP(pal.time) AS `time`
                FROM
                    Problemset_Access_Log pal
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = pal.identity_id
                INNER JOIN
                    Assignments a
                ON
                    a.problemset_id = pal.problemset_id
                WHERE
                    a.course_id = ?
                ORDER BY
                    `time`;';
        global $conn;
        return $conn->GetAll($sql, [$course_id]);
    }

    final public static function getByProblemsetIdentityId($problemsetId, $identityId) {
        $sql = 'SELECT
                    *
                FROM
                    Problemset_Access_Log
                WHERE
                    problemset_id = ?
                AND
                    identity_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$problemsetId, $identityId]);

        $problemsetAccessLog = [];
        foreach ($rs as $row) {
            array_push($problemsetAccessLog, new ProblemsetAccessLog($row));
        }
        return $problemsetAccessLog;
    }
}
