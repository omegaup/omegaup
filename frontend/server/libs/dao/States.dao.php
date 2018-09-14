<?php

require_once('base/States.dao.base.php');
require_once('base/States.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** States Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link States }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class StatesDAO extends StatesDAOBase {
    final public static function getByCountry($countryId) {
        $sql = 'SELECT
                    *
                FROM
                    States
                WHERE
                    country_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$countryId]);

        $states = [];
        foreach ($rs as $row) {
            array_push($states, new States($row));
        }
        return $states;
    }
}
