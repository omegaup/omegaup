<?php

namespace OmegaUp\DAO;

/**
 * Emails Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Emails}.
 * @access public
 * @package docs
 */
class Emails extends \OmegaUp\DAO\Base\Emails {
    /**
     * @return list<\OmegaUp\DAO\VO\Emails>
     */
    final public static function getByUserId(int $userId): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Emails::FIELD_NAMES,
            'Emails'
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    Emails
                WHERE
                    user_id = ?";

        /** @var list<array{email: null|string, email_id: int, user_id: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$userId]);

        $emails = [];
        foreach ($rs as $row) {
            array_push($emails, new \OmegaUp\DAO\VO\Emails($row));
        }
        return $emails;
    }

    /**
     * @return null|string
     */
    final public static function getMainMailByUserId(int $userId) {
        $sql = 'SELECT
                    email
                FROM
                    Emails e
                INNER JOIN
                    Users u
                ON
                    e.email_id = u.main_email_id
                WHERE
                    u.user_id = ?';

        /** @var null|string */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$userId]);
    }

    final public static function existsByEmail(string $email): bool {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    `Emails`
                WHERE
                    (
                        `email` = ?
                    );';
        $params = [$email];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * @return null|\OmegaUp\DAO\VO\Emails
     */
    final public static function getByEmail(string $email) {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Emails::FIELD_NAMES,
            'Emails'
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    `Emails`
                WHERE
                    `email` = ?
                LIMIT 1;";
        $params = [$email];
        /** @var null|array{email: null|string, email_id: int, user_id: int|null} */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        return new \OmegaUp\DAO\VO\Emails($row);
    }
}
