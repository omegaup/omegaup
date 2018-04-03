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
    public static function getExtraInformation($email) {
        global  $conn;
        $sql = 'SELECT
                  u.reset_sent_at,
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
          // Asks whether request was made on the last day
          'within_last_day' => Time::get() - strtotime($rs['reset_sent_at']) < 60 * 60 * 24,
          'verified' => $rs['verified'] == 1,
          'username' => $rs['username']
        ];
    }
    public static function isVerified($identity_id) {
        global  $conn;
        $sql = 'SELECT
                  u.verified
                FROM
                  `Identities` i
                INNER JOIN
                  `Users` u
                ON
                  u.user_id = i.user_id
                WHERE
                  i.identity_id = ?
                ORDER BY
                  u.user_id DESC
                LIMIT
                  0, 1';
        $params = [ $identity_id ];
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }
        return $rs['verified'];
    }
}
