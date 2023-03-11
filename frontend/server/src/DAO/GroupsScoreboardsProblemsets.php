<?php

namespace OmegaUp\DAO;

/**
 * GroupsScoreboardsProblemsets Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets}.
 *
 * @access public
 */
class GroupsScoreboardsProblemsets extends \OmegaUp\DAO\Base\GroupsScoreboardsProblemsets {
    /**
     * @return \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets[]
     */
    public static function getByGroupScoreboard(
        int $groupScoreboardId
    ): array {
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets::FIELD_NAMES,
            'Groups_Scoreboards_Problemsets'
        ) . ' FROM Groups_Scoreboards_Problemsets WHERE group_scoreboard_id = ?;';
        /** @var list<array{group_scoreboard_id: int, only_ac: bool, problemset_id: int, weight: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$groupScoreboardId]
        );

        /** @var \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets[] */
        $groupsScoreboardsProblemsets = [];
        foreach ($rs as $row) {
            array_push(
                $groupsScoreboardsProblemsets,
                new \OmegaUp\DAO\VO\GroupsScoreboardsProblemsets(
                    $row
                )
            );
        }
        return $groupsScoreboardsProblemsets;
    }
}
