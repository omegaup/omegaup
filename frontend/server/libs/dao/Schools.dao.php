<?php

require_once('base/Schools.dao.base.php');
require_once('base/Schools.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Schools Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Schools }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class SchoolsDAO extends SchoolsDAOBase
{
    /**
     * Finds schools that cotains 'name'
     *
     * @global type $conn
     * @param string $name
     * @return array Schools
     */
    public static function findByName($name) {
        global  $conn;

        $sql = '
            SELECT
                s.*
            FROM
                Schools s
            WHERE
                s.name LIKE CONCAT(\'%\', ?, \'%\')
            LIMIT 10';
        $args = array($name);

        $result = array();
        foreach ($conn->Execute($sql, $args) as $row) {
            $result[] = new Schools($row);
        }
        return $result;
    }
}
