<?php

namespace OmegaUp\DAO;

/**
 * States Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\States}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class States extends \OmegaUp\DAO\Base\States {
    final public static function getByCountry($countryId) {
        $sql = 'SELECT
                    *
                FROM
                    States
                WHERE
                    country_id = ?;';

        /** @var list<array{country_id: string, name: string, state_id: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$countryId]
        );

        $states = [];
        foreach ($rs as $row) {
            array_push($states, new \OmegaUp\DAO\VO\States($row));
        }
        return $states;
    }
}
