<?php

include_once('base/Submissions.dao.base.php');
include_once('base/Submissions.vo.base.php');
/** Submissions Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Submissions }.
  * @access public
  *
  */
class SubmissionsDAO extends SubmissionsDAOBase {
    final public static function getByGuid($guid) {
        $sql = 'SELECT * FROM Submissions WHERE (guid = ?) LIMIT 1;';
        $params = [$guid];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }

        return new Submissions($rs);
    }
}
