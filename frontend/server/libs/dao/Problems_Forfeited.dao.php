<?php

require_once('base/Problems_Forfeited.dao.base.php');
require_once('base/Problems_Forfeited.vo.base.php');

/** Problems_Forfeited Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link roblemsForfeited }.
 * @author carlosabcs
 * @access public
 * @package docs
 *
 */
class ProblemsForfeitedDAO extends ProblemsForfeitedDAOBase {
    public static function getProblemsForfeitedCount(Users $user): int {
        global $conn;
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Problems_Forfeited
                WHERE
                    user_id = ?;';
        $args = [$user->user_id];
        return $conn->getOne($sql, $args);
    }

    public static function isProblemForfeited(
        Problems $problem,
        Identities $identity
    ) : bool {
        global $conn;
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Problems_Forfeited
                WHERE
                    problem_id = ? AND user_id = ?;';
        $args = [$problem->problem_id, $identity->user_id];
        return $conn->GetOne($sql, $args) > 0;
    }
}
