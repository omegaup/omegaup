<?php

require_once('base/Groups_Scoreboards.dao.base.php');

/**
 * GroupsScoreboards Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupsScoreboards}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class GroupsScoreboardsDAO extends GroupsScoreboardsDAOBase {
    public static function getByGroup($group_id) {
        $sql = 'SELECT * FROM Groups_Scoreboards WHERE group_id = ?;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$group_id]);

        $groupsScoreboards = [];
        foreach ($rs as $row) {
            array_push($groupsScoreboards, new \OmegaUp\DAO\VO\GroupsScoreboards($row));
        }
        return $groupsScoreboards;
    }

    public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Groups_Scoreboards WHERE alias = ? LIMIT 1;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\GroupsScoreboards($rs);
    }
}
