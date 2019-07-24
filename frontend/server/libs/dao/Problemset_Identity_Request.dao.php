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
    public static function getRequestsForProblemset(int $problemsetId) : array {
        global  $conn;
        $sql = '
            SELECT DISTINCT
                iu.username,
                iu.user_id,
                iu.username,
                iu.country_id,
                c.name AS country,
                r.identity_id,
                r.problemset_id,
                r.request_time,
                r.last_update,
                r.accepted,
                r.extra_note,
                h.admin_id,
                ia.user_id AS admin_user_id,
                ia.username AS admin_username,
                ia.name AS admin_name
            FROM
                `Problemset_Identity_Request` r
            INNER JOIN
                `Identities` iu
            ON
                iu.identity_id = r.identity_id
            LEFT JOIN
                `Countries` c
            ON
                c.country_id = iu.country_id
            LEFT JOIN
                `Problemset_Identity_Request_History` h
            ON
                r.problemset_id = h.problemset_id
                AND r.identity_id = h.identity_id
            LEFT JOIN
                `Identities` ia
            ON
                ia.user_id = h.admin_id
            WHERE
                r.problemset_id = ?;';

        return $conn->GetAll($sql, [$problemsetId]);
    }
}
