<?php

include('base/Identity_Login_Log.dao.base.php');
include('base/Identity_Login_Log.vo.base.php');
/** IdentityLoginLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link IdentityLoginLog }.
  * @access public
  *
  */
class IdentityLoginLogDAO extends IdentityLoginLogDAOBase {
    final public static function getByIdentity($identityId) {
        $sql = 'SELECT
                    *
                FROM
                    Identity_Login_Log
                WHERE
                    identity_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$identityId]);

        $identityLoginLogs = [];
        foreach ($rs as $row) {
            array_push($identityLoginLogs, new IdentityLoginLog($row));
        }
        return $identityLoginLogs;
    }
}
