<?php

/**
 * ProblemsLanguages Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsLanguages}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class ProblemsLanguagesDAO extends \OmegaUp\DAO\Base\ProblemsLanguages {
    final public static function deleteProblemLanguages(\OmegaUp\DAO\VO\ProblemsLanguages $problems_languages) {
        $sql = 'DELETE FROM `Problems_Languages` WHERE problem_id = ?;';
        $params = [$problems_languages->problem_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    final public static function getByProblemId($problemId) {
        $sql = 'SELECT
                    *
                FROM
                    Problems_Languages
                WHERE
                    problem_id = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemId]);

        $problemsLanguages = [];
        foreach ($rs as $row) {
            array_push($problemsLanguages, new \OmegaUp\DAO\VO\ProblemsLanguages($row));
        }
        return $problemsLanguages;
    }
}
