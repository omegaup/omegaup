<?php

include('base/Interview.dao.base.php');
include('base/Interview.vo.base.php');

/** Interview Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Interview }.
  * @access public
  *
  */
class InterviewDAO extends InterviewDAOBase
{
    final public static function getBackingContestByAlias($alias)
    {
        $sql = 'SELECT * FROM Contests WHERE (alias = ? ) LIMIT 1;';
        $params = array(  $alias );

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }

        $contest = new Contests($rs);

        //InterviewDAO::getByPK($contest->);

        return $contest;
    }

    final public static function getMyInterviews($user_id)
    {
        $sql = 'select * from Interview i, Contests c
                where
                    i.contest_id = c.contest_id
                and (c.director_id = ?
                        or c.contest_id in (select contest_id from User_Roles where user_id = ? and role_id = 2))'; // is role 2 the admin ?
        $params = array($user_id, $user_id);

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $result = array();

        foreach ($rs as $r) {
            $result[] = $r; // neded ?
        }

        return $result;
    }
}
