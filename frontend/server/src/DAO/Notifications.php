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
    const COURSE_REGISTRATION_REQUEST = 'course-registration-request';
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
