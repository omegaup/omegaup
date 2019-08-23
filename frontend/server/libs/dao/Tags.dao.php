<?php

require_once('base/Tags.dao.base.php');

/**
 * Tags Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Tags}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class TagsDAO extends TagsDAOBase {
    final public static function getByName(string $name) : ?\OmegaUp\DAO\VO\Tags {
        $sql = 'SELECT * FROM Tags WHERE name = ? LIMIT 1;';

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Tags($row);
    }

    public static function findByName(string $name) : array {
        $sql = "SELECT * FROM Tags WHERE name LIKE CONCAT('%', ?, '%') LIMIT 100";
        $args = [$name];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
        $result = [];
        foreach ($rs as $row) {
            array_push($result, new \OmegaUp\DAO\VO\Tags($row));
        }
        return $result;
    }
}
