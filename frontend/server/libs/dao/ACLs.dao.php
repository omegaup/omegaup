<?php

include('base/ACLs.dao.base.php');
include('base/ACLs.vo.base.php');
/** ACLs Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ACLs }.
  * @access public
  *
  */
class ACLsDAO extends ACLsDAOBase {
    public static function getACLIdentityByPK($acl_id) {
        //look for it on the database
        global $conn;
        $sql = 'SELECT
                  i.identity_id
                FROM
                  `Identities` i
                INNER JOIN
                  `ACLs` a
                ON
                  a.owner_id = i.user_id
                WHERE
                  a.acl_id = ?;';
        $params = [$acl_id];
        return $conn->GetOne($sql, $params);
    }
}
