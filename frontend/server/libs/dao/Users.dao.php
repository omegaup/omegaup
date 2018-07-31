<?php

require_once('base/Users.dao.base.php');
require_once('base/Users.vo.base.php');

/** Users Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Users }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class UsersDAO extends UsersDAOBase {
    public static function FindByEmail($email) {
        global  $conn;
        $sql = 'select u.* from Users u, Emails e where e.email = ? and e.user_id = u.user_id';
        $params = [ $email ];
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }
        return new Users($rs);
    }

    public static function FindByUsername($username) {
        global  $conn;
        $sql = 'SELECT u.* FROM Users u WHERE username = ? LIMIT 1';
        $rs = $conn->GetRow($sql, [$username]);
        if (count($rs)==0) {
            return null;
        }
        return new Users($rs);
    }

    public static function IsUserInterviewer($user_id) {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                User_Roles ur
            WHERE
                ur.user_id = ? AND ur.role_id = 4;';
        $params = [$user_id];
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    public static function FindByUsernameOrName($usernameOrName) {
        global  $conn;
        $sql = "
            SELECT
                u.*
            FROM
                Users u
            WHERE
                u.username = ? OR u.name = ?
            UNION DISTINCT
            SELECT DISTINCT
                u.*
            FROM
                Users u
            WHERE
                u.username LIKE CONCAT('%', ?, '%') OR
                u.username LIKE CONCAT('%', ?, '%')
            LIMIT 10";
        $args = [$usernameOrName, $usernameOrName, $usernameOrName, $usernameOrName];

        $rs = $conn->Execute($sql, $args);
        $result = [];
        foreach ($rs as $user_data) {
            array_push($result, new Users($user_data));
        }
        return $result;
    }

    public static function FindResetInfoByEmail($email) {
        $user = self::FindByEmail($email);
        if (is_null($user)) {
            return null;
        } else {
            return [
                'reset_digest'  => $user->reset_digest,
                'reset_sent_at'     => $user->reset_sent_at
            ];
        }
    }

    public static function savePassword(Users $Users) {
        $sql = '
            UPDATE
                `Users`
            SET
                `password` = ?,
                `username` = ?
            WHERE
                `user_id` = ?;';
        $params = [
            $Users->password,
            $Users->username,
            $Users->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    final public static function getExtendedProfileDataByPk($user_id) {
        if (is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT
                    c.`name` AS country,
                    s.`name` AS state,
                    sc.`name` AS school,
                    e.`email`,
                    l.`name` AS locale
                FROM
                    Users u
                INNER JOIN
                    Emails e ON u.main_email_id = e.email_id
                LEFT JOIN
                    Countries c ON u.country_id = c.country_id
                LEFT JOIN
                    States s ON u.state_id = s.state_id AND s.country_id = c.country_id
                LEFT JOIN
                    Schools sc ON u.school_id = sc.school_id
                LEFT JOIN
                    Languages l ON u.language_id = l.language_id
                WHERE
                    u.`user_id` = ?
                LIMIT
                    1;';
        $params = [$user_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return $rs;
    }

    public static function getHideTags($identity_id) {
        if (is_null($identity_id)) {
            return false;
        }
        $sql = 'SELECT
                    `Users`.`hide_problem_tags`
                FROM
                    Users
                INNER JOIN
                    Identities
                ON
                    Users.user_id = Identities.user_id
                WHERE
                    identity_id = ?
                LIMIT
                    1;';
        $params = [$identity_id];

        global $conn;
        return $conn->GetOne($sql, $params);
    }

    public static function getRankingClassName($user_id) {
        $sql = 'SELECT
                    `urc`.`classname`
                FROM
                    `User_Rank_Cutoffs` `urc`
                WHERE
                    `urc`.score <= (
                        SELECT
                            `ur`.`score`
                        FROM
                            `User_Rank` `ur`
                        WHERE
                            `ur`.user_id = ?
                    )
                ORDER BY
                    `urc`.percentile ASC
                LIMIT
                    1;';
        $params = [$user_id];
        global $conn;
        return $conn->GetOne($sql, $params) ?? 'user-rank-unranked';
    }

    final public static function getByVerification($verification_id) {
        $sql = 'SELECT
                    *
                FROM
                    Users
                WHERE
                    verification_id = ?';

        global $conn;
        $rs = $conn->Execute($sql, [$verification_id]);

        $users = [];
        foreach ($rs as $row) {
            array_push($users, new Users($row));
        }
        return $users;
    }

    final public static function getVerified($verified, $in_mailing_list) {
        $sql = 'SELECT
                    *
                FROM
                    Users
                WHERE
                    verified = ?
                AND
                    in_mailing_list = ?';

        global $conn;
        $rs = $conn->Execute($sql, [$verified, $in_mailing_list]);

        $users = [];
        foreach ($rs as $row) {
            array_push($users, new Users($row));
        }
        return $users;
    }
}
