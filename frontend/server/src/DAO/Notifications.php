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
    public static function getUnreadNotifications(\OmegaUp\DAO\VO\Users $user) {
        $sql = 'SELECT
                    n.notification_id, n.contents, UNIX_TIMESTAMP(n.timestamp) as timestamp
                FROM
                    Notifications n
                WHERE
                    n.user_id = ? AND n.read = 0
                ORDER BY
                    n.timestamp ASC;';
        $args = [$user->user_id];
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
    }
}
