<?php

require_once('base/Contests.dao.base.php');
require_once('base/Contests.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
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
                                director_id,
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
        $sql = 'SELECT count(*) as Total FROM Contests WHERE public = 0 and (director_id = ?);';
        $params = array($user->getUserId());

        global $conn;
        $rs = $conn->GetRow($sql, $params);

        if (!array_key_exists('Total', $rs)) {
            return 0;
        }

        return $rs['Total'];
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
     * Regresa todos los concursos que un usuario puede ver.
     *
     * Explicación:
     *
     * La estructura de este query optimiza el uso de indíces en mysql, en particular idx_contest_public_director_id
     * y idx_contest_public.
     *
     * El primer SELECT transforma las columnas a como las espera la API.
     * Luego:
     *
     * Todos los concursos privados donde el usuadio fue el creador (director_id)
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
     * @param int $columnas_por_pagina
     * @return array
     */
    final public static function getAllContestsForUser($user_id, $pagina = 1, $renglones_por_pagina = 1000) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;

        $sql = "
                (
                     SELECT
                        $columns
                     FROM
                        Contests
                     WHERE
                        Contests.public = 0 AND Contests.director_id = ? AND interview = 0
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
                         Contests.public = 0 AND Contests_Users.user_id = ?
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
                         User_Roles.contest_id = Contests.contest_id

                     WHERE
                         Contests.public = 0 AND
                         User_Roles.user_id = ? AND
                         (User_Roles.role_id = 2 or User_Roles.role_id = 1)
                 )

                 UNION
                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     JOIN
                         Group_Roles ON Contests.contest_id = Group_Roles.contest_id
                     JOIN
                         Groups_Users ON Groups_Users.group_id = Group_Roles.group_id
                     WHERE
                         Contests.public = 0 AND
                         Groups_Users.user_id = ? AND
                         (Group_Roles.role_id = 2 or Group_Roles.role_id = 1)
                 )

                 UNION

                 (
                     SELECT
                         $columns
                     FROM
                         Contests
                     WHERE
                         Public = 1
                 )

                 ORDER BY
                     CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC,
                     `recommended` DESC,
                     `original_finish_time` DESC
                 LIMIT ?, ?
                ";

        $params = array($user_id, $user_id, $user_id, $user_id, $offset, $renglones_por_pagina);

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $allData = array();

        foreach ($rs as $foo) {
            $bar = new Contests($foo);
            array_push($allData, $bar);
        }

        return $allData;
    }

    final public static function getAllPublicContests($pagina = 1, $renglones_por_pagina = 1000) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;

        $sql = "
               SELECT
                    $columns
                FROM
                    Contests
                WHERE
                    Public = 1
                    and interview = 0
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

    final public static function getAllContests($pagina = 1, $renglones_por_pagina = 1000) {
        $offset = ($pagina - 1) * $renglones_por_pagina;

        $columns = ContestsDAO::$getContestsColumns;

        $sql = "
                SELECT
                    $columns
                FROM
                    Contests
                WHERE
                    interview = 0
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
