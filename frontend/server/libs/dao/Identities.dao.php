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
    public static function FindByEmail($email) {
        global  $conn;
        $sql = 'SELECT
                  i.*
                FROM
                  `Identities` i
                INNER JOIN
                  `Emails` e
                ON
                  e.user_id = i.user_id
                WHERE
                  e.email = ?';
        $params = [ $email ];
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }
        return new Identities($rs);
    }
    public static function FindByUsername($username) {
        $result = IdentitiesDAO::search(new Identities([
            'username' => $username
        ]));
        if (sizeof($result) != 1) {
            return null;
        }
        return array_pop($result);
    }
}
