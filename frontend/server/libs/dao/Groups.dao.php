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
        $sql = 'SELECT g.* FROM Groups g WHERE g.alias = ?;';
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
}
