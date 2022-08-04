<?php

namespace OmegaUp\DAO;

/**
 * States Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\States}.
 * @access public
 * @package docs
 */
class States extends \OmegaUp\DAO\Base\States {
    /**
     * @return list<\OmegaUp\DAO\VO\States>
     */
    final public static function getByCountry(string $countryId): array {
        $fields = join(', ', array_keys(\OmegaUp\DAO\VO\States::FIELD_NAMES));
        $sql = "SELECT
                    {$fields}
                FROM
                    States
                WHERE
                    country_id = ?;";

        /** @var list<array{country_id: string, name: string, state_id: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$countryId]
        );

        $states = [];
        foreach ($rs as $row) {
            $states[] = new \OmegaUp\DAO\VO\States($row);
        }
        return $states;
    }
}
