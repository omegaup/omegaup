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
            self::$constCache[$className] = array(
                'constants' => $constants,
                'min' => min($values),
                'max' => max($values),
            );
        }
        return self::$constCache[$className];
    }

    private static $constCache = array();
}

class ActiveStatus extends StatusBase {
    const ALL = 0;
    const ACTIVE = 1;
    const PAST = 2;

    public static $SQL_FOR_STATUS = array(
        'TRUE',
        'finish_time > NOW()',
        'finish_time <= NOW()',
    );
}

class RecommendedStatus extends StatusBase {
    const ALL = 0;
    const RECOMMENDED = 1;
    const NOT_RECOMMENDED = 2;

    public static $SQL_FOR_STATUS = array(
        'TRUE',
        'recommended = 1',
        'recommended = 0',
    );
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
class ContestsDAO extends ContestsDAOBase
{
    private static $getContestsColumns = '
                                Contests.contest_id,
                                title,
                                description,
                                finish_time as original_finish_time,
                                UNIX_TIMESTAMP (start_time) as start_time,
                                UNIX_TIMESTAMP (finish_time) as finish_time,
                                public,
                                alias,
                                recommended,
                                window_length
                                ';

    final public static function getByAlias($alias)
    {
        $sql = 'SELECT * FROM Contests WHERE (alias = ? ) LIMIT 1;';
        $params = array(  $alias );

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }

        $contest = new Contests($rs);

        return $contest;
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
            public = 0 and a.owner_id = ?;';
        $params = array($user->user_id);

        global $conn;
        $rs = $conn->GetRow($sql, $params);

        if (!array_key_exists('total', $rs)) {
            return 0;
        }

        return $rs['total'];
    }

    public static function hasStarted(Contests $contest) {
        return time() >= strtotime($contest->start_time);
    }

    public static function hasFinished(Contests $contest) {
        return time() >= strtotime($contest->finish_time);
    }

    public static function isInsideContest(Contests $contest, $user_id) {
        if (time() > strtotime($contest->finish_time) ||
            time() < strtotime($contest->start_time)) {
            return false;
        }
        if (is_null($contest->window_length)) {
            return true;
        }
        $contest_user = ContestsUsersDAO::getByPK($user_id, $contest->contest_id);
        $first_access_time = $contest_user->access_time;

        return time() <= strtotime($first_access_time) + $contest->window_length * 60;
    }

    public static function getContestsParticipated($user_id) {
        $sql = 'SELECT * from Contests WHERE contest_id IN ('
                    . 'SELECT DISTINCT contest_id FROM Runs WHERE user_id = ? AND test = 0 AND contest_id IS NOT NULL'
               . ')'
               . 'ORDER BY contest_id DESC';
        $params = array($user_id);

        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = array();
        foreach ($rs as $foo) {
            $bar =  new Contests($foo);
            array_push($ar, $bar);
        }
        return $ar;
    }

    /**
     * Returns all contests that a user can manage.
     */
    final public static function getAllContestsAdminedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*
            FROM
                Contests c
            WHERE
                c.acl_id IN (
                    SELECT
                        ur.acl_id
                    FROM
                        User_Roles ur
                    WHERE
                        ur.role_id = ? AND ur.user_id = ?
                UNION DISTINCT
                    SELECT
                        a.acl_id
                    FROM
                        ACLs a
                    WHERE
                        a.owner_id = ?
                UNION DISTINCT
                    SELECT
                        gr.acl_id
                    FROM
                        Group_Roles gr
                    INNER JOIN
                        Groups_Users gu
                    ON
                        gu.group_id = gr.group_id
                    WHERE
                        gr.role_id = ? AND gu.user_id = ?
                )
            ORDER BY
                c.contest_id DESC
            LIMIT ?, ?;';

