<?php

include_once('base/Groups_Scoreboards_Problemsets.dao.base.php');

/**
 * GroupsScoreboardsProblemsets Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets}.
 *
 * @access public
 */
class GroupsScoreboardsProblemsetsDAO extends GroupsScoreboardsProblemsetsDAOBase {
    public static function getByGroupScoreboard($group_scoreboard_id) {
        $sql = 'SELECT * FROM Groups_Scoreboards_Problemsets WHERE group_scoreboard_id = ?;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$group_scoreboard_id]);

        $groupsScoreboardsProblemsets = [];
        foreach ($rs as $row) {
            array_push($groupsScoreboardsProblemsets, new \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets($row));
        }
        return $groupsScoreboardsProblemsets;
    }
}
