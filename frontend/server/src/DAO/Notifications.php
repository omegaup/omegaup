<?php

namespace OmegaUp\DAO;

/**
 * Notifications Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Notifications}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Notifications extends \OmegaUp\DAO\Base\Notifications {
    const CONTEST_CLARIFICATION_REQUEST = 'contest-clarification-request';
    const CONTEST_CLARIFICATION_RESPONSE = 'contest-clarification-response';
    const COURSE_CLARIFICATION_REQUEST = 'course-clarification-request';
    const COURSE_CLARIFICATION_RESPONSE = 'course-clarification-response';
    const COURSE_REGISTRATION_ACCEPTED = 'course-registration-accepted';
    const COURSE_REGISTRATION_REJECTED = 'course-registration-rejected';
    const COURSE_REGISTRATION_REQUEST = 'course-registration-request';
    const COURSE_SUBMISSION_FEEDBACK = 'course-submission-feedback';
    const COURSE_REGISTRATION_MANUAL = 'course-registration-manual';
    const COURSE_ADMINISTRATOR_ADDED = 'course-administrator-added';
    const DEMOTION = 'demotion';

    /**
     * @return list<array{contents: string, notification_id: int, timestamp: \OmegaUp\Timestamp}>
     */
    public static function getUnreadNotifications(
        \OmegaUp\DAO\VO\Users $user
    ): array {
        $sql = '
            SELECT
                `n`.`notification_id`,
                `n`.`contents`,
                `n`.`timestamp`
            FROM
                Notifications `n`
            WHERE
                `n`.`user_id` = ? AND
                `n`.`read` = 0
            ORDER BY
                `n`.`timestamp` ASC;
        ';
        /** @var list<array{contents: string, notification_id: int, timestamp: \OmegaUp\Timestamp}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$user->user_id]
        );
    }
}
