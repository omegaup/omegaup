<?php

namespace OmegaUp\DAO;

/**
 * AuthTokens Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AuthTokens}.
 *
 * @psalm-type IdentityExt=array{classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}
 * @psalm-type AuthIdentityExt=array{currentIdentity: IdentityExt, loginIdentity: IdentityExt}
 *
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

    /**
     * @return AuthIdentityExt|null
     */
    public static function getIdentityByToken(string $authToken) {
        $sql = "SELECT
                    i.*,
                    aut.identity_id = i.identity_id AS `is_main_identity`,
                    IFNULL(
                        (
                            SELECT `urc`.`classname` FROM
                                `User_Rank_Cutoffs` `urc`
                            WHERE
                                `urc`.`score` <= (
                                        SELECT
                                            `ur`.`score`
                                        FROM
                                            `User_Rank` `ur`
                                        WHERE
                                            `ur`.`user_id` = `i`.`user_id`
                                    )
                            ORDER BY
                                `urc`.`percentile` ASC
                            LIMIT
                                1
                        ),
                        'user-rank-unranked'
                    ) `classname`
                FROM
                    `Auth_Tokens` aut
                INNER JOIN
                    `Identities` i
                ON
                    i.identity_id IN (aut.identity_id, aut.acting_identity_id)
                WHERE
                    aut.token = ?
                ORDER BY
                    `is_main_identity` DESC;";
        /** @var list<array{classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, is_main_identity: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$authToken]
        );
        if (empty($rs)) {
            return null;
        }
        if (count($rs) === 1) {
            unset($rs[0]['is_main_identity']);
            return [
                'currentIdentity' => $rs[0],
                'loginIdentity' => $rs[0],
            ];
        }
        $currentIdentity = array_pop($rs);
        $loginIdentity = array_pop($rs);
        unset($currentIdentity['is_main_identity']);
        unset($loginIdentity['is_main_identity']);

        return [
            'currentIdentity' => $currentIdentity,
            'loginIdentity' => $loginIdentity,
        ];
    }

    public static function updateActingIdentityId(
        string $token,
        int $actingIdentityId
    ): int {
        $sql = 'UPDATE
                    `Auth_Tokens`
                SET
                    `acting_identity_id` = ?
                WHERE
                    token = ?;';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$actingIdentityId, $token]
        );

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    public static function expireAuthTokens(int $identityId): int {
        $sql = 'DELETE FROM
                    `Auth_Tokens`
                WHERE
                    identity_id = ?;';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$identityId]);

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
        /** @var list<array{acting_identity_id: int|null, create_time: \OmegaUp\Timestamp, identity_id: int, token: string, user_id: int|null}> */
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
