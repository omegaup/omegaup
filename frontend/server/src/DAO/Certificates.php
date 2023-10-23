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
     * Returns the certificate type using its verification code
     *
     * @return string|null
     */
    final public static function getCertificateTypeByVerificationCode(
        string $verificationCode
    ) {
        $sql = '
            SELECT
                certificate_type
            FROM
                Certificates
            WHERE
                verification_code = ?;
        ';

        /** @var string|null */
        $type = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$verificationCode]
        );

        return $type;
    }

    /**
     * Returns the data of the contest certificate using its verification code
     *
     * @return array{contest_place: int|null, contest_title: string, identity_name: string, timestamp: \OmegaUp\Timestamp}|null
     */
    final public static function getContestCertificateByVerificationCode(
        string $verificationCode
    ) {
        $sql = '
            SELECT
                co.title AS contest_title,
                COALESCE(i.name, i.username) AS identity_name,
                ce.contest_place,
                ce.timestamp
            FROM
                Certificates ce
            INNER JOIN
                Contests co
            ON
                ce.contest_id = co.contest_id
            INNER JOIN
                Identities i
            ON
                i.identity_id = ce.identity_id
            WHERE
                ce.verification_code = ?;
        ';

        /** @var array{contest_place: int|null, contest_title: string, identity_name: string, timestamp: \OmegaUp\Timestamp}|null */
        $data = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$verificationCode]
        );

        return $data;
    }

     /**
     * Returns the data of the course certificate using its verification code
     *
     * @return array{course_name: string, identity_name: string, timestamp: \OmegaUp\Timestamp}|null
     */
    final public static function getCourseCertificateByVerificationCode(
        string $verificationCode
    ) {
        $sql = '
            SELECT
                co.name AS course_name,
                COALESCE(i.name, i.username) AS identity_name,
                ce.timestamp
            FROM
                Certificates ce
            INNER JOIN
                Courses co
            ON
                ce.course_id = co.course_id
            INNER JOIN
                Identities i
            ON
                i.identity_id = ce.identity_id
            WHERE
                ce.verification_code = ?;
        ';

        /** @var array{course_name: string, identity_name: string, timestamp: \OmegaUp\Timestamp}|null */
        $data = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$verificationCode]
        );

        return $data;
    }

     /**
     * Returns the data of the coder of the month certificate using its verification code
     *
     * @return array{identity_name: string, timestamp: \OmegaUp\Timestamp}|null
     */
    final public static function getCoderOfTheMonthCertificateByVerificationCode(
        string $verificationCode
    ) {
        $sql = '
            SELECT
                COALESCE(i.name, i.username) AS identity_name,
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
            [$verificationCode]
        );

        return $data;
    }

    /**
     * Returns if a certificate is valid using its verification code
     *
     * @return int
     */
    final public static function isValid(
        string $verificationCode
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
        $isValid = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$verificationCode]
        );

        return $isValid;
    }
}
