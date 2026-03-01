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
 *
 * @psalm-type AuthorsRank=array{ranking: list<array{author_ranking: int|null, author_score: float, country_id: null|string, username: string, name: null|string, classname: string}>, total: int}
 */
class UserRank extends \OmegaUp\DAO\Base\UserRank {
    /**
     * @param null|string|int $value
     * @return array{rank: list<array{classname: string, country_id: null|string, name: null|string, problems_solved: int, ranking: null|int, score: float, timestamp: \OmegaUp\Timestamp, user_id: int, username: string}>, total: int}
     */
    public static function getFilteredRank(
        int $page,
        int $colsPerPage,
        ?string $order = null,
        string $orderType = 'ASC',
        ?string $filteredBy = null,
        $value = null,
        ?int $rankingCursor = null
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
                `ur`.`timestamp`,
                IFNULL(`ur`.`classname`, "user-rank-unranked") AS classname';
        $sqlCount = '
              SELECT
                COUNT(1)';
        $params = [];
        $whereClauses = ['`ur`.`ranking` IS NOT NULL'];
        if ($filteredBy === 'state' && is_string($value)) {
            $values = explode('-', $value);
            $params[] = $values[0];
            $params[] = $values[1];
            $whereClauses[] = '`ur`.`country_id` = ?';
            $whereClauses[] = '`ur`.`state_id` = ?';
        } elseif (!empty($filteredBy)) {
            $params[] = $value;
            $whereClauses[] = '`ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $filteredBy
            ) . '_id` = ?';
        }

        $countParams = $params;
        $orderByClause = '';
        if (!is_null($order)) {
            $orderByClause = ' ORDER BY `ur`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' . ($orderType === 'DESC' ? 'DESC' : 'ASC');
        }

        $useKeysetPagination = (
            !is_null($rankingCursor) &&
            (is_null($order) || $order === 'ranking') &&
            $orderType === 'ASC'
        );
        $dataWhereClauses = $whereClauses;
        if ($useKeysetPagination) {
            $dataWhereClauses[] = '`ur`.`ranking` > ?';
            $params[] = $rankingCursor;
            if (is_null($order)) {
                $orderByClause = ' ORDER BY `ur`.`ranking` ASC';
            }
        }

        $sqlFromCount = '
              FROM
                `User_Rank` `ur`
              WHERE
                ' . implode(' AND ', $whereClauses);
        $sqlFromData = '
              FROM
                `User_Rank` `ur`
              WHERE
                ' . implode(' AND ', $dataWhereClauses);

        $paramsLimit = $useKeysetPagination
            ? [intval($colsPerPage)]
            : [
                max(0, $page - 1) * intval($colsPerPage),
                intval($colsPerPage),
            ];
        $sqlLimit = $useKeysetPagination ? ' LIMIT ?' : ' LIMIT ?, ?';
        // Get total rows
        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount}{$sqlFromCount}",
            $countParams
        ) ?? 0;

        $params = array_merge($params, $paramsLimit);

        // Get rows
        /** @var list<array{classname: string, country_id: null|string, name: null|string, problems_solved: int, ranking: int|null, score: float, timestamp: \OmegaUp\Timestamp, user_id: int, username: string}> */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$sql}{$sqlFromData}{$orderByClause}{$sqlLimit}",
            $params
        );
        return [
            'rank' => $allData,
            'total' => $totalRows
        ];
    }

    /**
     * @return AuthorsRank
     */
    public static function getAuthorsRank(
        int $page,
        int $rowsPerPage,
        ?int $authorRankingCursor = null
    ): array {
        $sqlSelect = '
            SELECT
                `ur`.`author_ranking`,
                `ur`.`author_score`,
                `ur`.`username`,
                `ur`.`country_id`,
                `ur`.`name`,
                IFNULL(`ur`.`classname`, "user-rank-unranked") AS classname
        ';
        $sqlCount = '
            SELECT
                COUNT(*)
        ';
        $sqlFrom = '
            FROM
                `User_Rank` `ur`
            WHERE
                `ur`.`author_score` IS NOT NULL AND
                `ur`.`author_ranking` IS NOT NULL
        ';
        $sqlFromCount = $sqlFrom;
        if (!is_null($authorRankingCursor)) {
            $sqlFrom .= ' AND `ur`.`author_ranking` > ?';
        }
        $sqlOrderBy = '
            ORDER BY
                    `ur`.`author_ranking` ASC
        ';
        $sqlLimit = is_null($authorRankingCursor) ? ' LIMIT ?, ?' : ' LIMIT ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount}{$sqlFromCount}",
            []
        ) ?? 0;

        /** @var list<array{author_ranking: int|null, author_score: float, classname: string, country_id: null|string, name: null|string, username: string}> */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$sqlSelect}{$sqlFrom}{$sqlOrderBy}{$sqlLimit}",
            is_null($authorRankingCursor)
                ? [
                    max(0, $page - 1) * $rowsPerPage,
                    $rowsPerPage
                ]
                : [
                    $authorRankingCursor,
                    $rowsPerPage
                ]
        );
        return [
            'ranking' => $allData,
            'total' => $totalRows,
        ];
    }

    /**
     * @return AuthorsRank
     */
    public static function getAuthorsRankWithQualityProblems(
        int $page,
        int $rowsPerPage
    ): array {
        $sqlSelect = '
            SELECT
                IFNULL(`ur`.`author_ranking`, 0) AS `author_ranking`,
                IFNULL(`ur`.`author_score`, 0) AS `author_score`,
                `ur`.`country_id`,
                `ur`.`username`,
                `ur`.`name`,
                IFNULL(`ur`.`classname`, "user-rank-unranked") AS classname
        ';
        $sqlFrom = '
            FROM
                `User_Rank` `ur`
            WHERE
                `ur`.`author_score` IS NOT NULL AND
                `ur`.`author_ranking` IS NOT NULL AND
                (
                    SELECT
                        COUNT(*)
                    FROM
                        `Problems` `p`
                    INNER JOIN
                        `ACLs` `acl` ON `p`.`acl_id` = `acl`.`acl_id`
                    INNER JOIN
                        `Users` `u` ON `u`.`user_id` = `acl`.`owner_id`
                    WHERE
                    `u`.`user_id` = `ur`.`user_id` AND `p`.`quality_seal` = 1
                ) > 0
        ';
        $sqlCount = '
            SELECT
                COUNT(1)
        ';
        $sqlOrderBy = '
            ORDER BY
                    `ur`.`author_ranking` ASC
        ';
        $sqlLimit = ' LIMIT ?, ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount}{$sqlFrom}",
            []
        ) ?? 0;

        /** @var list<array{author_ranking: int, author_score: float, classname: string, country_id: null|string, name: null|string, username: string}> */
        $allData = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$sqlSelect}{$sqlFrom}{$sqlOrderBy}{$sqlLimit}",
            [
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage
            ]
        );
        return [
            'ranking' => $allData,
            'total' => $totalRows,
        ];
    }
}
