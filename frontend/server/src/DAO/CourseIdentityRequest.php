<?php

namespace OmegaUp\DAO;

/**
 * CourseIdentityRequest Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CourseIdentityRequest}.
 *
 * @access public
 */
class CourseIdentityRequest extends \OmegaUp\DAO\Base\CourseIdentityRequest {
    /**
     * @return list<array{accepted: bool|null, admin_name: null|string, admin_username: null|string, country: null|string, country_id: null|string, identity_id: int, last_update: null|string, request_time: string, username: string}>
     */
    public static function getRequestsForCourseWithFirstAdmin(int $courseId) {
        $sql = '
            SELECT DISTINCT
                i.identity_id,
                i.username,
                i.country_id,
                c.name AS country,
                r.request_time,
                r.last_update,
                r.accepted,
                arh.username AS admin_username,
                arh.name AS admin_name
            FROM
                `Course_Identity_Request` r
            INNER JOIN
                `Identities` i
            ON
                i.identity_id = r.identity_id
            LEFT JOIN
                (
                    SELECT
                        rh.history_id,
                        rh.course_id,
                        rh.identity_id,
                        i.user_id,
                        i.username,
                        i.name
                    FROM
                        `Course_Identity_Request_History` rh
                    INNER JOIN
                        (
                            SELECT
                                MIN(history_id) AS `history_id`
                            FROM
                                `Course_Identity_Request_History`
                            WHERE
                                course_id = ?
                            GROUP BY
                                `identity_id`, `course_id`
                        ) h
                    ON
                        rh.history_id = h.history_id
                    INNER JOIN
                        `Users` u
                    ON
                        u.user_id = rh.admin_id
                    INNER JOIN
                         `Identities` i
                     ON
                        u.main_identity_id = i.identity_id
                ) arh
            ON
                arh.identity_id = r.identity_id
                AND arh.course_id = r.course_id
            LEFT JOIN
                `Countries` c
            ON
                c.country_id = i.country_id
            WHERE
                r.course_id = ?
            ORDER BY
                i.identity_id;';

        /** @var list<array{accepted: bool|null, admin_name: null|string, admin_username: null|string, country: null|string, country_id: null|string, identity_id: int, last_update: null|string, request_time: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId, $courseId]
        );
    }
}
