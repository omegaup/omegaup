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
    final public static function getByUserId($user_id) {
        $sql = 'SELECT
                    *
                FROM
                    Emails
                WHERE
                    user_id = ?';

        global $conn;
        $rs = $conn->Execute($sql, [$user_id]);

        $emails = [];
        foreach ($rs as $row) {
            array_push($emails, new Emails($row));
        }
        return $emails;
    }
}
