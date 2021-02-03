<?php

namespace OmegaUp\DAO;

/**
 * ProblemsetProblemOpened Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetProblemOpened}.
 *
 * @access public
 */
class ProblemsetProblemOpened extends \OmegaUp\DAO\Base\ProblemsetProblemOpened {
    final public static function removeProblemOpenedFromProblemset(
        int $problemsetId
    ): int {
        $sql = '
            DELETE FROM
                `Problemset_Problem_Opened`
            WHERE
                `problemset_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problemsetId]);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
