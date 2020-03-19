<?php

namespace OmegaUp\DAO;

/**
 * ProblemsetIdentityRequest Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetIdentityRequest}.
 *
 * @access public
 */
class ProblemsetIdentityRequest extends \OmegaUp\DAO\Base\ProblemsetIdentityRequest {
    /**
     * @return list<array{accepted: bool|null, admin_id: int|null, extra_note: null|string, identity_id: int, last_update: null|string, problemset_id: int, request_time: string}>
     */
    public static function getFirstAdminForProblemsetRequest(
        int $problemsetId
    ) {
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

        /** @var list<array{accepted: bool|null, admin_id: int|null, extra_note: null|string, identity_id: int, last_update: null|string, problemset_id: int, request_time: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * @return list<array{accepted: bool|null, country: null|string, identity_id: int, last_update: null|string, request_time: string, username: string}>
     */
    public static function getRequestsForProblemset(int $problemsetId) {
        $sql = '
            SELECT DISTINCT
                i.identity_id,
                i.username,
                c.name AS country,
                r.request_time,
                r.last_update,
                r.accepted
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

        $result = [];
        /** @var array{accepted: bool|null, country: null|string, identity_id: int, last_update: null|string, request_time: string, username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$problemsetId]
            ) as $row
        ) {
            $row['accepted'] = boolval($row['accepted']);
            $result[] = $row;
        }
        return $result;
    }
}
