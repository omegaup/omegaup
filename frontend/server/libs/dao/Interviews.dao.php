<?php

include('base/Interviews.dao.base.php');
include('base/Interviews.vo.base.php');
/** Interviews Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Interviews }.
  * @access public
  *
  */
class InterviewsDAO extends InterviewsDAOBase
{
    public static function isContestInterview(Contests $contest) {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Interviews
            WHERE
                contest_id = ?;';
        $params = array($contest->getContestId());
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    final public static function getMyInterviews($user_id)
    {
        $sql = 'select c.* from Contests c, Interviews i
                where
                 (c.contest_id = i.contest_id)
                 and
                 (c.director_id = ?
                or c.contest_id in (select contest_id from User_Roles where user_id = ? and role_id = 2))';

        $params = array($user_id, $user_id);

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $result = array();

        foreach ($rs as $r) {
            $r['interview'] = true;
            $result[] = $r;
        }

        return $result;
    }
}
