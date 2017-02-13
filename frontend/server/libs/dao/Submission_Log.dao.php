<?php

include('base/Submission_Log.dao.base.php');
include('base/Submission_Log.vo.base.php');
/** SubmissionLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link SubmissionLog }.
  * @access public
  *
  */
class SubmissionLogDAO extends SubmissionLogDAOBase {
    public static function GetSubmissionsForProblemset(Problemsets $problemset) {
        $sql = 'SELECT u.username, p.alias, sl.ip, UNIX_TIMESTAMP(sl.time) AS `time` FROM Submission_Log sl INNER JOIN Users u ON u.user_id = sl.user_id INNER JOIN Runs r ON r.run_id = sl.run_id INNER JOIN Problems p ON p.problem_id = r.problem_id WHERE sl.problemset_id = ? ORDER BY time;';
        $val = [$problemset->problemset_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }
}
