<?php

require_once('Estructura.php');
require_once('base/Groups_Users.dao.base.php');
require_once('base/Groups_Users.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** GroupsUsers Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsUsers }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class GroupsUsersDAO extends GroupsUsersDAOBase {
    public static function GetMemberUsernames(Groups $group) {
        global  $conn;
        $sql = '
            SELECT
                u.username
            FROM
                Groups_Users gu
            INNER JOIN
                Users u ON u.user_id = gu.user_id
            WHERE
                gu.group_id = ?;';
        $params = [$group->group_id];
        return $conn->GetAll($sql, $params);
    }

    public static function GetMemberCountById($group_id) {
        global  $conn;
        $sql = '
            SELECT
                COUNT(*) AS count
            FROM
                Groups_Users gu
            WHERE
                gu.group_id = ?;';
        $params = [$group_id];
        return $conn->GetOne($sql, $params);
    }
}
