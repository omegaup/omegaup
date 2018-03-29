<?php

include_once('base/Problemset_Identity_Request.dao.base.php');
include_once('base/Problemset_Identity_Request.vo.base.php');
/** ProblemsetIdentityRequest Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentityRequest }.
  * @access public
  *
  */
class ProblemsetIdentityRequestDAO extends ProblemsetIdentityRequestDAOBase {
    public static function getRequestsForProblemset($problemset_id) {
        global  $conn;
        $sql = 'SELECT R.*, (select H.admin_id from `Problemset_Identity_Request_History` H where R.identity_id = H.identity_id '
                . ' order by H.history_id limit 1 ) as admin_id FROM `Problemset_Identity_Request` R where R.problemset_id = ? ';
        $args = [$problemset_id];

        return $conn->GetAll($sql, $args);
    }
}
