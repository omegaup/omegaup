<?php

namespace OmegaUp\DAO;

/**
 * Notifications Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Notifications}.
 * @access public
 * @package docs
 */
class Notifications extends \OmegaUp\DAO\Base\Notifications {
    const CERTIFICATE_AWARDED = 'certificate-awarded';
    const CONTEST_CLARIFICATION_REQUEST = 'contest-clarification-request';
    const CONTEST_CLARIFICATION_RESPONSE = 'contest-clarification-response';
    const CONTEST_REGISTRATION_ACCEPTED = 'contest-registration-accepted';
    const CONTEST_REGISTRATION_REJECTED = 'contest-registration-rejected';
    const CONTEST_REGISTRATION_REQUEST = 'contest-registration-request';
    const COURSE_CLARIFICATION_REQUEST = 'course-clarification-request';
    const COURSE_CLARIFICATION_RESPONSE = 'course-clarification-response';
    const COURSE_REGISTRATION_ACCEPTED = 'course-registration-accepted';
    const COURSE_REGISTRATION_REJECTED = 'course-registration-rejected';
    const COURSE_REGISTRATION_REQUEST = 'course-registration-request';
    const COURSE_SUBMISSION_FEEDBACK = 'course-submission-feedback';
    const COURSE_SUBMISSION_FEEDBACK_THREAD = 'course-submission-feedback-thread';
    const COURSE_REGISTRATION_MANUAL = 'course-registration-manual';
    const COURSE_ADMINISTRATOR_ADDED = 'course-administrator-added';
    const COURSE_TEACHING_ASSISTANT_ADDED = 'course-teaching-assistant-added';
    const COURSE_REQUEST_FEEDBACK = 'course-request-feedback';
    const COURSE_ASSIGNMENT_ADDED = 'course_assignment_added';
    const COURSE_ASSIGNMENT_PROBLEM_ADDED = 'course_assignment_problem_added';
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

    /**
     * Creates multiple notifications in a single bulk INSERT query.
     * This method is optimized for creating many notifications at once,
     * reducing database round-trips from O(N) to O(1).
     *
     * @param list<\OmegaUp\DAO\VO\Notifications> $notifications Array of Notification objects to create
     * @return int Number of rows affected
     */
    public static function createBulk(array $notifications): int {
        if (empty($notifications)) {
            return 0;
        }

        $rowPlaceholders = [];
        $params = [];

        foreach ($notifications as $notification) {
            $rowPlaceholders[] = '(?, ?, ?, ?)';
            $params[] = (
                $notification->user_id === null ?
                null :
                intval($notification->user_id)
            );
            $params[] = \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $notification->timestamp
            );
            $params[] = intval($notification->read);
            $params[] = $notification->contents;
        }

        $sql = '
            INSERT INTO
                `Notifications` (
                    `user_id`,
                    `timestamp`,
                    `read`,
                    `contents`
                ) VALUES ' . join(', ', $rowPlaceholders) . ';';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
