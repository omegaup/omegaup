<?php

require_once('base/Roles.dao.base.php');
require_once('base/Roles.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Roles Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Roles }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class RolesDAO extends RolesDAOBase {
    final public static function getByName($name) {
        $sql = 'SELECT
                    *
                FROM
                    Roles
                WHERE
                    name = ?';

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }

        return new Roles($row);
    }
}
