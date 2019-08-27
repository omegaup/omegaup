<?php

include('base/User_Rank.dao.base.php');

/**
 * UserRank Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRank}.
 *
 * @access public
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
                `ur`.`user_id`,
                `ur`.`rank`,
                `ur`.`problems_solved_count` as `problems_solved`,
                `ur`.`score`,
                `ur`.`username`,
                `ur`.`name`,
                `ur`.`country_id`,
                (SELECT
                    `urc`.`classname`
                 FROM
                    `User_Rank_Cutoffs` `urc`
                 WHERE
                    `urc`.`score` <= `ur`.`score`
                 ORDER BY
                    `urc`.`percentile` ASC
                 LIMIT
                    1) as `classname`';
        $sql_count = '
              SELECT
                COUNT(1)';
        $params = [];
        $sql_from = '
              FROM
                `User_Rank` `ur`';
        if ($filteredBy == 'state') {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $sql_from .= ' WHERE `ur`.`country_id` = ? AND `ur`.`state_id` = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $sql_from .= ' WHERE `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape($filteredBy) . '_id` = ?';
        }
        if (!is_null($order)) {
            $sql_from .= ' ORDER BY `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape($order) . '` ' . ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        $sql_limit = '';
        $params_limit = [];
        if (!is_null($page)) {
            $params_limit[] = (($page - 1) * $colsPerPage); // Offset
            $params_limit[] = (int)$colsPerPage;
            $sql_limit = ' LIMIT ?, ?';
        }
        // Get total rows
        $total_rows = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql_count . $sql_from, $params);

        $params = array_merge($params, $params_limit);

        // Get rows
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql . $sql_from . $sql_limit, $params);
        return [
            'rows' => $allData,
            'total' => $total_rows
        ];
    }
}
