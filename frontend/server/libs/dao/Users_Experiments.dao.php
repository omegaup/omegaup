<?php

include('base/Users_Experiments.dao.base.php');

/**
 * UsersExperiments Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UsersExperiments}.
 *
 * @access public
 */
class UsersExperimentsDAO extends UsersExperimentsDAOBase {
    public static function delete(int $userId, string $experiment) : void {
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
                    *
                FROM
                    Users_Experiments
                WHERE
                    user_id = ?;';

        /** @var OmegaUp\DAO\VO\UsersExperiments[] */
        $usersExperiments = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$userId]) as $row) {
            array_push($usersExperiments, new \OmegaUp\DAO\VO\UsersExperiments($row));
        }
        return $usersExperiments;
    }
}
