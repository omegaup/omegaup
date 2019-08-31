<?php

/**
 * Users Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link \OmegaUp\DAO\VO\Users}.
 * @author alanboy
 * @access public
 * @package docs
 */
class UsersDAO extends \OmegaUp\DAO\Base\Users {
    public static function FindByEmail($email) {
        $sql = 'select u.* from Users u, Emails e where e.email = ? and e.user_id = u.user_id';
        $params = [ $email ];
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
    }

    public static function FindByUsername($username) {
        $sql = 'SELECT u.* FROM Users u WHERE username = ? LIMIT 1;';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$username]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
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
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params) > 0;
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

    public static function savePassword(\OmegaUp\DAO\VO\Users $Users) {
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
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    final public static function getExtendedProfileDataByPk($user_id) {
        if (is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT
                    COALESCE(c.`name`, "xx") AS country,
                    c.`country_id` AS country_id,
                    s.`name` AS state,
                    s.`state_id` AS state_id,
                    sc.`name` AS school,
                    sc.`school_id` AS school_id,
                    e.`email`,
                    l.`name` AS locale
                FROM
                    Users u
                INNER JOIN
                    Identities i ON u.main_identity_id = i.identity_id
                INNER JOIN
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
                    u.`user_id` = ?
                LIMIT
                    1;';
        $params = [$user_id];
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
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

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
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
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params) ?? 'user-rank-unranked';
    }

    final public static function getByVerification($verification_id) {
        $sql = 'SELECT
                    *
                FROM
                    Users
                WHERE
                    verification_id = ?';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$verification_id]);

        $users = [];
        foreach ($rs as $row) {
            array_push($users, new \OmegaUp\DAO\VO\Users($row));
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

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$verified, $in_mailing_list]);

        $users = [];
        foreach ($rs as $row) {
            array_push($users, new \OmegaUp\DAO\VO\Users($row));
        }
        return $users;
    }

    public static function getUsersCount() {
        $sql = 'SELECT
                    COUNT(*) AS total
                FROM
                    Users;';
        return \OmegaUp\MySQLConnection::getInstance()->GetRow($sql)['total'];
    }
}
