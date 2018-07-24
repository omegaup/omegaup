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
    public static function GetMemberIdentities(Groups $group) {
        global  $conn;
        $sql = '
            SELECT
                i.username,
                IF(LOCATE(\':\', i.username) > 0, i.name, NULL) as name,
                IF(LOCATE(\':\', i.username) > 0, c.name, NULL) as country,
                IF(LOCATE(\':\', i.username) > 0, c.country_id, NULL) as country_id,
                IF(LOCATE(\':\', i.username) > 0, s.name, NULL) as state,
                IF(LOCATE(\':\', i.username) > 0, s.state_id, NULL) as state_id,
                IF(LOCATE(\':\', i.username) > 0, sc.name, NULL) as school,
                IF(LOCATE(\':\', i.username) > 0, sc.school_id, NULL) as school_id,
                IF(LOCATE(\':\', i.username) > 0, u.username, NULL) as user_username
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            LEFT JOIN
                States s ON s.state_id = i.state_id AND s.country_id = i.country_id
            LEFT JOIN
                Countries c ON c.country_id = s.country_id
            LEFT JOIN
                Schools sc ON sc.school_id = i.school_id
            LEFT JOIN
                Users u ON u.user_id = i.user_id
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
}
