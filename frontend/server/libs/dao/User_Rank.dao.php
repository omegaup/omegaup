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

    public static function getFilteredRank($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC', $filteredBy = null, $value = null) {
        $sql = 'SELECT ur.user_id, ur.rank, ur.problems_solved_count, ur.score, ur.username, ur.name, ur.country_id from User_Rank ur inner join Users u on ur.user_id = u.user_id';
        global $conn;
        if (!is_null($filteredBy)) {
            $sql .= ' WHERE u.' . $filteredBy . ' = \'' . $value . '\'';
        }
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new UserRank($row);
        }
        return $allData;
    }
}
