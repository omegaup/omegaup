<?php

namespace OmegaUp\DAO;

/**
 * UsersExperiments Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UsersExperiments}.
 *
 * @access public
 */
class UsersExperiments extends \OmegaUp\DAO\Base\UsersExperiments {
    public static function delete(int $userId, string $experiment): void {
        $sql = '
            DELETE FROM
                Users_Experiments
            WHERE
                user_id = ? AND experiment = ?;';
        $params = [
            $userId,
            $experiment,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
    }

    /**
     * @param int $userId
     * @return \OmegaUp\DAO\VO\UsersExperiments[]
     */
    final public static function getByUserId(int $userId) {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\UsersExperiments::FIELD_NAMES,
            'Users_Experiments'
        ) . '
                FROM
                    Users_Experiments
                WHERE
                    user_id = ?;';

        /** @var \OmegaUp\DAO\VO\UsersExperiments[] */
        $usersExperiments = [];
        /** @var array{experiment: string, user_id: int} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$userId]
            ) as $row
        ) {
            array_push(
                $usersExperiments,
                new \OmegaUp\DAO\VO\UsersExperiments(
                    $row
                )
            );
        }
        return $usersExperiments;
    }
}
