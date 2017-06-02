<?php

include_once('base/QualityNominations.dao.base.php');
include_once('base/QualityNominations.vo.base.php');
/** QualityNominations Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominations }.
  * @access public
  *
  */
class QualityNominationsDAO extends QualityNominationsDAOBase {
    public static function getNominationStatusForProblem(Problems $problem, Users $user) {
        $solvedSql = '
          SELECT
            COUNT(r.run_id) > 0 as solved
          FROM
            Runs AS r
          WHERE
            r.problem_id = ? AND r.user_id = ? AND r.verdict = "AC";
        ';

        global $conn;
        $solved = $conn->GetRow($solvedSql, [$problem->problem_id, $user->user_id]);

        $nominatedSql = '
          SELECT
            COUNT(qn.qualitynomination_id) > 0 as nominated
          FROM
            QualityNominations qn
          WHERE
            qn.problem_id = ? AND qn.user_id = ?;
        ';

        $nominated = $conn->GetRow($nominatedSql, [$problem->problem_id, $user->user_id]);
        return array_merge($solved, $nominated);
    }
}
