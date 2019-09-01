<?php

namespace OmegaUp\DAO;

/**
 * ProblemsetAccessLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetAccessLog}.
 *
 * @access public
 */
class ProblemsetAccessLog extends \OmegaUp\DAO\Base\ProblemsetAccessLog {
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

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
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
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$course_id]);
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

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemsetId, $identityId]);

        $problemsetAccessLog = [];
        foreach ($rs as $row) {
            array_push($problemsetAccessLog, new \OmegaUp\DAO\VO\ProblemsetAccessLog($row));
        }
        return $problemsetAccessLog;
    }
}
