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
        $vo_Query = new Identities([
            'username' => $username
        ]);

        $a_Results = IdentitiesDAO::search($vo_Query);

        if (sizeof($a_Results) != 1) {
            return null;
        }

        return array_pop($a_Results);
    }

    public static function FindByUserId($user_id) {
        global  $conn;
        $sql = 'SELECT
                  i.*
                FROM
                  `Identities` i
                WHERE
                  i.user_id = ?';
        $params = [ $user_id ];
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }
        return new Identities($rs);
    }
}
