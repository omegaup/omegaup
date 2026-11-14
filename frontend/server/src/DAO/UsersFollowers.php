<?php

namespace OmegaUp\DAO;

/**
 * UsersFollowers Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UsersFollowers}.
 *
 * @access public
 * @package docs
 */
class UsersFollowers extends \OmegaUp\DAO\Base\UsersFollowers {
    /**
     * Removes the follow relationship between two users.
     *
     * @return int The number of rows that were deleted, 0 if the relationship
     *             did not exist.
     */
    public static function unfollow(
        int $followerUserId,
        int $followedUserId
    ): int {
        $sql = '
            DELETE FROM
                `Users_Followers`
            WHERE
                `follower_user_id` = ?
                AND `followed_user_id` = ?;';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$followerUserId, $followedUserId]
        );
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    public static function isFollowing(
        int $followerUserId,
        int $followedUserId
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Users_Followers`
            WHERE
                `follower_user_id` = ?
                AND `followed_user_id` = ?;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$followerUserId, $followedUserId]
        );
        return $count > 0;
    }

    /**
     * Gets how many users follow $userId and how many $userId follows.
     *
     * @return array{followersCount: int, followingCount: int}
     */
    public static function getCounts(int $userId): array {
        $sql = '
            SELECT
                (
                    SELECT
                        COUNT(*)
                    FROM
                        `Users_Followers`
                    WHERE
                        `followed_user_id` = ?
                ) AS `followersCount`,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        `Users_Followers`
                    WHERE
                        `follower_user_id` = ?
                ) AS `followingCount`;';
        /** @var array{followersCount: int|null, followingCount: int|null}|null */
        $counts = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$userId, $userId]
        );
        return [
            'followersCount' => $counts['followersCount'] ?? 0,
            'followingCount' => $counts['followingCount'] ?? 0,
        ];
    }

    /**
     * Gets the users that follow $userId, most recent first.
     *
     * @return array{followers: list<array{classname: string, name: null|string, username: string}>, totalRows: int}
     */
    public static function getFollowers(
        int $userId,
        int $page,
        int $rowsPerPage
    ): array {
        $sqlFrom = '
            FROM
                `Users_Followers` uf
            INNER JOIN
                `Users` u ON u.user_id = uf.follower_user_id
            INNER JOIN
                `Identities` i ON i.identity_id = u.main_identity_id
            LEFT JOIN
                `User_Rank` ur ON ur.user_id = u.user_id
            WHERE
                uf.followed_user_id = ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT
                COUNT(*)
            FROM
                `Users_Followers`
            WHERE
                `followed_user_id` = ?;',
            [$userId]
        ) ?? 0;

        /** @var list<array{classname: string, name: null|string, username: string}> */
        $followers = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "SELECT
                i.username,
                i.name,
                IFNULL(ur.classname, 'user-rank-unranked') AS classname
            {$sqlFrom}
            ORDER BY
                uf.created_at DESC, uf.users_follower_id DESC
            LIMIT ?, ?;",
            [
                $userId,
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
            ]
        );

        return [
            'followers' => $followers,
            'totalRows' => $totalRows,
        ];
    }

    /**
     * Gets the users that $userId follows, most recent first.
     *
     * @return array{following: list<array{classname: string, name: null|string, username: string}>, totalRows: int}
     */
    public static function getFollowing(
        int $userId,
        int $page,
        int $rowsPerPage
    ): array {
        $sqlFrom = '
            FROM
                `Users_Followers` uf
            INNER JOIN
                `Users` u ON u.user_id = uf.followed_user_id
            INNER JOIN
                `Identities` i ON i.identity_id = u.main_identity_id
            LEFT JOIN
                `User_Rank` ur ON ur.user_id = u.user_id
            WHERE
                uf.follower_user_id = ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT
                COUNT(*)
            FROM
                `Users_Followers`
            WHERE
                `follower_user_id` = ?;',
            [$userId]
        ) ?? 0;

        /** @var list<array{classname: string, name: null|string, username: string}> */
        $following = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "SELECT
                i.username,
                i.name,
                IFNULL(ur.classname, 'user-rank-unranked') AS classname
            {$sqlFrom}
            ORDER BY
                uf.created_at DESC, uf.users_follower_id DESC
            LIMIT ?, ?;",
            [
                $userId,
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
            ]
        );

        return [
            'following' => $following,
            'totalRows' => $totalRows,
        ];
    }
}
