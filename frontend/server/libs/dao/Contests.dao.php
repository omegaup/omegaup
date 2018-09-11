<?php

require_once('base/Contests.dao.base.php');
require_once('base/Contests.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */

/**
 * Base class for the ActiveStatus and RecommendedStatus enums below.
 *
 * It handles validation of input values, constants by name or by value,
 * and getting the corresponding SQL snippet for them.
 */
class StatusBase {
    /**
     * @param mixed $status Numeric or named constant.
     * @return int value on success, null otherwise.
     */
    public static function getIntValue($status) {
        $cache = self::getConstCache(get_called_class());
        if (is_numeric($status)) {
            // $status may be a string, force it to an int.
            $status = intval($status);
            if ($cache['min'] <= $status && $status <= $cache['max']) {
                return $status;
            }
        } elseif (is_string($status)) {
            if (in_array($status, $cache['constants'])) {
                return $cache['constants'][$status];
            }
        }
        return;
    }

    /**
     * @param int $status
     * @return string SQL snippet.
     */
    public static function sql($status) {
        $class = get_called_class();
        $cache = self::getConstCache($class);
        // This should've been validated before, but lets be paranoid anyway.
        $status = max($cache['min'], min($cache['max'], $status));
        return $class::$SQL_FOR_STATUS[$status];
    }

    /**
     * @param string $className The derived class name.
     * @return array with 'constants', 'min' and 'max' fields.
     */
    private static function getConstCache($className) {
        if (!isset(self::$constCache[$className])) {
            $reflection = new ReflectionClass($className);
            $constants = $reflection->getConstants();
            $values = array_values($constants);
            self::$constCache[$className] = [
                'constants' => $constants,
                'min' => min($values),
                'max' => max($values),
            ];
        }
        return self::$constCache[$className];
    }

    private static $constCache = [];
}

class ActiveStatus extends StatusBase {
    const ALL = 0;
    const ACTIVE = 1;
    const PAST = 2;
    const FUTURE = 3;

    public static $SQL_FOR_STATUS = [
        'TRUE',
        'finish_time >= NOW() AND start_time <= NOW()',
        'finish_time < NOW()',
        'start_time > NOW()',
    ];
}

class RecommendedStatus extends StatusBase {
    const ALL = 0;
    const RECOMMENDED = 1;
    const NOT_RECOMMENDED = 2;

    public static $SQL_FOR_STATUS = [
        'TRUE',
        'recommended = 1',
        'recommended = 0',
    ];
}

class FilteredStatus extends StatusBase {
    const ALL = 0;
    const SIMPLE = 1;
    const FULLTEXT = 2;

    public static $SQL_FOR_STATUS = [
        'TRUE',
        '(title LIKE CONCAT(\'%\', ?, \'%\') OR description LIKE CONCAT(\'%\', ?, \'%\'))',
        'MATCH(title, description) AGAINST(? IN BOOLEAN MODE)',
    ];
}

class ParticipatingStatus extends StatusBase {
    const NO = 0;
    const YES = 1;
}

class PublicStatus extends StatusBase {
    const NO = 0;
    const YES = 1;
}

/** Contests Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Contests }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class ContestsDAO extends ContestsDAOBase {
    private static $getContestsColumns = '
                                Contests.contest_id,
                                Contests.problemset_id,
                                title,
                                description,
                                finish_time as original_finish_time,
                                UNIX_TIMESTAMP (start_time) as start_time,
                                UNIX_TIMESTAMP (finish_time) as finish_time,
                                admission_mode,
                                alias,
                                recommended,
                                window_length,
                                UNIX_TIMESTAMP (last_updated) as last_updated,
                                rerun_id
                                ';

    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Contests WHERE alias = ? LIMIT 1;';
        $params = [$alias];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }

        $contest = new Contests($rs);

        return $contest;
    }

    final public static function getByTitle($title) {
        $sql = 'SELECT * FROM Contests WHERE title = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$title]);

        $contests = [];
        foreach ($rs as $row) {
            array_push($contests, new Contests($row));
        }
        return $contests;
    }

    final public static function getByAliasWithExtraInformation($alias) {
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

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return $rs;
    }

    final public static function getByProblemset($problemset_id) {
        $sql = 'SELECT * FROM Contests WHERE problemset_id = ? LIMIT 0, 1;';
        global $conn;
        $row = $conn->GetRow($sql, [$problemset_id]);
        if (count($row) == 0) {
            return null;
        }

        return new Contests($row);
    }

    public static function getPrivateContestsCount(Users $user) {
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

        global $conn;
        $rs = $conn->GetRow($sql, $params);

        if (!array_key_exists('total', $rs)) {
            return 0;
        }

        return $rs['total'];
    }

    public static function hasStarted(Contests $contest) {
        return Time::get() >= strtotime($contest->start_time);
    }

    public static function hasFinished(Contests $contest) {
        return Time::get() >= strtotime($contest->finish_time);
    }

    public static function getContestsParticipated($identity_id) {
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
            WHERE c.contest_id IN (
                SELECT DISTINCT
                    c2.contest_id
                FROM
                    Runs r
                INNER JOIN
                    Contests c2
                ON
                    c2.problemset_id = r.problemset_id
                WHERE
                    r.identity_id = ? AND r.type= \'normal\' AND r.problemset_id IS NOT NULL
            )
            ORDER BY
                contest_id DESC;';
        $params = [$identity_id];

        global $conn;
        return $conn->GetAll($sql, $params);
    }

    /**
     * Returns all contests that an identity can manage.
     */
    final public static function getAllContestsAdminedByIdentity(
        $identity_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*
            FROM
                Contests c
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
            $identity_id,
            Authorization::ADMIN_ROLE,
            $identity_id,
            Authorization::ADMIN_ROLE,
            $identity_id,
            $offset,
            $pageSize,
        ];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $contests = [];
        foreach ($rs as $row) {
            array_push($contests, new Contests($row));
        }
        return $contests;
    }

    /**
     * Returns all contests owned by a user.
     */
    final public static function getAllContestsOwnedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*,
                p.scoreboard_url,
                p.scoreboard_url_admin
            FROM
                Contests c
            INNER JOIN
                ACLs a ON a.acl_id = c.acl_id
            INNER JOIN
                Problemsets p ON p.problemset_id = c.problemset_id
            WHERE
                a.owner_id = ?
            ORDER BY
                c.contest_id DESC
            LIMIT ?, ?;';
        $params = [
            $user_id,
            $offset,
            $pageSize,
        ];

        global $conn;
        return $conn->GetAll($sql, $params);
    }

    /**
     * Returns all contests where a user is participating in.
     */
    final public static function getContestsParticipating(
        $identity_id,
        $page = 1,
        $pageSize = 1000,
        $query = null
    ) {
        $end_check = ActiveStatus::sql(ActiveStatus::ACTIVE);
        $recommended_check = RecommendedStatus::sql(ActiveStatus::ALL);
        $columns = ContestsDAO::$getContestsColumns;
        $offset = ($page - 1) * $pageSize;
        $filter = self::formatSearch($query);
        $query_check = FilteredStatus::sql($filter['type']);

        $sql = "
            SELECT
                $columns,
                Problemsets.scoreboard_url,
                Problemsets.scoreboard_url_admin
            FROM
                Contests
            INNER JOIN
                Problemset_Identities
            ON
                Contests.problemset_id = Problemset_Identities.problemset_id
            INNER JOIN
                Problemsets
            ON
                Problemsets.problemset_id = Contests.problemset_id
            WHERE
                Problemset_Identities.identity_id = ? AND
                $recommended_check  AND $end_check AND $query_check
            ORDER BY
                recommended DESC,
                finish_time DESC
            LIMIT ?, ?;";
        global $conn;
        $params[] = $identity_id;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = $offset;
        $params[] = $pageSize;

        return $conn->GetAll($sql, $params);
    }

    /**
     * Returns all recent public contests.
     */
    final public static function getRecentPublicContests(
        $user_id,
        $page = 1,
        $pageSize = 1000,
        $query = null
    ) {
        $end_check = ActiveStatus::sql(ActiveStatus::ACTIVE);
        $recommended_check = RecommendedStatus::sql(ActiveStatus::ALL);
        $columns = ContestsDAO::$getContestsColumns;
        $offset = ($page - 1) * $pageSize;
        $filter = self::formatSearch($query);
        $query_check = FilteredStatus::sql($filter['type']);

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

        global $conn;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = $offset;
        $params[] = $pageSize;

        return $conn->GetAll($sql, $params);
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
     *
     * @global type $conn
     * @param int $identity_id
     * @param int $pagina
     * @param int $renglones_por_pagina
     * @param ActiveStatus $activos
     * @param RecommendedStatus $recomendados
     * @param FilteredStatus $query
     * @return array
     */
    final public static function getAllContestsForIdentity(
        $identity_id,
        $pagina = 1,
        $renglones_por_pagina = 1000,
        $activos = ActiveStatus::ALL,
        $recomendados = RecommendedStatus::ALL,
        $query = null
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;
        $end_check = ActiveStatus::sql($activos);
        $recommended_check = RecommendedStatus::sql($recomendados);
        $filter = self::formatSearch($query);
        $query_check = FilteredStatus::sql($filter['type']);

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
        $params[] = $identity_id;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION
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
        $params[] = $identity_id;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION
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
        $params[] = $identity_id;
        $params[] = Authorization::ADMIN_ROLE;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        $sql .= "
                 UNION
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
        $params[] = $identity_id;
        $params[] = Authorization::ADMIN_ROLE;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $sql .= "
                 UNION
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
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = $offset;
        $params[] = $renglones_por_pagina;
        global $conn;
        return $conn->GetAll($sql, $params);
    }

    final public static function getAllPublicContests(
        $pagina = 1,
        $renglones_por_pagina = 1000,
        $activos = ActiveStatus::ALL,
        $recomendados = RecommendedStatus::ALL,
        $query = null
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;
        $end_check = ActiveStatus::sql($activos);
        $recommended_check = RecommendedStatus::sql($recomendados);
        $filter = self::formatSearch($query);
        $query_check = FilteredStatus::sql($filter['type']);

        $columns = ContestsDAO::$getContestsColumns;

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

        global $conn;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = $offset;
        $params[] = $renglones_por_pagina;
        return $conn->GetAll($sql, $params);
    }

    final public static function getAllContests(
        $pagina = 1,
        $renglones_por_pagina = 1000,
        $activos = ActiveStatus::ALL,
        $recomendados = RecommendedStatus::ALL,
        $query = null
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;
        $end_check = ActiveStatus::sql($activos);
        $recommended_check = RecommendedStatus::sql($recomendados);
        $filter = self::formatSearch($query);
        $query_check = FilteredStatus::sql($filter['type']);

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

        global $conn;
        if ($filter['type'] === FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }
        $params[] = $offset;
        $params[] = $renglones_por_pagina;
        return $conn->GetAll($sql, $params);
    }

    public static function getContestForProblemset($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }

        try {
            $contest = ContestsDAO::getByProblemset($problemset_id);
            if (!empty($contest)) {
                return $contest;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return null;
    }

    public static function getNeedsInformation($problemset_id) {
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

        global $conn;
        $params = [$problemset_id];

        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            throw new NotFoundException('problemsetNotFound');
        }
        return [
            'needs_basic_information' => $rs['needs_basic_information'] == '1',
            'requests_user_information' => $rs['requests_user_information']
        ];
    }

    /**
     * Generate alias of virtual contest / ghost mode
     *
     * @param Contests $contest
     * @param Users $user
     * @return string of unique virtual contest alias
     */
    public static function generateAlias(Contests $contest) {
        // Virtual contest alias format (alias-virtual-random)
        $alias = $contest->alias;

        return substr($alias, 0, 20) . '-virtual-' . SecurityTools::randomString(3);
    }

    /**
     * Check if contest is virtual contest
     * @param Contest $contest
     * @return boolean
     */
    public static function isVirtual(Contests $contest) {
        return $contest->rerun_id != 0;
    }

    /**
     * @param $query
     * @return Array [type, query]
     */
    private static function formatSearch($query) {
        if (empty($query)) {
            return ['type' => FilteredStatus::ALL];
        }
        $query = preg_replace('/\s+/', ' ', $query);
        $result = [];
        foreach (explode(' ', $query) as $token) {
            if (strlen($token) <= 3) {
                return ['type' => FilteredStatus::SIMPLE, 'query' => $query];
            }
            $result[] = '+' . urlencode($token) . '*';
        }
        return ['type' => FilteredStatus::FULLTEXT, 'query' => join(' ', $result)];
    }
}
