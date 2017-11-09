<?php

require_once('base/Emails.dao.base.php');
require_once('base/Emails.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Emails Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Emails }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class EmailsDAO extends EmailsDAOBase {
  /**
   * @param $email
   * @return $name
   */
    public static function getUserNameByEmail($email) {
        $sql = 'SELECT
              name
            FROM
              Users u
            INNER JOIN
              Emails e
            ON
              e.user_id = u.user_id
            WHERE
              email = ?;';

        $params = [$email];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $result = [];
        foreach ($rs as $r) {
            $result[] = $r['name'];
        }
        return $result[0];
    }
}
