<?php

include_once('base/Identities.dao.base.php');
include_once('base/Identities.vo.base.php');
/** Identities Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Identities }.
  * @access public
  *
  */
class IdentitiesDAO extends IdentitiesDAOBase {
    public static function FindByEmail($email) {
        global  $conn;
        $sql = 'SELECT
                  i.*
                FROM
                  `Identities` i
                INNER JOIN
                  `Emails` e
                ON
                  e.user_id = i.user_id
                WHERE
                  e.email = ?
                LIMIT
                  0, 1';
        $params = [ $email ];
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new Identities($rs);
    }

    public static function findByUsername(string $username) : ?Identities {
        global  $conn;
        $sql = 'SELECT
                   i.*
                FROM
                  `Identities` i
                WHERE
                  i.username = ?
                LIMIT
                  0, 1';
        $params = [ $username ];
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new Identities($rs);
    }

    public static function findByUsernameOrName(string $usernameOrName) : array {
        global  $conn;
        $sql = "
            SELECT
                i.*
            FROM
                Identities i
            WHERE
                i.username = ? OR i.name = ?
            UNION DISTINCT
            SELECT DISTINCT
                i.*
            FROM
                Identities i
            WHERE
                i.username LIKE CONCAT('%', ?, '%') OR
                i.username LIKE CONCAT('%', ?, '%')
            LIMIT 20";
        $args = [$usernameOrName, $usernameOrName, $usernameOrName, $usernameOrName];

        $rs = $conn->GetAll($sql, $args);
        $result = [];
        foreach ($rs as $identityData) {
            array_push($result, new Identities($identityData));
        }
        return $result;
    }

    public static function findByUserId(int $userId) : ?Identities {
        global  $conn;
        $sql = 'SELECT
                  i.*
                FROM
                  `Identities` i
                INNER JOIN
                  `Users` u
                ON
                  i.identity_id = u.main_identity_id
                WHERE
                  i.user_id = ?
                LIMIT
                  0, 1';
        $rs = $conn->GetRow($sql, [$userId]);
        if (empty($rs)) {
            return null;
        }
        return new Identities($rs);
    }

    public static function getExtraInformation($email) {
        global  $conn;
        $sql = 'SELECT
                  UNIX_TIMESTAMP(u.reset_sent_at) AS reset_sent_at,
                  u.verified,
                  u.username,
                  (
                    SELECT
                      MAX(UNIX_TIMESTAMP(ill.time))
                    FROM
                      Identity_Login_Log AS ill
                    WHERE
                      ill.identity_id = i.identity_id
                  ) AS last_login
                FROM
                  `Identities` i
                INNER JOIN
                  `Users` u
                ON
                  u.user_id = i.user_id
                INNER JOIN
                  `Emails` e
                ON
                  e.user_id = u.user_id
                WHERE
                  e.email = ?
                ORDER BY
                  u.user_id DESC
                LIMIT
                  0, 1';
        $params = [ $email ];
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return [
          // Asks whether request was made on the last day
          'within_last_day' => Time::get() - ((int)$rs['reset_sent_at']) < 60 * 60 * 24,
          'verified' => $rs['verified'] == 1,
          'username' => $rs['username'],
          'last_login' => is_null($rs['last_login']) ? null : ((int)$rs['last_login']),
        ];
    }

    public static function isVerified($identity_id) {
        global  $conn;
        $sql = 'SELECT
                  u.verified
                FROM
                  `Identities` i
                INNER JOIN
                  `Users` u
                ON
                  u.user_id = i.user_id
                WHERE
                  i.identity_id = ?
                ORDER BY
                  u.user_id DESC
                LIMIT
                  0, 1';
        $params = [ $identity_id ];
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return $rs['verified'];
    }

    final public static function getExtendedProfileDataByPk($identity_id) {
        if (is_null($identity_id)) {
            return null;
        }
        $sql = 'SELECT
                    COALESCE(c.`name`, "xx") AS country,
                    s.`name` AS state,
                    sc.`name` AS school,
                    e.`email`,
                    l.`name` AS locale
                FROM
                    Identities i
                LEFT JOIN
                    Users u ON u.user_id = i.user_id
                LEFT JOIN
                    Emails e ON u.main_email_id = e.email_id
                LEFT JOIN
                    Countries c ON i.country_id = c.country_id
                LEFT JOIN
                    States s ON i.state_id = s.state_id AND s.country_id = c.country_id
                LEFT JOIN
                    Schools sc ON i.school_id = sc.school_id
                LEFT JOIN
                    Languages l ON i.language_id = l.language_id
                WHERE
                    i.`identity_id` = ?
                LIMIT
                    1;';
        $params = [$identity_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return $rs;
    }

    public static function isUserAssociatedWithIdentityOfGroup(int $userId, int $identityId) {
        global  $conn;
        $sql = '
            SELECT
                COUNT(*) = 1 AS associated
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                i.user_id = ? AND
                gi.group_id IN (
                    SELECT
                        group_id
                    FROM
                        Groups_Identities
                    WHERE
                        identity_id = ?
                )
            LIMIT 1;';
        $args = [$userId, $identityId];

        $rs = $conn->GetRow($sql, $args);

        return $rs['associated'] == '1';
    }

    public static function getUnassociatedIdentity($username) {
        global  $conn;
        $sql = '
            SELECT
                i.*
            FROM
                Identities i
            WHERE
                i.username = ?
                AND user_id IS NULL
            LIMIT 1;';
        $args = [$username];

        $rs = $conn->GetRow($sql, $args);
        if (empty($rs)) {
            return null;
        }
        return new Identities($rs);
    }

    public static function getAssociatedIdentities($userId) {
        global  $conn;
        $sql = '
            SELECT
                i.username,
                i.identity_id,
                u.main_identity_id
            FROM
                Identities i
            INNER JOIN
                Users u
            ON
                i.user_id = u.user_id
            WHERE
                i.user_id = ?
                ';

        $rs = $conn->GetAll($sql, [$userId]);
        $result = [];
        foreach ($rs as $identity) {
            array_push($result, [
                'username' => $identity['username'],
                'default' => $identity['identity_id'] == $identity['main_identity_id'],
            ]);
        }
        return $result;
    }

    public static function associateIdentityWithUser($userId, $identity_id) {
        global $conn;
        $sql = '
            UPDATE
                Identities
            SET
                user_id = ?
            WHERE
                identity_id = ?
        ';
        $conn->Execute($sql, [$userId, $identity_id]);

        return $conn->Affected_Rows();
    }
}
