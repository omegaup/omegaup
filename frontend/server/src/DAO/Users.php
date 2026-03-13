<?php

namespace OmegaUp\DAO;

/**
 * Users Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Users}.
 *
 * @access public
 * @package docs
 */
class Users extends \OmegaUp\DAO\Base\Users {
    public static function findByEmail(string $email): ?\OmegaUp\DAO\VO\Users {
        $fields = join(
            ', ',
            array_map(
                fn (string $field): string => "u.{$field}",
                array_keys(
                    \OmegaUp\DAO\VO\Users::FIELD_NAMES
                )
            )
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    `Users` u
                INNER JOIN
                    `Emails` e
                ON
                    e.user_id = u.user_id
                WHERE
                    e.email = ?;";
        $params = [ $email ];
        /** @var array{birth_date: null|string, creation_timestamp: \OmegaUp\Timestamp, deletion_token: null|string, facebook_user_id: null|string, git_token: null|string, github_url: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, linkedin_url: null|string, main_email_id: int|null, main_identity_id: int|null, parent_email_id: int|null, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_email_verification_initial: \OmegaUp\Timestamp|null, parent_verified: bool|null, parental_verification_token: null|string, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool, x_url: null|string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
    }

    public static function FindByUsername(
        string $username
    ): ?\OmegaUp\DAO\VO\Users {
        $fields = join(
            ', ',
            array_map(
                fn (string $field): string => "u.{$field}",
                array_keys(
                    \OmegaUp\DAO\VO\Users::FIELD_NAMES
                )
            )
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    Users u
                INNER JOIN
                    Identities i
                ON
                    i.user_id = u.user_id
                WHERE
                    i.username = ?
                LIMIT 1;";
        /** @var array{birth_date: null|string, creation_timestamp: \OmegaUp\Timestamp, deletion_token: null|string, facebook_user_id: null|string, git_token: null|string, github_url: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, linkedin_url: null|string, main_email_id: int|null, main_identity_id: int|null, parent_email_id: int|null, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_email_verification_initial: \OmegaUp\Timestamp|null, parent_verified: bool|null, parental_verification_token: null|string, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool, x_url: null|string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$username]
        );
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($rs);
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
     * @return array{birth_date: \OmegaUp\Timestamp|null, classname: string, country: string, country_id: null|string, email: null|string, gender: null|string, graduation_date: \OmegaUp\Timestamp|null, locale: null|string, school: null|string, school_id: int|null, scholar_degree: null|string, state: null|string, state_id: null|string, hide_problem_tags: bool, verified: bool|null}|null
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
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Users u
                INNER JOIN
                    Identities i ON u.main_identity_id = i.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
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
        $user['graduation_date'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $user['graduation_date']
        );
        return $user;
    }

    /**
     * @return array{classname: string, country_id: string, email: null|string}
     */
    final public static function getBasicProfileDataByPk(?int $userId): array {
        if (is_null($userId)) {
            return [
                'classname' => 'user-rank-unranked',
                'country_id' => 'xx',
                'email' => null,
            ];
        }
        $sql = 'SELECT
                    IFNULL(i.`country_id`, "xx") AS country_id,
                    e.`email`,
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Users u
                INNER JOIN
                    Identities i ON u.main_identity_id = i.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                LEFT JOIN
                    Emails e ON u.main_email_id = e.email_id
                WHERE
                    u.`user_id` = ?
                LIMIT
                    1;';
        /** @var array{classname: string, country_id: string, email: null|string}|null */
        $user = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$userId]
        );

        if (is_null($user)) {
            return [
                'classname' => 'user-rank-unranked',
                'country_id' => 'xx',
                'email' => null,
            ];
        }

        return $user;
    }

