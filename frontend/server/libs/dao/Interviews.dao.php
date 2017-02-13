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
class InterviewsDAO extends InterviewsDAOBase {
    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Interviews WHERE alias = ? LIMIT 1;';
        $params = [$alias];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }

        $interview = new Interviews($rs);

        return $interview;
    }

    final public static function getMyInterviews($user_id) {
        $sql = '
            SELECT
                i.*
            FROM
                Interviews AS i
            INNER JOIN
                ACLs AS a
            ON
                a.acl_id = i.acl_id
            WHERE
                a.owner_id = ?
                OR (SELECT COUNT(*) FROM User_Roles WHERE user_id = ? AND role_id = ? AND acl_id = a.acl_id) > 0;';

        $params = [$user_id, $user_id, Authorization::ADMIN_ROLE];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $result = [];

        foreach ($rs as $r) {
            $result[] = $r;
        }

        return $result;
    }
}
