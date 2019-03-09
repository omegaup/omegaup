<?php
require_once('base/Auth_Tokens.dao.base.php');
require_once('base/Auth_Tokens.vo.base.php');
/** Page-level DocBlock .
 *
 * @author alanboy
 * @package docs
 *
 */
/** AuthTokens Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link AuthTokens }.
 * @author alanboy
 * @access public
 * @package docs
 *
 */
class AuthTokensDAO extends AuthTokensDAOBase {
    public static function getUserByToken($auth_token) {
        global $conn;
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
        $rs = $conn->GetRow($sql, [$auth_token]);
        if (empty($rs)) {
            return null;
        }
        return new Users($rs);
    }

    public static function getIdentityByToken($auth_token) {
        global $conn;
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
        $rs = $conn->GetRow($sql, [$auth_token]);

        if (empty($rs)) {
            return null;
        }
        return new Identities($rs);
    }

    public static function expireAuthTokens($identity_id) {
        global $conn;
        $sql = 'DELETE FROM
                    `Auth_Tokens`
                WHERE
                    identity_id = ?;';
        $conn->Execute($sql, [$identity_id]);

        return $conn->Affected_Rows();
    }

    final public static function getByIdentityId($identityId) {
        global $conn;
        $sql = 'SELECT
                    at.*
                FROM
                    `Auth_Tokens` at
                WHERE
                    at.identity_id = ?;';
        $rs = $conn->Execute($sql, [$identityId]);

        $authTokens = [];
        foreach ($rs as $row) {
            array_push($authTokens, new AuthTokens($row));
        }
        return $authTokens;
    }
}
