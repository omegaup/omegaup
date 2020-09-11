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
    /**
     * @return list<array{classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}>
     */
    public static function getAccessForProblemset(int $problemsetId) {
        $sql = 'SELECT
                    i.username,
                    pal.ip,
                    pal.`time`,
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
                    "open" AS eventType
                FROM
                    Problemset_Access_Log pal
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = pal.identity_id
                WHERE
                    pal.problemset_id = ?
                ORDER BY `time`;';
        $val = [$problemsetId];

        /** @var list<array{classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * @return list<array{classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}>
     */
    final public static function getAccessForCourse(int $courseId) {
        $sql = 'SELECT
                    i.username,
                    pal.ip,
                    pal.`time`,
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
                    "open" AS eventType
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
        /** @var list<array{classname: string, eventType: string, ip: int, time: \OmegaUp\Timestamp, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }

    /**
     * @return list<\OmegaUp\DAO\VO\ProblemsetAccessLog>
     */
    final public static function getByProblemsetIdentityId(
        int $problemsetId,
        int $identityId
    ): array {
        $sql = 'SELECT
                    *
                FROM
                    Problemset_Access_Log
                WHERE
                    problemset_id = ?
                AND
                    identity_id = ?;';

        /** @var list<array{identity_id: int, ip: int, problemset_id: int, time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId, $identityId]
        );

        $problemsetAccessLog = [];
        foreach ($rs as $row) {
            $problemsetAccessLog[] = new \OmegaUp\DAO\VO\ProblemsetAccessLog(
                $row
            );
        }
        return $problemsetAccessLog;
    }

    final public static function removeAccessLogFromProblemset(
        int $problemsetId
    ): int {
        $sql = '
            DELETE FROM
                `Problemset_Access_Log`
            WHERE
                `problemset_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problemsetId]);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
