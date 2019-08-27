<?php

require_once('base/Emails.dao.base.php');

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
class EmailsDAO extends EmailsDAOBase {
    final public static function getByUserId($user_id) {
        $sql = 'SELECT
                    *
                FROM
                    Emails
                WHERE
                    user_id = ?';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$user_id]);

        $emails = [];
        foreach ($rs as $row) {
            array_push($emails, new \OmegaUp\DAO\VO\Emails($row));
        }
        return $emails;
    }
}
