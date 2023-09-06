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
     * Returns the data of the coder of the month certificate using its verification code
     *
     * @return array{identity_name: string, timestamp: \OmegaUp\Timestamp}|null
     */
    final public static function getCoderOfTheMonthCertificateByVerificationCode(
        string $verification_code
    ) {
        $sql = '
            SELECT
                i.name AS identity_name,
                ce.timestamp
            FROM
                Certificates ce
            INNER JOIN
                Identities i
            ON
                i.identity_id = ce.identity_id
            WHERE
                ce.verification_code = ?;
        ';

        /** @var array{identity_name: string, timestamp: \OmegaUp\Timestamp}|null */
        $data = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$verification_code]
        );

        return $data;
    }
}
