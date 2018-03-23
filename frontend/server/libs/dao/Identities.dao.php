<?php

include_once('base/Identities.dao.base.php');
include_once('base/Identities.vo.base.php');
/** Identities Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Identities }.
  * @access public
  *
  */
class IdentitiesDAO extends IdentitiesDAOBase {
    public static function getLastPasswordChangeRequest($email) {
        global  $conn;
        $sql = 'SELECT
                  u.reset_sent_at,
                  u.username
                FROM
                  `Identities` i
                INNER JOIN
                  `Users` u
                ON
                  u.user_id = i.user_id
                INNER JOIN
                  `Emails` e
                ON
                  e.user_id = u.user_id
                WHERE
                  e.email = ?
                ORDER BY
                  u.user_id DESC
                LIMIT
                  0, 1';
        $params = [ $email ];
        $rs = $conn->GetRow($sql, $params);

        if (count($rs)==0) {
            return null;
        }

        if (is_null($rs['reset_sent_at'])) {
            return ['within_last_day' => null];
        }
        // Request was made 24 hours ago or after
        $response['within_last_day'] =
            Time::get() - strtotime($rs['reset_sent_at']) < 60 * 60 * 24;
        $response['username'] = $rs['username'];
        return $response;
    }
}
