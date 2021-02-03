<?php

namespace OmegaUp\DAO;

/**
 * Users Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link \OmegaUp\DAO\VO\Users}.
 * @author alanboy
 * @access public
 * @package docs
 */
class Users extends \OmegaUp\DAO\Base\Users {
    public static function findByEmail(string $email): ?\OmegaUp\DAO\VO\Users {
        $sql = 'select u.* from Users u, Emails e where e.email = ? and e.user_id = u.user_id';
        $params = [ $email ];
        /** @var array{birth_date: null|string, facebook_user_id: null|string, git_token: null|string, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, main_email_id: int|null, main_identity_id: int|null, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
    }

    public static function FindByUsername(
        string $username
    ): ?\OmegaUp\DAO\VO\Users {
        $sql = 'SELECT
                    u.*
                FROM
                    Users u
                INNER JOIN
                    Identities i
                ON
                    i.user_id = u.user_id
                WHERE
                    i.username = ?
                LIMIT 1;';
        /** @var array{birth_date: null|string, facebook_user_id: null|string, git_token: null|string, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, main_email_id: int|null, main_identity_id: int|null, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$username]
        );
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
    }

    public static function IsUserInterviewer(int $userId): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                User_Roles ur
            WHERE
                ur.user_id = ? AND ur.role_id = 4;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$userId]
        );
        return $count > 0;
    }

    /**
     * @return null|array{reset_digest: ?string, reset_sent_at: \OmegaUp\Timestamp|null}
     */
    public static function FindResetInfoByEmail(string $email): ?array {
        $user = self::findByEmail($email);
        if (is_null($user)) {
            return null;
        }
        return [
            'reset_digest' => $user->reset_digest,
            'reset_sent_at' => $user->reset_sent_at,
        ];
    }
    /**
     * @param int $user_id
     * @return array{birth_date: \OmegaUp\Timestamp|null, classname: string, country: string, country_id: null|string, email: null|string, gender: null|string, graduation_date: null|string, locale: null|string, school: null|string, school_id: int|null, scholar_degree: null|string, state: null|string, state_id: null|string, hide_problem_tags: bool, verified: bool|null}|null
    */
    final public static function getExtendedProfileDataByPk(int $userId): ?array {
        $sql = 'SELECT
                    IFNULL(c.`name`, "xx") AS country,
                    c.`country_id` AS country_id,
                    s.`name` AS state,
                    s.`state_id` AS state_id,
                    sc.`name` AS school,
                    sc.`school_id` AS school_id,
                    isc.`graduation_date` AS graduation_date,
                    e.`email`,
                    l.`name` AS locale,
                    IF(u.`is_private` = 1, NULL, u.`birth_date`) AS birth_date,
                    IF(u.`is_private` = 1, NULL, u.`scholar_degree`) AS scholar_degree,
                    IF(u.`is_private` = 1, NULL, u.`hide_problem_tags`) AS hide_problem_tags,
                    IF(u.`is_private` = 1, NULL, u.`verified`) AS verified,
                    IF(u.`is_private` = 1, NULL, i.`gender`) AS gender,
                    IFNULL(
                        (
                            SELECT urc.classname FROM
                                User_Rank_Cutoffs urc
                            WHERE
                                urc.score <= (
                                        SELECT
                                            ur.score
                                        FROM
                                            User_Rank ur
                                        WHERE
                                            ur.user_id = i.user_id
                                    )
                            ORDER BY
                                urc.percentile ASC
                            LIMIT
                                1
                        ),
                        \'user-rank-unranked\'
                    ) AS classname
                FROM
                    Users u
                INNER JOIN
                    Identities i ON u.main_identity_id = i.identity_id
                LEFT JOIN
                    Emails e ON u.main_email_id = e.email_id
                LEFT JOIN
                    Countries c ON i.country_id = c.country_id
                LEFT JOIN
                    States s ON i.state_id = s.state_id AND s.country_id = c.country_id
                LEFT JOIN
                    Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
                LEFT JOIN
                    Schools sc ON sc.school_id = isc.school_id
                LEFT JOIN
                    Languages l ON i.language_id = l.language_id
                WHERE
                    u.`user_id` = ?
                LIMIT
                    1;';
        /** @var array{birth_date: null|string, classname: string, country: string, country_id: null|string, email: null|string, gender: null|string, graduation_date: null|string, hide_problem_tags: bool|null, locale: null|string, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, verified: bool|null}|null */
        $user = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$userId]
        );

        if (is_null($user)) {
            return null;
        }

        $user['hide_problem_tags'] = boolval($user['hide_problem_tags']);
        $user['birth_date'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $user['birth_date']
        );

        return $user;
    }

    public static function getHideTags(?int $identityId): bool {
        if (is_null($identityId)) {
            return false;
        }
        $sql = '
            SELECT
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
                1;
        ';
        $params = [$identityId];

        return boolval(
            /** @var bool|null */
            \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params)
        );
    }

    public static function getRankingClassName(?int $userId): string {
        if (is_null($userId)) {
            return 'user-rank-unranked';
        }
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
        $params = [$userId];
        /** @var string */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            $params
        ) ?? 'user-rank-unranked';
    }

    final public static function getByVerification(
        string $verificationId
    ): ?\OmegaUp\DAO\VO\Users {
        $sql = '
            SELECT
                *
            FROM
                Users
            WHERE
                verification_id = ?
            LIMIT 1;
        ';

        /** @var array{birth_date: null|string, facebook_user_id: null|string, git_token: null|string, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, main_email_id: int|null, main_identity_id: int|null, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$verificationId]
        );
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Users($row);
    }

    /**
     * @return null|array{username: string, verified: bool}
     */
    public static function getStatusVerified(string $email) {
        $sql = 'SELECT
                    verified,
                    username,
                FROM
                    Identities i
                INNER JOIN
                    Users u
                ON
                    u.user_id = i.user_id
                INNER JOIN
                    Emails e
                ON
                    u.main_email_id = e.email_id
                WHERE
                    email = ?;
                LIMIT 1;';
        /** @var null|array{username: string, verified: bool} */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow($sql);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Users>
     */
    final public static function getVerified(
        bool $verified,
        bool $inMailingList
    ): array {
        $sql = 'SELECT
                    *
                FROM
                    Users
                WHERE
                    verified = ?
                AND
                    in_mailing_list = ?';

        /** @var list<array{birth_date: null|string, facebook_user_id: null|string, git_token: null|string, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, main_email_id: int|null, main_identity_id: int|null, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$verified, $inMailingList]
        );

        $users = [];
        foreach ($rs as $row) {
            $users[] = new \OmegaUp\DAO\VO\Users($row);
        }
        return $users;
    }

    public static function getUsersCount(): int {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Users;';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql);
    }
}
