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
 *
 * @psalm-type CertificateListItem=array{certificate_type: string, date: \OmegaUp\Timestamp, name: null|string, verification_code: string}
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
     * Gets an array of a user's certificates using the user id.
     * @return list<CertificateListItem>
     */
    final public static function getUserCertificates(
        int $userId
    ): array {
        $sql = '
            SELECT
                `Certificates`.verification_code,
                `Certificates`.timestamp AS date,
                `Certificates`.certificate_type,
                IF(
                    `Certificates`.certificate_type = "course",
                    `Courses`.name,
                    IF(
                        `Certificates`.certificate_type = "contest",
                        `Contests`.title,
                        NULL
                    )
                ) AS name
            FROM `Certificates`
            LEFT JOIN `Courses`
                ON `Certificates`.course_id = `Courses`.course_id
            LEFT JOIN `Contests`
                ON `Certificates`.contest_id = `Contests`.contest_id
            INNER JOIN `Identities`
                ON `Identities`.identity_id = `Certificates`.identity_id
            INNER JOIN `Users`
                ON `Identities`.user_id = `Users`.user_id
            WHERE
                `Users`.user_id = ?
            ORDER BY `Certificates`.timestamp DESC;
        ';

        /** @var list<array{certificate_type: string, date: \OmegaUp\Timestamp, name: null|string, verification_code: string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$userId]
        );
        return $result;
    }

    /**
     * Returns true if a certificate is valid using its verification code
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

    /**
     * Returns all certificates associated with the given contest.
     *
     * @param int $contestId
     * @return list<\OmegaUp\DAO\VO\Certificates>
     */
    public static function getByContestId(int $contestId): array {
        $sql = '
            SELECT
                `Certificates`.certificate_id,
                `Certificates`.identity_id,
                `Certificates`.`timestamp`,
                `Certificates`.certificate_type,
                `Certificates`.course_id,
                `Certificates`.contest_id,
                `Certificates`.coder_of_the_month_id,
                `Certificates`.verification_code,
                `Certificates`.contest_place
            FROM
                `Certificates`
            WHERE
                contest_id = ?
                AND certificate_type = "contest"
            ORDER BY
                certificate_id ASC;';
        /** @var list<array<string, mixed>> $rows */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$contestId]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = new \OmegaUp\DAO\VO\Certificates($row);
        }
        /** @var list<\OmegaUp\DAO\VO\Certificates> */
        return $result;
    }
}
