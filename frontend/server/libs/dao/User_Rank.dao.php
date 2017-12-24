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
    public static function getFilteredRank($page = null, $colsPerPage = null, $order = null, $orderType = 'ASC', $filteredBy = null, $value = null) {
        $sql = '
                SELECT
                  user_id,
                  rank,
                  problems_solved_count,
                  score,
                  username, name,
                  country_id
                FROM
                  User_Rank ';
        global $conn;
        $params = [];
        if ($filteredBy == 'state') {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $sql .= ' WHERE country_id = ? AND state_id = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $sql .= ' WHERE ' . mysqli_real_escape_string($conn->_connectionID, $filteredBy) . '_id = ?';
        }
        if (!is_null($order)) {
            $sql .= ' ORDER BY ' . mysqli_real_escape_string($conn->_connectionID, $order) . ' ' . ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($page)) {
            $params[] = (($page - 1) * $colsPerPage); // Offset
            $params[] = (int)$colsPerPage;
            $sql .= ' LIMIT ?, ?';
        }
        $rs = $conn->Execute($sql, $params);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new UserRank($row);
        }
        return $allData;
    }
}
