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
        $sql = 'SELECT
            COUNT(r.run_id) > 0 as solved,
            COUNT(qn.quality_nomination_id) > 0 as nominated
        FROM
            QualityNominations qn
        INNER JOIN
            Runs AS r
        ON
            r.user_id = qn.user_id AND r.verdict = "AC"
        WHERE
            qn.problem_id = ? AND qn.user_id = ?;';

        global $conn;
        return $conn->GetRow($sql, [$problem->problem_id, $user->user_id]);
    }
}
