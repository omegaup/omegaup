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
    public static function delete($user_id, $experiment) {
        $sql = '
            DELETE FROM
                Users_Experiments
            WHERE
                user_id = ? AND experiment = ?;';
        $params = [
            $user_id,
            $experiment,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
    }

    final public static function getByUserId($user_id) {
        $sql = 'SELECT
                    *
                FROM
                    Users_Experiments
                WHERE
                    user_id = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$user_id]);

        $users_experiments = [];
        foreach ($rs as $row) {
            array_push($users_experiments, new \OmegaUp\DAO\VO\UsersExperiments($row));
        }
        return $users_experiments;
    }
}
