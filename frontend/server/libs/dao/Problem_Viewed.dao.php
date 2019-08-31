<?php

/**
 * ProblemViewed Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemViewed}.
 *
 * @access public
 */
class ProblemViewedDAO extends \OmegaUp\DAO\Base\ProblemViewed {
    public static function MarkProblemViewed($identity_id, $problem_id) {
        $sql = 'INSERT IGNORE Problem_Viewed (identity_id, problem_id) VALUES (?, ?);';
        $values = [$identity_id, $problem_id];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $values);
    }
}
