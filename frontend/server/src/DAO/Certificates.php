<?php

namespace OmegaUp\DAO;

/**
 * Certificates Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Certificates}.
 * @access public
 * @package docs
 */
class Certificates extends \OmegaUp\DAO\Base\Certificates {
    /**
     * Returns if a certificate is valid using its verification code
     *
     * @return int
     */
    final public static function isValid(
        string $verification_code
    ) {
        $sql = '
        SELECT
            EXISTS(
                SELECT certificate_id
                FROM Certificates
                WHERE verification_code = ?
            );
        ';

        /** @var int */
        $type = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$verification_code]
        );

        return $type;
    }
}
