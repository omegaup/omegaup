<?php

namespace OmegaUp\DAO;

/**
 * Roles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Roles}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Roles extends \OmegaUp\DAO\Base\Roles {
    final public static function getByName(string $name): \OmegaUp\DAO\VO\Roles {
        $sql = 'SELECT
                    *
                FROM
                    Roles
                WHERE
                    name = ?';

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
