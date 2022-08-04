<?php

namespace OmegaUp\DAO;

/**
 * Roles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Roles}.
 * @access public
 * @package docs
 */
class Roles extends \OmegaUp\DAO\Base\Roles {
    final public static function getByName(string $name): \OmegaUp\DAO\VO\Roles {
        $fields = join(', ', array_keys(\OmegaUp\DAO\VO\Roles::FIELD_NAMES));
        $sql = "SELECT
                    {$fields}
                FROM
                    Roles
                WHERE
                    name = ?";

        /** @var array{description: string, name: string, role_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'role'
            );
        }
        return new \OmegaUp\DAO\VO\Roles($row);
    }
}
