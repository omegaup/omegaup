<?php

require_once('base/Languages.dao.base.php');

/**
 * Languages Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Languages}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class LanguagesDAO extends LanguagesDAOBase {
    final public static function getByName($name) {
        $sql = 'SELECT
                    *
                FROM
                    Languages
                WHERE
                    name = ?
                LIMIT
                    0, 1;';

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Languages($row);
    }
}
