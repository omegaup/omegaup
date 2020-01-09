<?php

namespace OmegaUp\DAO;

/**
 * Emails Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Emails}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Emails extends \OmegaUp\DAO\Base\Emails {
    /**
     * @return \OmegaUp\DAO\VO\Emails[]
     */
    final public static function getByUserId(int $userId): array {
        $sql = 'SELECT
                    *
                FROM
                    Emails
                WHERE
                    user_id = ?';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$userId]);

        $emails = [];
        foreach ($rs as $row) {
            array_push($emails, new \OmegaUp\DAO\VO\Emails($row));
        }
        return $emails;
    }
}
