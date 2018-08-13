<?php

include_once('base/Groups_Identities.dao.base.php');
include_once('base/Groups_Identities.vo.base.php');
/** GroupsIdentities Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsIdentities }.
  * @access public
  *
  */
class GroupsIdentitiesDAO extends GroupsIdentitiesDAOBase {
    public static function GetMemberUsernames(Groups $group) {
        global  $conn;
        $sql = '
            SELECT
                i.username
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?;';
        $params = [$group->group_id];
        return $conn->GetAll($sql, $params);
    }

    public static function GetMemberCountById($group_id) {
        global  $conn;
        $sql = '
            SELECT
                COUNT(*) AS count
            FROM
                Groups_Identities gi
            WHERE
                gi.group_id = ?;';
        $params = [$group_id];
        return $conn->GetOne($sql, $params);
    }

    final public static function getByGroupId($groupId) {
        $sql = '
            SELECT
                *
            FROM
                Groups_Identities i
            WHERE
                group_id = ?;';

        global $conn;
        $groupsIdentities = [];
        foreach ($conn->Execute($sql, [$groupId]) as $row) {
            array_push($groupsIdentities, new GroupsIdentities($row));
        }
        return $groupsIdentities;
    }

    final public static function getUsernamesByGroupId($group_id) {
        $sql = '
            SELECT
                i.username
            FROM
                Identities i
            INNER JOIN
                Groups_Identities gi
            ON
                i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?;';

        global $conn;
        $identities = [];
        foreach ($conn->Execute($sql, [$group_id]) as $row) {
            array_push($identities, $row['username']);
        }
        return $identities;
    }
}
