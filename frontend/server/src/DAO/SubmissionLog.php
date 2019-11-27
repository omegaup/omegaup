<?php

namespace OmegaUp\DAO;

/**
 * SubmissionLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SubmissionLog}.
 *
 * @access public
 */
class SubmissionLog extends \OmegaUp\DAO\Base\SubmissionLog {
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
                    Submissions s
                ON
                    s.submission_id = sl.submission_id
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = s.problem_id
                WHERE
                    sl.problemset_id = ?
                ORDER BY
                    `time`;';
        $val = [$problemset_id];

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * @return list<array{alias?: string, classname: string, ip: int, time: int, username: string}>
     */
    final public static function GetSubmissionsForCourse(int $courseId) {
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
                    Submissions s
                ON
                    s.submission_id = sl.submission_id
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = s.problem_id
                INNER JOIN
                    Assignments a
                ON
                    a.problemset_id = sl.problemset_id
                WHERE
                    a.course_id = ?
                ORDER BY
                    `time`;';
        /** @var list<array{alias?: string, classname: string, ip: int, time: int, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }
}
