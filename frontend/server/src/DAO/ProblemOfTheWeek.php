<?php

namespace OmegaUp\DAO;

/**
 * ProblemOfTheWeek Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemOfTheWeek}.
 *
 * @access public
 */
class ProblemOfTheWeek extends \OmegaUp\DAO\Base\ProblemOfTheWeek {
    /**
     * @return list<\OmegaUp\DAO\VO\ProblemOfTheWeek>
     */
    final public static function getByDifficulty(string $difficulty): array {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\ProblemOfTheWeek::FIELD_NAMES,
            'Problem_Of_The_Week'
        ) . '
                FROM
                    Problem_Of_The_Week
                WHERE
                    difficulty = ?;';

        /** @var list<array{difficulty: string, problem_id: int, problem_of_the_week_id: int, time: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$difficulty]
        );

        $problemsOfTheWeek = [];
        foreach ($rs as $row) {
            $problemsOfTheWeek[] = new \OmegaUp\DAO\VO\ProblemOfTheWeek(
                $row
            );
        }
        return $problemsOfTheWeek;
    }
}
