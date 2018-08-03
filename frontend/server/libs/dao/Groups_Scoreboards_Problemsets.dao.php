<?php

include_once('base/Groups_Scoreboards_Problemsets.dao.base.php');
include_once('base/Groups_Scoreboards_Problemsets.vo.base.php');
/** GroupsScoreboardsProblemsets Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsScoreboardsProblemsets }.
  * @access public
  *
  */
class GroupsScoreboardsProblemsetsDAO extends GroupsScoreboardsProblemsetsDAOBase {
    public static function getByGroupScoreboard($group_scoreboard_id) {
        $sql = 'SELECT * FROM Groups_Scoreboards_Problemsets WHERE group_scoreboard_id = ?;';
        global $conn;
        $rs = $conn->Execute($sql, [$group_scoreboard_id]);

        $groupsScoreboardsProblemsets = [];
        foreach ($rs as $row) {
            array_push($groupsScoreboardsProblemsets, new GroupsScoreboardsProblemsets($row));
        }
        return $groupsScoreboardsProblemsets;
    }
}
