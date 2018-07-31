<?php

require_once('Estructura.php');
require_once('base/Groups_Scoreboards.dao.base.php');
require_once('base/Groups_Scoreboards.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** GroupsScoreboards Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsScoreboards }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class GroupsScoreboardsDAO extends GroupsScoreboardsDAOBase {
    public static function getByGroup($group_id) {
        $sql = 'SELECT * FROM Groups_Scoreboards WHERE group_id = ?;';
        global $conn;
        $rs = $conn->Execute($sql, [$group_id]);

        $groupsScoreboards = [];
        foreach ($rs as $row) {
            array_push($groupsScoreboards, new GroupsScoreboards($row));
        }
        return $groupsScoreboards;
    }

    public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Groups_Scoreboards WHERE alias = ? LIMIT 1;';
        global $conn;
        $rs = $conn->GetRow($sql, [$alias]);
        if (count($rs) == 0) {
            return null;
        }

        return new GroupsScoreboards($rs);
    }
}
