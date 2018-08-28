<?php

require_once('Estructura.php');
require_once('base/Groups.dao.base.php');
require_once('base/Groups.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Groups Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Groups }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class GroupsDAO extends GroupsDAOBase {
    public static function FindByAlias($alias) {
        global  $conn;
        $sql = 'SELECT g.* FROM Groups g WHERE g.alias = ? LIMIT 1;';
        $params = [$alias];
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Groups($rs);
    }

    public static function SearchByName($name) {
        global  $conn;
        $sql = "SELECT g.* from Groups g where g.name LIKE CONCAT('%', ?, '%') LIMIT 10;";
        $args = [$name];

        $rs = $conn->Execute($sql, $args);
        $ar = [];
        foreach ($rs as $row) {
            array_push($ar, new Groups($row));
        }
        return $ar;
    }

    public static function getByName($name) {
        global  $conn;
        $sql = 'SELECT g.* from Groups g where g.name = ? LIMIT 1;';

        $rs = $conn->GetRow($sql, [$name]);
        if (count($rs) == 0) {
            return null;
        }
        return new Groups($rs);
    }

    /**
     * Returns all groups that a user can manage.
     */
    final public static function getAllGroupsAdminedByUser($user_id, $identity_id) {
        $sql = '
            SELECT
                DISTINCT g.*
            FROM
                Groups g
            INNER JOIN
                ACLs AS a ON a.acl_id = g.acl_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = g.acl_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = g.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                a.owner_id = ? OR
                (ur.role_id = ? AND ur.user_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)
            ORDER BY
                g.group_id DESC;';
        $params = [
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            Authorization::ADMIN_ROLE,
            $identity_id,
        ];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $groups = [];
        foreach ($rs as $row) {
            array_push($groups, new Groups($row));
        }
        return $groups;
    }

    /**
     * Gets a random sample (of up to size $n) of group members.
     */
    final public static function sampleMembers(Groups $group, $n) {
        $sql = '
            SELECT
                i.*
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?
            ORDER BY
                RAND()
            LIMIT
                0, ?;';
        global $conn;

        $identities = [];
        foreach ($conn->Execute($sql, [$group->group_id, $n]) as $row) {
            $identities[] = new Identities($row);
        }
        return $identities;
    }
}
