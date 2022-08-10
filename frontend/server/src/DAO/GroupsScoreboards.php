<?php

namespace OmegaUp\DAO;

/**
 * GroupsScoreboards Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupsScoreboards}.
 * @access public
 * @package docs
 */
class GroupsScoreboards extends \OmegaUp\DAO\Base\GroupsScoreboards {
    /**
     * @return list<\OmegaUp\DAO\VO\GroupsScoreboards>
     */
    public static function getByGroup(
        int $groupId
    ): array {
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\GroupsScoreboards::FIELD_NAMES,
            'Groups_Scoreboards'
        ) . ' FROM Groups_Scoreboards WHERE group_id = ?;';
        /** @var list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, group_scoreboard_id: int, name: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$groupId]);

        $groupsScoreboards = [];
        foreach ($rs as $row) {
            $groupsScoreboards[] = new \OmegaUp\DAO\VO\GroupsScoreboards(
                $row
            );
        }
        return $groupsScoreboards;
    }

    public static function getByAlias(
        string $alias
    ): ?\OmegaUp\DAO\VO\GroupsScoreboards {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\GroupsScoreboards::FIELD_NAMES,
            'Groups_Scoreboards',
        );
        $sql = "SELECT {$fields} FROM Groups_Scoreboards WHERE alias = ? LIMIT 1;";
        /** @var array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, group_scoreboard_id: int, name: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\GroupsScoreboards($row);
    }
}
