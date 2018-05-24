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
                    u.user_id,
                    u.username,
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
                                    `ur`.user_id = `u`.`user_id`
                            )
                    ORDER BY
                        `urc`.percentile ASC
                    LIMIT
                        1) `classname`
                FROM
                    Problemset_Access_Log pal
                INNER JOIN
                    Users u
                ON
                    u.user_id = pal.user_id
                WHERE
                    pal.problemset_id = ?
                ORDER BY `time`;';
        $val = [$problemset_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    final public static function GetAccessForCourse($course_id) {
        $sql = 'SELECT
                    u.user_id,
                    u.username,
                    pal.ip,
                    UNIX_TIMESTAMP(pal.time) AS `time`
                FROM
                    Problemset_Access_Log pal
                INNER JOIN
                    Users u
                ON
                    u.user_id = pal.user_id
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
}
