<?php

namespace OmegaUp\DAO;

/**
 * Certificates Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Certificates}.
 *
 * @psalm-type Certificate=array{answer: null|string, assignment_alias?: null|string, author: string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 */
class Certificates extends \OmegaUp\DAO\Base\Certificates {
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
