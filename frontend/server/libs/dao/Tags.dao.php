<?php

require_once('base/Tags.dao.base.php');
require_once('base/Tags.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Tags Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Tags }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class TagsDAO extends TagsDAOBase {
    final public static function getByName($name) {
        $sql = 'SELECT * FROM Tags WHERE (name = ? ) LIMIT 1;';
        $params = [$name];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }

        return new Tags($rs);
    }

    public static function FindByName($name) {
        global $conn;
        $sql = "SELECT name FROM Tags WHERE name LIKE CONCAT('%', ?, '%') LIMIT 100";
        $args = [$name];

        $rs = $conn->Execute($sql, $args);
        $result = [];
        foreach ($rs as $row) {
            array_push($result, new Tags($row));
        }
        return $result;
    }
}
