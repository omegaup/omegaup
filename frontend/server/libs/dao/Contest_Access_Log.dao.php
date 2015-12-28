<?php

include('base/Contest_Access_Log.dao.base.php');
include('base/Contest_Access_Log.vo.base.php');
/** ContestAccessLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestAccessLog }.
  * @access public
  *
  */
class ContestAccessLogDAO extends ContestAccessLogDAOBase {
    public static function GetAccessForContest(Contests $contest) {
        $sql = 'SELECT u.username, cal.ip, UNIX_TIMESTAMP(cal.time) AS `time` FROM Contest_Access_Log cal INNER JOIN Users u ON u.user_id = cal.user_id WHERE cal.contest_id = ? ORDER BY time;';
        $val = array($contest->contest_id);

        global $conn;
        return $conn->GetAll($sql, $val);
    }
}
