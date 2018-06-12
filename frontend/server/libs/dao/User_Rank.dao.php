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
    public static function getFilteredRank(
        $page = null,
        $colsPerPage = null,
        $order = null,
        $orderType = 'ASC',
        $filteredBy = null,
        $value = null
    ) {
        $sql = '
              SELECT
                user_id,
                rank,
                problems_solved_count,
                score,
                username, name,
                country_id';
        $sql_count = '
              SELECT
                COUNT(1)';
        global $conn;
        $params = [];
        $sql_from = '
              FROM
                User_Rank ';
        if ($filteredBy == 'state') {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $sql_from .= ' WHERE country_id = ? AND state_id = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $sql_from .= ' WHERE ' . mysqli_real_escape_string($conn->_connectionID, $filteredBy) . '_id = ?';
        }
        if (!is_null($order)) {
            $sql_from .= ' ORDER BY ' . mysqli_real_escape_string($conn->_connectionID, $order) . ' ' . ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        $sql_limit = '';
        $params_limit = [];
        if (!is_null($page)) {
            $params_limit[] = (($page - 1) * $colsPerPage); // Offset
            $params_limit[] = (int)$colsPerPage;
            $sql_limit = ' LIMIT ?, ?';
        }
        // Get total rows
        $total_rows = $conn->GetOne($sql_count . $sql_from, $params);

        $params = array_merge($params, $params_limit);

        // Get rows
        $rs = $conn->Execute($sql . $sql_from . $sql_limit, $params);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new UserRank($row);
        }
        return [
            'rows' => $allData,
            'total' => $total_rows
        ];
    }
}
