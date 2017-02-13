<?php

include('base/User_Rank.dao.base.php');
include('base/User_Rank.vo.base.php');
/** UserRank Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link UserRank }.
  * @access public
  *
  */
class UserRankDAO extends UserRankDAOBase {
    /**
     * Actualiza la tabla User_Rank
     *
     * @return boolean
     */
    public static function refreshUserRank() {
        global $conn;
        $sql = 'CALL Refresh_User_Rank();';
        $conn->Execute($sql);

        return true;
    }
}