    public static function getPreferredLanguage(?int $userId): ?string {
        if (is_null($userId)) {
            return null;
        }
        $sql = 'SELECT
                    preferred_language
                FROM
                    Users u
                WHERE
                    u.user_id = ?;';
        $params = [$userId];
        /** @var null|string */
        $preferredLanguage = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            $params
        );
        return $preferredLanguage;
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
                    ur.classname
                FROM
                    User_Rank ur
                WHERE
                    ur.user_id = ?;';
        $params = [$userId];
        /** @var null|string */
        $className = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            $params
        );
        return $className ?? 'user-rank-unranked';
    }

    final public static function getByVerification(
        string $verificationId
    ): ?\OmegaUp\DAO\VO\Users {
        $fields = join(', ', array_keys(\OmegaUp\DAO\VO\Users::FIELD_NAMES));
        $sql = "SELECT
                    {$fields}
                FROM
                    Users u
                WHERE
                    verification_id = ?
                LIMIT 1;";

        /** @var array{birth_date: null|string, creation_timestamp: \OmegaUp\Timestamp, deletion_token: null|string, facebook_user_id: null|string, git_token: null|string, github_url: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, linkedin_url: null|string, main_email_id: int|null, main_identity_id: int|null, parent_email_id: int|null, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_email_verification_initial: \OmegaUp\Timestamp|null, parent_verified: bool|null, parental_verification_token: null|string, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool, x_url: null|string}|null */
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
    * Delete User
    */
    public static function deleteUserAndIndentityInformation(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity
    ): int {
        try {
            \OmegaUp\DAO\DAO::transBegin();
            $sql = '
                UPDATE
                    `Users`
                SET
                    `facebook_user_id` = NULL,
                    `git_token`= NULL,
                    `main_email_id`= NULL,
                    `main_identity_id`= NULL,
                    `has_learning_objective`= NULL,
                    `has_scholar_objective`= NULL,
                    `has_competitive_objective`= NULL,
                    `verification_id`= NULL,
                    `reset_digest`= NULL,
                    `reset_sent_at`= NULL,
                    `hide_problem_tags`= NULL,
                    `birth_date`= NULL,
                    verified = 0,
                    in_mailing_list = 0,
                    is_private = 0
                WHERE
                    `user_id` = ?;';
            $params = [
                $user->user_id,
            ];
            \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
            $randomString = \OmegaUp\SecurityTools::randomString(20);
            $sql = '
                UPDATE
                    `Identities`
                SET
                    `username` = ?,
                    `password` = NULL,
                    `name`= NULL,
                    `user_id`= NULL,
                    `language_id`= NULL,
                    `country_id`= NULL,
                    `state_id`= NULL,
                    `gender`= NULL
                WHERE
                    `identity_id` = ?;';
            $params = [
                "deleted_user_{$randomString}",
                $identity->identity_id,
            ];
            \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
            $sql = '
                UPDATE
                    `Emails`
                SET
                    `email` = ?
                WHERE
                    `user_id` = ?;';
            $params = [
                "deleted_user_{$randomString}@{$randomString}",
                $identity->user_id,
            ];
            \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

   /**
     * @return list<\OmegaUp\DAO\VO\Users>
     */
    final public static function getVerified(
        bool $verified,
        bool $inMailingList
    ): array {
        $fields = join(', ', array_keys(\OmegaUp\DAO\VO\Users::FIELD_NAMES));
        $sql = "SELECT
                    {$fields}
                FROM
                    Users u
                WHERE
                    verified = ?
                AND
                    in_mailing_list = ?";

        /** @var list<array{birth_date: null|string, creation_timestamp: \OmegaUp\Timestamp, deletion_token: null|string, facebook_user_id: null|string, git_token: null|string, github_url: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, linkedin_url: null|string, main_email_id: int|null, main_identity_id: int|null, parent_email_id: int|null, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_email_verification_initial: \OmegaUp\Timestamp|null, parent_verified: bool|null, parental_verification_token: null|string, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool, x_url: null|string}> */
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

    final public static function generateDeletionToken(
        \OmegaUp\DAO\VO\Users $user,
        string $token
    ): void {
        $sql = '
            UPDATE
                Users u
            SET
                u.deletion_token = ?
            WHERE
                u.user_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$token, $user->user_id]
        );
    }

    final public static function validateDeletionToken(
        \OmegaUp\DAO\VO\Users $user,
        string $token
    ): bool {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Users
                WHERE
                    `user_id` = ?
                    AND `deletion_token` = ?;';

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$user->user_id, $token]
        );

        return boolval($count);
    }

    public static function findByParentalToken(string $token): ?\OmegaUp\DAO\VO\Users {
        $fields = join(
            ', ',
            array_map(
                fn (string $field): string => "u.{$field}",
                array_keys(
                    \OmegaUp\DAO\VO\Users::FIELD_NAMES
                )
            )
        );
        $sql = "SELECT
                      {$fields}

                    FROM
                        Users u
                    INNER JOIN
                        Identities i ON u.main_identity_id = i.identity_id
                    WHERE
                      parental_verification_token = ?
                    LIMIT 1
                    FOR UPDATE;";
        /** @var array{birth_date: null|string, creation_timestamp: \OmegaUp\Timestamp, deletion_token: null|string, facebook_user_id: null|string, git_token: null|string, github_url: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool|null, in_mailing_list: bool, is_private: bool, linkedin_url: null|string, main_email_id: int|null, main_identity_id: int|null, parent_email_id: int|null, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_email_verification_initial: \OmegaUp\Timestamp|null, parent_verified: bool|null, parental_verification_token: null|string, preferred_language: null|string, reset_digest: null|string, reset_sent_at: \OmegaUp\Timestamp|null, scholar_degree: null|string, user_id: int, verification_id: null|string, verified: bool, x_url: null|string}|null */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$token]
        );
        if (is_null($result)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Users($result);
    }

    /**
     * @return list<array{classname: string, name: null|string, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_verified: bool|null, username: string}>
     */
    public static function getUserDependents(\OmegaUp\DAO\VO\Users $user): array {
        $sql = 'SELECT
                    i.name,
                    i.username,
                    u.parent_email_verification_deadline,
                    u.parent_verified,
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Users u
                INNER JOIN
                    Identities i ON u.main_identity_id = i.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                LEFT JOIN
                    Emails e ON u.main_email_id = e.email_id
                WHERE
                    u.parent_email_id = ?';

        /** @var list<array{classname: string, name: null|string, parent_email_verification_deadline: \OmegaUp\Timestamp|null, parent_verified: bool|null, username: string}> */
        $dependents = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [ $user->main_email_id ]
        );
        return $dependents;
    }
}
