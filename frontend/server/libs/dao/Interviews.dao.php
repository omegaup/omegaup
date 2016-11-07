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
        $params = array($contest->contest_id);
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    final public static function getMyInterviews($user_id)
    {
        $sql = '
            SELECT
                c.*
            FROM
                Contests AS c
            INNER JOIN
                Interviews AS i
            ON
                i.contest_id = c.contest_id
            INNER JOIN
                ACLs AS a
            ON
                a.acl_id = c.acl_id
            WHERE
                a.owner_id = ?
                OR (SELECT COUNT(*) FROM User_Roles WHERE user_id = ? AND role_id = ? AND acl_id = a.acl_id) > 0;';

        $params = array($user_id, $user_id, Authorization::ADMIN_ROLE);

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
