<?php

namespace OmegaUp\DAO;

/**
 * ProblemsLanguages Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsLanguages}.
 * @access public
 * @package docs
 */
class ProblemsLanguages extends \OmegaUp\DAO\Base\ProblemsLanguages {
    final public static function deleteProblemLanguages(
        \OmegaUp\DAO\VO\ProblemsLanguages $problemsLanguages
    ): int {
        $sql = 'DELETE FROM `Problems_Languages` WHERE problem_id = ?;';
        $params = [$problemsLanguages->problem_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return list<\OmegaUp\DAO\VO\ProblemsLanguages>
     */
    final public static function getByProblemId(int $problemId): array {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\ProblemsLanguages::FIELD_NAMES,
            'Problems_Languages'
        ) . '
                FROM
                    Problems_Languages
                WHERE
                    problem_id = ?;';

        /** @var list<array{language_id: int, problem_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemId]
        );

        $problemsLanguages = [];
        foreach ($rs as $row) {
            $problemsLanguages[] = new \OmegaUp\DAO\VO\ProblemsLanguages(
                $row
            );
        }
        return $problemsLanguages;
    }
}
