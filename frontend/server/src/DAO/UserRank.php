<?php

namespace OmegaUp\DAO;

/**
 * UserRank Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRank}.
 *
 * @access public
 */
class UserRank extends \OmegaUp\DAO\Base\UserRank {
    /**
     * @param null|string|int $value
     * @return array{rank: array{user_id: int, rank: int, problems_solved: int, score: float, username: string, name: ?string, country_id: ?string, classname: string}[], total: int}
     */
    public static function getFilteredRank(
        int $page,
        int $colsPerPage,
        ?string $order = null,
        string $orderType = 'ASC',
        ?string $filteredBy = null,
        $value = null
    ): array {
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
        if ($filteredBy == 'state' && is_string($value)) {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $sql_from .= ' WHERE `ur`.`country_id` = ? AND `ur`.`state_id` = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $sql_from .= ' WHERE `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $filteredBy
            ) . '_id` = ?';
        }
        if (!is_null($order)) {
            $sql_from .= ' ORDER BY `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' . ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        $paramsLimit = [
            (($page - 1) * intval($colsPerPage)), // Offset
            intval($colsPerPage),
        ];
        $sqlLimit = ' LIMIT ?, ?';
        // Get total rows
        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql_count . $sql_from,
            $params
        ) ?? 0;

        $params = array_merge($params, $paramsLimit);

        // Get rows
        /** @var array{user_id: int, rank: int, problems_solved: int, score: float, username: string, name: ?string, country_id: ?string, classname: string}[] */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql . $sql_from . $sqlLimit,
            $params
        );
        return [
            'rank' => $allData,
            'total' => $totalRows
        ];
    }
}
