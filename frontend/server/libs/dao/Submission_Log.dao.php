<?php

include('base/Submission_Log.dao.base.php');
include('base/Submission_Log.vo.base.php');
/** SubmissionLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link SubmissionLog }.
  * @access public
  *
  */
class SubmissionLogDAO extends SubmissionLogDAOBase {
    public static function GetSubmissionsForProblemset($problemset_id) {
        $sql = 'SELECT
                    i.username,
                    p.alias,
                    sl.ip,
                    UNIX_TIMESTAMP(sl.time) AS `time`,
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
                    Submission_Log sl
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = sl.identity_id
                INNER JOIN
                    Runs r
                ON
                    r.run_id = sl.run_id
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = r.problem_id
                WHERE
                    sl.problemset_id = ?
                ORDER BY
                    `time`;';
        $val = [$problemset_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    final public static function GetSubmissionsForCourse($course_id) {
        $sql = 'SELECT
                    i.username,
                    p.alias,
                    sl.ip,
                    UNIX_TIMESTAMP(sl.time) AS `time`
                FROM
                    Submission_Log sl
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = sl.identity_id
                INNER JOIN
                    Runs r
                ON
                    r.run_id = sl.run_id
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = r.problem_id
                INNER JOIN
                    Assignments a
                ON
                    a.problemset_id = sl.problemset_id
                WHERE
                    a.course_id = ?
                ORDER BY
                    `time`;';
        global $conn;
        return $conn->GetAll($sql, [$course_id]);
    }
}
