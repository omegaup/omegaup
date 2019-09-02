<?php

namespace OmegaUp\DAO;

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
class GroupsScoreboards extends \OmegaUp\DAO\Base\GroupsScoreboards {
    /**
     * @return \OmegaUp\DAO\VO\GroupsScoreboards[]
     */
    public static function getByGroup(
        int $groupId
    ) : array {
        $sql = 'SELECT * FROM Groups_Scoreboards WHERE group_id = ?;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$groupId]);

        /** @var \OmegaUp\DAO\VO\GroupsScoreboards[] */
        $groupsScoreboards = [];
        foreach ($rs as $row) {
            array_push($groupsScoreboards, new \OmegaUp\DAO\VO\GroupsScoreboards($row));
        }
        return $groupsScoreboards;
    }

    public static function getByAlias(
        string $alias
    ) : ?\OmegaUp\DAO\VO\GroupsScoreboards {
        $sql = 'SELECT * FROM Groups_Scoreboards WHERE alias = ? LIMIT 1;';
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\GroupsScoreboards($row);
    }
}
