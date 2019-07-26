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
                r.*,
                (SELECT
                    h.admin_id
                FROM
                    `Problemset_Identity_Request_History` h
                WHERE
                    r.identity_id = h.identity_id
                    AND r.problemset_id = h.problemset_id
                ORDER BY
                    h.history_id
                LIMIT
                    1) AS admin_id
            FROM
                `Problemset_Identity_Request` r
            WHERE
                r.problemset_id = ?;';

        $rs = $conn->GetAll($sql, [$problemsetId]);

        $admins = [];
        $requestsAdmins = [];
        foreach ($rs as $result) {
            $adminId = $result['admin_id'];
            if (!array_key_exists($adminId, $admins)) {
                $admin = [];
                $data = IdentitiesDAO::getByPK($adminId);
                if (!is_null($data)) {
                    $admin = [
                        'user_id' => $data->user_id,
                        'username' => $data->username,
                        'name' => $data->name,
                    ];
                }
                $requestsAdmins[$result['identity_id']] = $admin;
            }
        }

        return $requestsAdmins;
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
