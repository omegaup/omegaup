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
 * @psalm-type Certificate=array{answer: null|string, assignment_alias?: null|string, author: string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 */
class Certificates extends \OmegaUp\DAO\Base\Certificates {
    /**
     * Returns the certificate type using its verification code
     *
     * @return string|null
     */
    final public static function getCertificateTypeByVerificationCode(
        string $verification_code
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
            [$verification_code]
        );

        return $type;
    }

    /**
     * Returns the data of the contest certificate using its verification code
     *
     * @return array{contest_title: string, identity_name: string, contest_place: int|null, timestamp: \OmegaUp\Timestamp}|null
     */
    final public static function getContestCertificateByVerificationCode(
        string $verification_code
    ) {
        $sql = '
            SELECT
                co.title AS contest_title,
                i.name AS identity_name,
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

        /** @var array{contest_title: string, identity_name: string, contest_place: int, timestamp: \OmegaUp\Timestamp}|null */
        $data = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$verification_code]
        );

        return $data;
    }

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

    /**
     * Gets an array of the best solving runs for a problem.
     * @return list<array{classname: string, username: string, language: string, runtime: float, memory: float, time: \OmegaUp\Timestamp}>
     */
    final public static function getUserCertificates(
        int $userId
    ): array {
        $sql = '
            SELECT
                `Certificates`.verification_code,
                `Certificates`.timestamp AS Fecha,
                `Certificates`.identity_id,
                IF(
                    `Certificates`.certificate_type = "course", `Courses`.name,
                    IF(`Certificates`.certificate_type = "contest", `Contests`.title, null)
                ) as Nombre
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
                ORDER BY `Certificates`.timestamp ASC;
        ';
        $val = [$userId];

        $result = [];
        /** @var array{classname: string, language: string, memory: int, per_identity_rank: int, runtime: int, time: \OmegaUp\Timestamp, username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            $result[] = $row;
        }
        error_log(print_r('holaaaaaaaaaaaa', true));
        return $result;
    }
}
