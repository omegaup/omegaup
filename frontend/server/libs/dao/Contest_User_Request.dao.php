<?php

include('base/Contest_User_Request.dao.base.php');
include('base/Contest_User_Request.vo.base.php');
/** ContestUserRequest Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestUserRequest }.
  * @access public
  *
  */
class ContestUserRequestDAO extends ContestUserRequestDAOBase
{
    public static function getRequestsForContest($contest_id)
    {
        global  $conn;
        $sql = 'SELECT R.*, (select H.admin_id from `Contest_User_Request_History` H where R.user_id = H.user_id '
                . ' order by H.history_id limit 1 ) as admin_id FROM `Contest_User_Request` R where R.contest_id = ? ';

        $args = array($contest_id);

        $rs = $conn->Execute($sql, $args);

        $result = array();

        foreach ($rs as $request) {
            array_push($result, $request);
        }
        return $result;
    }
}
