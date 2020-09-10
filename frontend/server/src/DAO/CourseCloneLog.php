<?php

namespace OmegaUp\DAO;

/**
 * CourseCloneLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CourseCloneLog}.
 *
 * @access public
 */
class CourseCloneLog extends \OmegaUp\DAO\Base\CourseCloneLog {
    /**
     * @return list<array{alias: string, classname: string, eventType: string, ip: int, name: string, result: string, time: \OmegaUp\Timestamp, token_payload: string, username: string}>
     */
    final public static function getCloneAttemptsForCourse(int $courseId) {
        $sql = 'SELECT
                    i.username,
                    INET_ATON(ccl.ip) AS `ip`,
                    ccl.`timestamp` AS `time`,
                    ccl.result,
                    ccl.token_payload,
                    c.name,
                    c.alias,
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
                    "clone" AS eventType
                FROM
                Course_Clone_Log ccl
                INNER JOIN
                    Users u
                ON
                    u.user_id = ccl.user_id
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = u.main_identity_id
                LEFT JOIN
                    Courses c
                ON
                    c.course_id = ccl.new_course_id
                WHERE
                    ccl.course_id = ?
                ORDER BY
                    `time`;';
        /** @var list<array{alias: string, classname: string, eventType: string, ip: int, name: string, result: string, time: \OmegaUp\Timestamp, token_payload: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }
}
