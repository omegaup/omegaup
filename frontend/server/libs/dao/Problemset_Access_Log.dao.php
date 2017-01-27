<?php

include('base/Problemset_Access_Log.dao.base.php');
include('base/Problemset_Access_Log.vo.base.php');
/** ProblemsetAccessLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetAccessLog }.
  * @access public
  *
  */
class ProblemsetAccessLogDAO extends ProblemsetAccessLogDAOBase
{
    public static function GetAccessForProblemset(Problemsets $problemset) {
        $sql = 'SELECT u.username, pal.ip, UNIX_TIMESTAMP(pal.time) AS `time` FROM Problemset_Access_Log pal INNER JOIN Users u ON u.user_id = pal.user_id WHERE pal.problemset_id = ? ORDER BY time;';
        $val = array($problemset->problemset_id);

        global $conn;
        return $conn->GetAll($sql, $val);
    }
}
