<?php

namespace OmegaUp\DAO;

/**
 * Problems_Forfeited Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsForfeited}.
 * @access public
 * @package docs
 */
class ProblemsForfeited extends \OmegaUp\DAO\Base\ProblemsForfeited {
    public static function getProblemsForfeitedCountInDay(\OmegaUp\DAO\VO\Users $user): int {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Problems_Forfeited
                WHERE
                    user_id = ?
                AND
                    DATE(forfeited_date) = CURDATE()';
                ;
        $args = [$user->user_id];
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    public static function isProblemForfeited(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Problems_Forfeited
                WHERE
                    problem_id = ? AND user_id = ?;';
        $args = [$problem->problem_id, $identity->user_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $args);
        return $count > 0;
    }
}
