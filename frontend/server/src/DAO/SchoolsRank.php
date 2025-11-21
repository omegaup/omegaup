<?php

namespace OmegaUp\DAO;

/**
 * SchoolsRank Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SchoolsRank}.
 */

/**
 * @psalm-type SchoolsRank=array{rank: list<array{country_id: null|string, name: string, ranking: int|null, school_id: int, score: float}>, total: int}
 */
class SchoolsRank {
    /**
     * @param null|string|int $value
     * @return SchoolsRank
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
                `s`.`school_id`,
                `s`.`ranking`,
                `s`.`name`,
                `s`.`country_id`,
                `s`.`score`';
        $sqlCount = '
              SELECT
                COUNT(1)';
        $params = [];
        $sqlFrom = '
              FROM
                `Schools` `s`
              WHERE
                `s`.`score` != 0';
        if ($filteredBy === 'state' && is_string($value)) {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $sqlFrom .= ' AND `s`.`country_id` = ? AND `s`.`state_id` = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $sqlFrom .= ' AND `s`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $filteredBy
            ) . '_id` = ?';
        }
        if (!is_null($order)) {
            $sqlFrom .= ' ORDER BY `s`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' . ($orderType === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $sqlFrom .= ' ORDER BY `s`.`ranking` IS NULL, `s`.`ranking` ASC';
        }
        $paramsLimit = [
            max(0, $page - 1) * intval($colsPerPage), // Offset
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
        /** @var list<array{country_id: null|string, name: string, ranking: int|null, school_id: int, score: float}> */
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
