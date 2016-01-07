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

    final public static function getAllMultipleOrder($pagina = null, $columnas_por_pagina = null, $orden = null) {
        $sql = 'SELECT * from Contests';

        if (! is_null($orden)) {
            $orden = implode(',', array_map(function ($entry) {
                return '`' . $entry['column'] . '`' . ' ' . $entry['type'];

            }, $orden));

            $sql .= ' ORDER BY ' . $orden;
        }
        if (! is_null($pagina)) {
            $sql .= ' LIMIT ' . (( $pagina - 1 )*$columnas_por_pagina) . ',' . $columnas_por_pagina;
        }
        global $conn;
        $rs = $conn->Execute($sql);
        $allData = array();
        foreach ($rs as $foo) {
            $bar = new Contests($foo);
            array_push($allData, $bar);
        }
        return $allData;
    }
}
