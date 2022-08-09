<?php

namespace OmegaUp\DAO;

/**
 * Languages Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Languages}.
 * @access public
 * @package docs
 */
class Languages extends \OmegaUp\DAO\Base\Languages {
    final public static function getByName(
        string $name
    ): ?\OmegaUp\DAO\VO\Languages {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Languages::FIELD_NAMES,
            'Languages'
        ) . '
                FROM
                    Languages
                WHERE
                    name = ?
                LIMIT
                    0, 1;';

        /** @var array{country_id: null|string, language_id: int, name: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Languages($row);
    }
}
