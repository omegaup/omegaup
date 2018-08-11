<?php

require_once('base/Languages.dao.base.php');
require_once('base/Languages.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Languages Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Languages }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class LanguagesDAO extends LanguagesDAOBase {
    final public static function getByName($name) {
        $sql = 'SELECT
                    *
                FROM
                    Languages
                WHERE
                    name = ?
                LIMIT
                    0, 1;';

        global $conn;
        $row = $conn->GetRow($sql, [$name]);
        if (count($row) == 0) {
            return null;
        }

        return new Languages($row);
    }
}
