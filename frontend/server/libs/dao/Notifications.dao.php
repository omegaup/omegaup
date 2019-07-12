<?php

require_once('base/Notifications.dao.base.php');
require_once('base/Notifications.vo.base.php');
/** Notifications Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Notifications }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class NotificationsDAO extends NotificationsDAOBase {
    public static function getUnreadNotifications(Users $user) {
        global $conn;
        $sql = 'SELECT
                    n.notification_id, n.contents, n.timestamp
                FROM
                    Notifications n
                WHERE
                    n.user_id = ? AND n.read = 0
                ORDER BY
                    n.timestamp ASC;';
        $args = [$user->user_id];
        return $conn->GetAll($sql, $args);
    }
}
