<?php

include_once('base/Problemset_Identity_Request.dao.base.php');
include_once('base/Problemset_Identity_Request.vo.base.php');
/** ProblemsetIdentityRequest Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentityRequest }.
  * @access public
  *
  */
class ProblemsetIdentityRequestDAO extends ProblemsetIdentityRequestDAOBase {
    public static function getFirstAdminForProblemsetRequest(
        int $problemsetId
    ) : ?array {
        global $conn;
        $sql = '
        SELECT
            hi.identity_id AS contestant_id,
            i.username,
            i.user_id,
            i.name,
            hi.history_id
        FROM
            `Identities` i
        INNER JOIN
            `Problemset_Identity_Request_History` hi
        ON
            hi.admin_id = i.user_id
        WHERE
            i.user_id IN (
                SELECT
                    (SELECT
                        h.admin_id
                    FROM
                        `Problemset_Identity_Request_History` h
                    WHERE
                        r.identity_id = h.identity_id
                        AND r.problemset_id = h.problemset_id
                    LIMIT
                        1) AS admin_id
                FROM
                    `Problemset_Identity_Request` r
                WHERE
                    r.problemset_id = ?)
            AND hi.problemset_id = ?
        ORDER BY
            hi.history_id;';

        return $conn->GetAll($sql, [$problemsetId, $problemsetId]);
    }

    public static function getRequestsForProblemset(int $problemsetId) : array {
        global  $conn;
        $sql = '
            SELECT DISTINCT
                i.identity_id,
                i.username,
                i.user_id,
                i.username,
                i.country_id,
                c.name AS country,
                r.problemset_id,
                r.request_time,
                r.last_update,
                r.accepted,
                r.extra_note
            FROM
                `Problemset_Identity_Request` r
            INNER JOIN
                `Identities` i
            ON
                i.identity_id = r.identity_id
            LEFT JOIN
                `Countries` c
            ON
                c.country_id = i.country_id
            WHERE
                r.problemset_id = ?
            ORDER BY
                i.identity_id;';

        return $conn->GetAll($sql, [$problemsetId]);
    }
}
