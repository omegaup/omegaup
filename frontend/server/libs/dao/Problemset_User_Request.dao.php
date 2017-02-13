<?php

include('base/Problemset_User_Request.dao.base.php');
include('base/Problemset_User_Request.vo.base.php');
/** ProblemsetUserRequest Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetUserRequest }.
  * @access public
  *
  */
class ProblemsetUserRequestDAO extends ProblemsetUserRequestDAOBase {
    public static function getRequestsForProblemset($problemset_id) {
        global  $conn;
        $sql = 'SELECT R.*, (select H.admin_id from `Problemset_User_Request_History` H where R.user_id = H.user_id '
                . ' order by H.history_id limit 1 ) as admin_id FROM `Problemset_User_Request` R where R.problemset_id = ? ';
        $args = [$problemset_id];

        return $conn->GetAll($sql, $args);
    }
}
