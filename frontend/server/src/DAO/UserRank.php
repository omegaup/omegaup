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
     * @return array{rank: list<array{classname: string, country_id: null|string, name: null|string, problems_solved: int, ranking: int, score: float, user_id: int, username: string}>, total: int}
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
                `ur`.`ranking`,
                `ur`.`problems_solved_count` as `problems_solved`,
                `ur`.`score`,
                `ur`.`username`,
                `ur`.`name`,
                `ur`.`country_id`,
                IFNULL(
                    (
                        SELECT
                            `urc`.`classname`
                        FROM
                            `User_Rank_Cutoffs` `urc`
                        WHERE
                            `urc`.`score` <= `ur`.`score`
                        ORDER BY
                            `urc`.`percentile` ASC
                        LIMIT 1
                    ),
                    "user-rank-unranked"
                ) as `classname`';
        $sqlCount = '
              SELECT
                COUNT(1)';
        $params = [];
        $sqlFrom = '
              FROM
                `User_Rank` `ur`';
        if ($filteredBy === 'state' && is_string($value)) {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $sqlFrom .= ' WHERE `ur`.`country_id` = ? AND `ur`.`state_id` = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $sqlFrom .= ' WHERE `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $filteredBy
            ) . '_id` = ?';
        }
        if (!is_null($order)) {
            $sqlFrom .= ' ORDER BY `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' . ($orderType === 'DESC' ? 'DESC' : 'ASC');
        }
        $paramsLimit = [
            (($page - 1) * intval($colsPerPage)), // Offset
            intval($colsPerPage),
        ];
        $sqlLimit = ' LIMIT ?, ?';
        // Get total rows
        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount}{$sqlFrom}",
            $params
        ) ?? 0;

        $params = array_merge($params, $paramsLimit);

        // Get rows
        /** @var list<array{classname: string, country_id: null|string, name: null|string, problems_solved: int, ranking: int, score: float, user_id: int, username: string}> */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$sql}{$sqlFrom}{$sqlLimit}",
            $params
        );
        return [
            'rank' => $allData,
            'total' => $totalRows
        ];
    }
}