        $params = array(
            Authorization::ADMIN_ROLE,
            $user_id,
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            $offset,
            $pageSize,
        );

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $contests = array();
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
                c.*
            FROM
                Contests c
            INNER JOIN
                ACLs a ON a.acl_id = c.acl_id
            WHERE
                a.owner_id = ?
            ORDER BY
                c.contest_id DESC
            LIMIT ?, ?;';
        $params = array(
            $user_id,
            $offset,
            $pageSize,
        );

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $contests = array();
        foreach ($rs as $row) {
            array_push($contests, new Contests($row));
        }
        return $contests;
    }

    /**
     * Regresa todos los concursos que un usuario puede ver.
     *
     * Explicación:
     *
     * La estructura de este query optimiza el uso de indíces en mysql.
     *
     * El primer SELECT transforma las columnas a como las espera la API.
     * Luego:
     *
     * Todos los concursos privados donde el usuario fue el creador
     * UNION
     * Todos los concursos privados a los que el usuario ha sido invitado
     * UNION
     * Todos los concursos privados a los que el usuario es ADMIN
     * UNION
     * Todos los concursos privados donde el usuario pertenece a un grupo que es ADMIN del concurso
     * UNION
     * Todos los concursos públicos.
     *
     *
     * @global type $conn
     * @param int $user_id
     * @param int $pagina
     * @param int $renglones_por_pagina
     * @param ActiveStatus $activos
     * @param RecommendedStatus $recomendados
     * @return array
     */
    final public static function getAllContestsForUser(
        $user_id,
        $pagina = 1,
        $renglones_por_pagina = 1000,
        $activos = ActiveStatus::ALL,
        $recomendados = RecommendedStatus::ALL
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;
        $end_check = ActiveStatus::sql($activos);
        $recommended_check = RecommendedStatus::sql($recomendados);
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
                    WHERE
                        Contests.public = 0 AND ACLs.owner_id = ? AND
                        $recommended_check AND $end_check
                 )
                 UNION
                 (
                    SELECT
                        $columns
                    FROM
                        Contests
                    JOIN
                        Contests_Users
                    ON
                        Contests.contest_id = Contests_Users.contest_id
                    WHERE
                        Contests.public = 0 AND Contests_Users.user_id = ? AND
                        $recommended_check AND $end_check
                 )
                 UNION
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     JOIN
                         User_Roles
                     ON
                         User_Roles.acl_id = Contests.acl_id
                     WHERE
                         Contests.public = 0 AND
                         User_Roles.user_id = ? AND
                         User_Roles.role_id = ? AND
                         $recommended_check AND $end_check
                 )
                 UNION
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     JOIN
                         Group_Roles ON Contests.acl_id = Group_Roles.acl_id
                     JOIN
                         Groups_Users ON Groups_Users.group_id = Group_Roles.group_id
                     WHERE
                         Contests.public = 0 AND
                         Groups_Users.user_id = ? AND
                         Group_Roles.role_id = ? AND
                         $recommended_check AND $end_check
                 )
                 UNION
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     WHERE
                         public = 1 AND $recommended_check AND $end_check
                 )
                 ORDER BY
                     CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                     `recommended` DESC,
                     `original_finish_time` DESC
                 LIMIT ?, ?
                ";

        $params = array(
            $user_id,
            $user_id,
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            Authorization::ADMIN_ROLE,
            $offset,
            $renglones_por_pagina,
        );

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $allData = array();

        foreach ($rs as $foo) {
            $bar = new Contests($foo);
            array_push($allData, $bar);
        }

        return $allData;
    }

    final public static function getAllPublicContests(
        $pagina = 1,
        $renglones_por_pagina = 1000,
        $activos = ActiveStatus::ALL,
        $recomendados = RecommendedStatus::ALL
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;
        $end_check = ActiveStatus::sql($activos);
        $recommended_check = RecommendedStatus::sql($recomendados);

        $columns = ContestsDAO::$getContestsColumns;

        $sql = "
               SELECT
                    $columns
                FROM
                    Contests
                WHERE
                    Public = 1
                AND $recommended_check
                AND $end_check
                ORDER BY
                    CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                    `recommended` DESC,
                    `original_finish_time` DESC
                LIMIT ?, ?
                ";

        global $conn;
        $params = array($offset, $renglones_por_pagina);
        $rs = $conn->Execute($sql, $params);

        $allData = array();

        foreach ($rs as $foo) {
            $bar = new Contests($foo);
            array_push($allData, $bar);
        }

        return $allData;
    }

    final public static function getAllContests(
        $pagina = 1,
        $renglones_por_pagina = 1000,
        $activos = ActiveStatus::ALL,
        $recomendados = RecommendedStatus::ALL
    ) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;
        $end_check = ActiveStatus::sql($activos);
        $recommended_check = RecommendedStatus::sql($recomendados);

        $sql = "
                SELECT
                    $columns
                FROM
                    Contests
                WHERE $recommended_check AND $end_check
                ORDER BY
                    CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                    `recommended` DESC,
                    `original_finish_time` DESC
                LIMIT ?, ?
                ";

        global $conn;
        $params = array($offset, $renglones_por_pagina);
        $rs = $conn->Execute($sql, $params);

        $allData = array();

        foreach ($rs as $foo) {
            $bar = new Contests($foo);
            array_push($allData, $bar);
        }

        return $allData;
    }
}
