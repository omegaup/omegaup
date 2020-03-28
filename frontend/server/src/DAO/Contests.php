<?php

namespace OmegaUp\DAO;

/**
 * Contests Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Contests}.
 *
 * @author alanboy
 * @access public
 * @package docs
  */
class Contests extends \OmegaUp\DAO\Base\Contests {
    /** @var string */
    private static $getContestsColumns = '
                                Contests.contest_id,
                                Contests.problemset_id,
                                Contests.title,
                                Contests.description,
                                Contests.finish_time as original_finish_time,
                                UNIX_TIMESTAMP(Contests.start_time) as start_time,
                                UNIX_TIMESTAMP(Contests.finish_time) as finish_time,
                                Contests.admission_mode,
                                Contests.alias,
                                Contests.recommended,
                                Contests.window_length,
                                UNIX_TIMESTAMP(Contests.last_updated) as last_updated,
                                Contests.rerun_id
                                ';

    final public static function getByAlias(string $alias): ?\OmegaUp\DAO\VO\Contests {
        $sql = 'SELECT * FROM Contests WHERE alias = ? LIMIT 1;';

        /** @var array{acl_id: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Contests($rs);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Contests>
     */
    final public static function getByTitle(string $title) {
        $sql = 'SELECT * FROM Contests WHERE title = ?;';

        /** @var list<array{acl_id: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$title]);

        $contests = [];
        foreach ($rs as $row) {
            $contests[] = new \OmegaUp\DAO\VO\Contests($row);
        }
        return $contests;
    }

    /**
     * @return array{acl_id: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null
     */
    final public static function getByAliasWithExtraInformation(string $alias): ?array {
        $sql = '
                SELECT
                    c.*,
                    p.scoreboard_url,
                    p.scoreboard_url_admin
                FROM
                    Contests c
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = c.problemset_id
                WHERE c.alias = ? LIMIT 1;';
        $params = [$alias];

        /** @var array{acl_id: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return $rs;
    }

    final public static function getByProblemset(
        int $problemsetId
    ): ?\OmegaUp\DAO\VO\Contests {
        $sql = 'SELECT * FROM Contests WHERE problemset_id = ? LIMIT 0, 1;';
        /** @var array{acl_id: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemsetId]
        );
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Contests($row);
    }

    public static function getPrivateContestsCount(
        \OmegaUp\DAO\VO\Users $user
    ): int {
        if (is_null($user->user_id)) {
            return 0;
        }
        $sql = 'SELECT
           COUNT(c.contest_id) as total
        FROM
            Contests AS c
        INNER JOIN
            ACLs AS a
        ON
            a.acl_id = c.acl_id
        WHERE
            admission_mode = \'private\' and a.owner_id = ?;';
        $params = [$user->user_id];
        /** @var array{total: int} */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);

        if (!array_key_exists('total', $rs)) {
            return 0;
        }

        return intval($rs['total']);
    }

    public static function hasStarted(\OmegaUp\DAO\VO\Contests $contest): bool {
        return \OmegaUp\Time::get() >= $contest->start_time;
    }

    public static function hasFinished(\OmegaUp\DAO\VO\Contests $contest): bool {
        return \OmegaUp\Time::get() >= $contest->finish_time;
    }

    /**
     * @return list<array{alias: string, title: string, start_time: int, finish_time: int, last_updated: int, scoreboard_url_admin: string}>
     */
    public static function getContestsParticipated(int $identityId) {
        $sql = '
            SELECT
                c.contest_id,
                c.alias,
                c.title,
                UNIX_TIMESTAMP(c.start_time) AS start_time,
                UNIX_TIMESTAMP(c.finish_time) AS finish_time,
                UNIX_TIMESTAMP(c.last_updated) AS last_updated,
                p.scoreboard_url_admin
            FROM
                Contests c
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = c.problemset_id
            WHERE c.contest_id IN (
                SELECT DISTINCT
                    c2.contest_id
                FROM
                    Submissions s
                INNER JOIN
                    Contests c2
                ON
                    c2.problemset_id = s.problemset_id
                WHERE
                    s.identity_id = ? AND s.type= \'normal\' AND s.problemset_id IS NOT NULL
            )
            ORDER BY
                contest_id DESC;';

        /** @var list<array{alias: string, contest_id: int, finish_time: int, last_updated: int, scoreboard_url_admin: string, start_time: int, title: string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId]
        );
        foreach ($result as &$row) {
            // We need to get contest_id just to be able to ORDER BY it, but we
            // should not return it to users.
            unset($row['contest_id']);
        }
        return $result;
    }

    /**
     * Returns all contests that an identity can manage.
     *
     * @return list<array{admission_mode: string, alias: string, finish_time: int, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string}>
     */
    final public static function getAllContestsAdminedByIdentity(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.title,
                c.alias,
                UNIX_TIMESTAMP (c.start_time) as start_time,
                UNIX_TIMESTAMP (c.finish_time) as finish_time,
                c.admission_mode,
                c.rerun_id,
                ps.scoreboard_url,
                ps.scoreboard_url_admin
            FROM
                Contests c
            INNER JOIN
                Problemsets AS ps ON ps.problemset_id = c.problemset_id
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = c.acl_id
            LEFT JOIN
                Identities uri ON uri.user_id = ur.user_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = c.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                ai.identity_id = ? OR
                (ur.role_id = ? AND uri.identity_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)
            GROUP BY
                c.contest_id
            ORDER BY
                c.contest_id DESC
            LIMIT ?, ?;';

        $params = [
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            $offset,
            $pageSize,
        ];

        /** @var list<array{admission_mode: string, alias: string, finish_time: int, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Get relevant columns of all contests, including scoreboard_url columns
     *
     * @return list<array{title: string, alias: string, start_time: int, finish_time: int, admission_mode: string, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string}>
     */
    final public static function getAllContestsWithScoreboard(
        ?int $page = 1,
        int $pageSize = 1000,
        ?string $order = null,
        string $orderType = 'ASC'
    ) {
        $sql = '
            SELECT
                c.title,
                c.alias,
                UNIX_TIMESTAMP (c.start_time) as start_time,
                UNIX_TIMESTAMP (c.finish_time) as finish_time,
                c.admission_mode,
                c.rerun_id,
                ps.scoreboard_url,
                ps.scoreboard_url_admin
            FROM
                Contests c
            INNER JOIN
                Problemsets ps ON ps.problemset_id = c.problemset_id';

        if (!is_null($order)) {
            $sql .= ' ORDER BY `c`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' .
                    ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($page)) {
            $sql .= ' LIMIT ' . (($page - 1) * $pageSize) . ', ' . intval(
                $pageSize
            );
        }

        /** @var list<array{title: string, alias: string, start_time: int, finish_time: int, admission_mode: string, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * Returns all contests owned by a user.
     *
     * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string, window_length: int|null}>
     */
    final public static function getAllContestsOwnedByUser(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000
    ): array {
        $offset = ($page - 1) * $pageSize;
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $sql = "
            SELECT
                $columns,
                p.scoreboard_url,
                p.scoreboard_url_admin
            FROM
                Contests
            INNER JOIN
                ACLs a ON a.acl_id = Contests.acl_id
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            INNER JOIN
                Problemsets p ON p.problemset_id = Contests.problemset_id
            WHERE
                u.main_identity_id = ?
            ORDER BY
                Contests.contest_id DESC
            LIMIT ?, ?;";
        $params = [
            $identityId,
            intval($offset),
            intval($pageSize),
        ];

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Returns all contests where a user is participating in.
     *
     * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string, window_length: int|null}>
     */
    final public static function getContestsParticipating(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000,
        int $active = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        ?string $query = null
    ) {
        $activeCondition = \OmegaUp\DAO\Enum\ActiveStatus::sql(
            $active
        );
        $recommendedCondition = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ALL
        );
        $filter = self::formatSearch($query);
        $queryCondition = \OmegaUp\DAO\Enum\FilteredStatus::sql(
            $filter['type']
        );
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $offset = ($page - 1) * $pageSize;

        $sql = "
            SELECT
                $columns,
                p.scoreboard_url,
                p.scoreboard_url_admin
            FROM
                Contests
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = Contests.problemset_id
            INNER JOIN
                (
                    SELECT
                        pi.identity_id,
                        pi.problemset_id
                    FROM
                        Problemset_Identities pi
                    UNION DISTINCT
                    SELECT
                        gi.identity_id,
                        p.problemset_id
                    FROM
                        Problemsets p
                    INNER JOIN
                        Group_Roles gr
                    ON
                        gr.acl_id = p.acl_id AND
                        gr.role_id = ?
                    INNER JOIN
                        Groups_Identities gi
                    ON
                        gi.group_id = gr.group_id
                ) pi
            ON
                pi.problemset_id = p.problemset_id AND
                pi.identity_id = ?
            WHERE
                $recommendedCondition AND
                $activeCondition AND
                $queryCondition
        ";
        $params = [
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            $identityId,
        ];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= '
            ORDER BY
                recommended DESC,
                finish_time DESC
            LIMIT ?, ?;
        ';
        $params[] = intval($offset);
        $params[] = intval($pageSize);

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Returns all recent public contests.
     *
     * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
     */
    final public static function getRecentPublicContests(
        int $user_id,
        int $page = 1,
        int $pageSize = 1000,
        ?string $query = null
    ) {
        $end_check = \OmegaUp\DAO\Enum\ActiveStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE
        );
        $recommended_check = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ALL
        );
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $offset = ($page - 1) * $pageSize;
        $filter = self::formatSearch($query);
        $query_check = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);

        $sql = "
            SELECT
                $columns
            FROM
                Contests
            WHERE
                $recommended_check  AND $end_check AND $query_check
                AND `admission_mode` != 'private'
            ORDER BY
                `last_updated` DESC,
                `recommended` DESC,
                `finish_time` DESC,
                `contest_id` DESC
            LIMIT ?, ?;";

        $params = [];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = intval($offset);
        $params[] = intval($pageSize);

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Regresa todos los concursos que una identidad puede ver.
     *
     * Explicación:
     *
     * La estructura de este query optimiza el uso de indíces en mysql.
     *
     * El primer SELECT transforma las columnas a como las espera la API.
     * Luego:
     *
     * Todos los concursos privados donde la identidad fue el creador
     * UNION
     * Todos los concursos privados a los que la identidad ha sido invitada
     * UNION
     * Todos los concursos privados a los que la identidad es ADMIN
     * UNION
     * Todos los concursos privados donde la identidad pertenece a un grupo que es ADMIN del concurso
     * UNION
     * Todos los concursos públicos.
     *
     * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
     */
    final public static function getAllContestsForIdentity(
        int $identityId,
        int $pagina = 1,
        int $renglones_por_pagina = 1000,
        int $activos = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        int $recomendados = \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
        ?string $query = null
    ): array {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $end_check = \OmegaUp\DAO\Enum\ActiveStatus::sql($activos);
        $recommended_check = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            $recomendados
        );
        $filter = self::formatSearch($query);
        $query_check = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);

        $sql = "
                 (
                    SELECT
                        $columns
                    FROM
                        Contests
                    INNER JOIN
                        ACLs
                    ON
                        ACLs.acl_id = Contests.acl_id
                    INNER JOIN
                        Identities
                    ON
                        ACLs.owner_id = Identities.user_id
                    WHERE
                        Contests.admission_mode = 'private' AND Identities.identity_id = ? AND
                        $recommended_check AND $end_check AND $query_check
                 ) ";
        $params = [$identityId];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION DISTINCT
                 (
                    SELECT
                        $columns
                    FROM
                        Contests
                    INNER JOIN
                        Problemset_Identities
                    ON
                        Contests.problemset_id = Problemset_Identities.problemset_id
                    WHERE
                        Contests.admission_mode = 'private' AND Problemset_Identities.identity_id = ? AND
                        $recommended_check AND $end_check AND $query_check
                 ) ";
        $params[] = $identityId;
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION DISTINCT
                 (
                    SELECT
                        $columns
                    FROM
                        Contests
                    INNER JOIN
                        Problemsets
                    ON
                        Problemsets.problemset_id = Contests.problemset_id
                    INNER JOIN
                        Group_Roles gr
                    ON
                        gr.acl_id = Problemsets.acl_id AND
                        gr.role_id = ?
                    INNER JOIN
                        Groups_Identities gi
                    ON
                        gi.group_id = gr.group_id
                    WHERE
                        Contests.admission_mode = 'private' AND
                        gi.identity_id = ? AND
                        $recommended_check AND $end_check AND $query_check
                 ) ";
        $params[] = \OmegaUp\Authorization::CONTESTANT_ROLE;
        $params[] = $identityId;
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION DISTINCT
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     INNER JOIN
                         User_Roles
                     ON
                         User_Roles.acl_id = Contests.acl_id
                     INNER JOIN
                         Identities
                     ON
                         Identities.user_id = User_Roles.user_id
                     WHERE
                         Contests.admission_mode = 'private' AND
                         Identities.identity_id = ? AND
                         User_Roles.role_id = ? AND
                         $recommended_check AND $end_check AND $query_check
                 ) ";
        $params[] = $identityId;
        $params[] = \OmegaUp\Authorization::ADMIN_ROLE;
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION DISTINCT
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     INNER JOIN
                         Group_Roles ON Contests.acl_id = Group_Roles.acl_id
                     INNER JOIN
                         Groups_Identities
                     ON
                         Groups_Identities.group_id = Group_Roles.group_id
                     WHERE
                         Contests.admission_mode = 'private' AND
                         Groups_Identities.identity_id = ? AND
                         Group_Roles.role_id = ? AND
                         $recommended_check AND $end_check AND $query_check
                 ) ";
        $params[] = $identityId;
        $params[] = \OmegaUp\Authorization::ADMIN_ROLE;
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $sql .= "
                 UNION DISTINCT
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     WHERE
                         admission_mode <> 'private' AND $recommended_check AND $end_check AND $query_check
                 )
                 ORDER BY
                     CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                     `recommended` DESC,
                     `original_finish_time` DESC
                 LIMIT ?, ?
                ";
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = intval($offset);
        $params[] = intval($renglones_por_pagina);
        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
     */
    final public static function getAllPublicContests(
        int $pagina = 1,
        int $renglones_por_pagina = 1000,
        int $activos = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        int $recomendados = \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
        ?string $query = null
    ): array {
        $offset = ($pagina - 1) * $renglones_por_pagina;
        $end_check = \OmegaUp\DAO\Enum\ActiveStatus::sql($activos);
        $recommended_check = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            $recomendados
        );
        $filter = self::formatSearch($query);
        $query_check = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);

        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;

        $sql = "
               SELECT
                    $columns
                FROM
                    `Contests`
                WHERE
                    `admission_mode` <> 'private'
                AND $recommended_check
                AND $end_check
                AND $query_check
                ORDER BY
                    CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                    `recommended` DESC,
                    `original_finish_time` DESC
                LIMIT ?, ?
                ";

        $params = [];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = intval($offset);
        $params[] = intval($renglones_por_pagina);
        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /** @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
     */
    final public static function getAllContests(
        int $pagina = 1,
        int $renglones_por_pagina = 1000,
        int $activos = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        int $recomendados = \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
        ?string $query = null
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $end_check = \OmegaUp\DAO\Enum\ActiveStatus::sql($activos);
        $recommended_check = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            $recomendados
        );
        $filter = self::formatSearch($query);
        $query_check = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);

        $sql = "
                SELECT
                    $columns
                FROM
                    Contests
                WHERE $recommended_check AND $end_check AND $query_check
                ORDER BY
                    CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                    `recommended` DESC,
                    `original_finish_time` DESC
                LIMIT ?, ?
                ";

        $params = [];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = intval($offset);
        $params[] = intval($renglones_por_pagina);
        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    public static function getContestForProblemset(?int $problemsetId): ?\OmegaUp\DAO\VO\Contests {
        if (is_null($problemsetId)) {
            return null;
        }

        return \OmegaUp\DAO\Contests::getByProblemset($problemsetId);
    }

    /**
     * @return array{needsBasicInformation: bool, requestsUserInformation: string}
     */
    public static function getNeedsInformation(int $problemsetId): array {
        $sql = '
                SELECT
                    needs_basic_information,
                    requests_user_information
                FROM
                    Problemsets
                WHERE
                    problemset_id = ?
                LIMIT 1
                ';

        $params = [$problemsetId];

        /** @var array{needs_basic_information: bool, requests_user_information: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        return [
            'needsBasicInformation' => $rs['needs_basic_information'] == '1',
            'requestsUserInformation' => $rs['requests_user_information']
        ];
    }

    /**
     * Generate alias of virtual contest / ghost mode
     *
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @param \OmegaUp\DAO\VO\Users $user
     * @return string of unique virtual contest alias
     */
    public static function generateAlias(\OmegaUp\DAO\VO\Contests $contest): string {
        // Virtual contest alias format (alias-virtual-random)
        return (
            substr(strval($contest->alias), 0, 20) .
            '-virtual-' .
            \OmegaUp\SecurityTools::randomString(3)
        );
    }

    /**
     * Check if contest is virtual contest
     */
    public static function isVirtual(\OmegaUp\DAO\VO\Contests $contest): bool {
        return $contest->rerun_id != 0;
    }

    /**
     * @param null|string $query
     * @return array{type: int, query: string}
     */
    private static function formatSearch(?string $query) {
        if (empty($query)) {
            return ['type' => \OmegaUp\DAO\Enum\FilteredStatus::ALL, 'query' => ''];
        }
        $query = preg_replace('/\s+/', ' ', $query);
        $result = [];
        foreach (explode(' ', $query) as $token) {
            if (strlen($token) <= 3) {
                return ['type' => \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE, 'query' => $query];
            }
            $result[] = '+' . urlencode($token) . '*';
        }
        return ['type' => \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT, 'query' => join(
            ' ',
            $result
        )];
    }

    /**
     * @return list<array{name: null|string, username: string, email: null|string, state: null|string, country: null|string, school: null|string}>
     */
    public static function getContestantsInfo(
        int $contestId
    ): array {
        $sql = '
            SELECT
                i.name,
                i.username,
                IF(pi.share_user_information, e.email, NULL) AS email,
                IF(pi.share_user_information, st.name, NULL) AS state,
                IF(pi.share_user_information, cn.name, NULL) AS country,
                IF(pi.share_user_information, sc.name, NULL) AS school
            FROM
                Users u
            INNER JOIN
                Identities i ON u.main_identity_id = i.identity_id
            INNER JOIN
                Emails e ON e.email_id = u.main_email_id
            LEFT JOIN
                States st ON st.state_id = i.state_id AND st.country_id = i.country_id
            LEFT JOIN
                Countries cn ON cn.country_id = i.country_id
            LEFT JOIN
                Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
            LEFT JOIN
                Schools sc ON sc.school_id = isc.school_id
            INNER JOIN
                Problemset_Identities pi ON pi.identity_id = i.identity_id
            INNER JOIN
                Contests c ON c.problemset_id = pi.problemset_id
            WHERE
                c.contest_id = ?;
        ';

        /** @var list<array{country: null|string, email: null|string, name: null|string, school: null|string, state: null|string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$contestId]
        );
    }

    public static function requestsUserInformation(int $contestId): bool {
        $sql = '
            SELECT
                requests_user_information
            FROM
                Problemsets p
            WHERE
                contest_id = ?
            LIMIT 1;
        ';
        /** @var string */
        $requestsUsersInfo = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$contestId]
        );

        return $requestsUsersInfo === 'yes' || $requestsUsersInfo ===  'optional';
    }
}
