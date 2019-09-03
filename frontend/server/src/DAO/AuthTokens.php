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
    public static function getUserByToken($auth_token) {
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
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$auth_token]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
    }

    public static function getIdentityByToken($auth_token) : ?\OmegaUp\DAO\VO\Identities {
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
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$auth_token]);

        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    public static function expireAuthTokens($identity_id) {
        $sql = 'DELETE FROM
                    `Auth_Tokens`
                WHERE
                    identity_id = ?;';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$identity_id]);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    final public static function getByIdentityId($identityId) {
        $sql = 'SELECT
                    at.*
                FROM
                    `Auth_Tokens` at
                WHERE
                    at.identity_id = ?;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$identityId]);

        $authTokens = [];
        foreach ($rs as $row) {
            array_push($authTokens, new \OmegaUp\DAO\VO\AuthTokens($row));
        }
        return $authTokens;
    }
}
