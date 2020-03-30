<?php

namespace OmegaUp\DAO;

/**
 * AuthTokens Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AuthTokens}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class AuthTokens extends \OmegaUp\DAO\Base\AuthTokens {
    public static function getUserByToken(string $authToken): ?\OmegaUp\DAO\VO\Users {
        $sql = 'SELECT
                    u.*
                FROM
                    `Users` u
                INNER JOIN
                    `Auth_Tokens` at
                ON
                    at.user_id = u.user_id
                WHERE
                    at.token = ?;';
        /** @var array{birth_date: null|string, facebook_user_id: null|string, git_token: null|string, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, main_email_id: int|null, main_identity_id: int|null, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$authToken]
        );
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
    }

    public static function getIdentityByToken(string $authToken): ?\OmegaUp\DAO\VO\Identities {
        $sql = 'SELECT
                    i.*
                FROM
                    `Identities` i
                INNER JOIN
                    `Auth_Tokens` at
                ON
                    at.identity_id = i.identity_id
                WHERE
                    at.token = ?;';
        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$authToken]
        );

        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    public static function expireAuthTokens(int $identity_id): int {
        $sql = 'DELETE FROM
                    `Auth_Tokens`
                WHERE
                    identity_id = ?;';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$identity_id]);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return list<\OmegaUp\DAO\VO\AuthTokens>
     */
    final public static function getByIdentityId(int $identityId): array {
        $sql = 'SELECT
                    at.*
                FROM
                    `Auth_Tokens` at
                WHERE
                    at.identity_id = ?;';
        /** @var list<array{create_time: \OmegaUp\Timestamp, identity_id: int, token: string, user_id: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId]
        );

        $authTokens = [];
        foreach ($rs as $row) {
            $authTokens[] = new \OmegaUp\DAO\VO\AuthTokens($row);
        }
        return $authTokens;
    }
}
