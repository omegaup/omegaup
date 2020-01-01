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
    final public static function getByDificulty($difficulty) {
        $sql = 'SELECT
                    *
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
            array_push(
                $problemsOfTheWeek,
                new \OmegaUp\DAO\VO\ProblemOfTheWeek(
                    $row
                )
            );
        }
        return $problemsOfTheWeek;
    }
}
