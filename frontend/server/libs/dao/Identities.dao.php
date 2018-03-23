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
    public static function getStatusVerified($email) {
        global  $conn;
        $sql = 'SELECT
                  u.verified,
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

        return [
            'verified' => $rs['verified'] == 1,
            'username' => $rs['username']
        ];
    }
}
