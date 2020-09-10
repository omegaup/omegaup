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
    /**
     * @return list<array{alias: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}>
     */
    public static function getSubmissionsForProblemset(int $problemsetId): array {
        $sql = 'SELECT
                    i.username,
                    p.alias,
                    sl.ip,
                    sl.`time`,
                    IFNULL(
                        (
                            SELECT `urc`.classname FROM
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
                                1
                        ),
                        "user-rank-unranked"
                    ) `classname`,
                    "submit" AS eventType
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
        $val = [$problemsetId];

        /** @var list<array{alias: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * @return list<array{alias: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}>
     */
    final public static function getSubmissionsForCourse(int $courseId): array {
        $sql = 'SELECT
                    i.username,
                    p.alias,
                    sl.ip,
                    sl.`time`,
                    IFNULL(
                        (
                            SELECT `urc`.classname FROM
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
                                1
                        ),
                        "user-rank-unranked"
                    ) `classname`,
                    "submit" AS eventType
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
        /** @var list<array{alias: string, classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }
}
