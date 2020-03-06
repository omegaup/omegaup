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
    public static function getFirstAdminForCourseRequest(
        int $courseId
    ): ?array {
        $sql = '
            SELECT
                r.*,
                (SELECT
                    h.admin_id
                FROM
                    `Course_Identity_Request_History` h
                WHERE
                    r.identity_id = h.identity_id
                    AND r.course_id = h.course_id
                ORDER BY
                    h.history_id
                LIMIT
                    1) AS admin_id
            FROM
                `Course_Identity_Request` r
            WHERE
                r.course_id = ?;';

        /** @var list<array{accepted: bool|null, admin_id: int|null, extra_note: null|string, identity_id: int, last_update: null|string, course_id: int, request_time: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }

    public static function getRequestsForCourse(int $courseId): array {
        $sql = '
            SELECT DISTINCT
                i.identity_id,
                i.username,
                i.user_id,
                i.username,
                i.country_id,
                c.name AS country,
                r.course_id,
                r.request_time,
                r.last_update,
                r.accepted,
                r.extra_note
            FROM
                `Course_Identity_Request` r
            INNER JOIN
                `Identities` i
            ON
                i.identity_id = r.identity_id
            LEFT JOIN
                `Countries` c
            ON
                c.country_id = i.country_id
            WHERE
                r.course_id = ?
            ORDER BY
                i.identity_id;';

        $result = [];
        /** @var array{accepted: bool|null, country: null|string, country_id: null|string, extra_note: null|string, identity_id: int, last_update: null|string, course_id: int, request_time: string, user_id: int|null, username: string, username: string} $row */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }
}
