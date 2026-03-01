<?php

namespace OmegaUp\DAO;

/**
 * Identities Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Identities}.
 *
 * @access public
 */
class Identities extends \OmegaUp\DAO\Base\Identities {
    public static function findByEmail(string $email): ?\OmegaUp\DAO\VO\Identities {
        $sql = 'SELECT
                  ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        ) . '
                FROM
                  `Identities` i
                INNER JOIN
                  `Users` u
                ON
                  u.user_id = i.user_id AND u.main_identity_id = i.identity_id
                INNER JOIN
                  `Emails` e
                ON
                  e.user_id = u.user_id
                WHERE
                  e.email = ?
                LIMIT
                  0, 1';
        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$email]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    public static function findByUsername(
        string $username
    ): ?\OmegaUp\DAO\VO\Identities {
        $sql = 'SELECT
                   ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        ) . '
                FROM
                  `Identities` i
                WHERE
                  i.username = ?
                LIMIT
                  0, 1';
        $params = [ $username ];
        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    /**
     * @return array{identity_id: int, is_private: bool, main_identity_id: int|null}|null
     */
    public static function getIdentityPrivacyDataByUsername(
        string $username
    ): ?array {
        $sql = 'SELECT
                    i.identity_id,
                    u.main_identity_id,
                    IFNULL(u.is_private, 0) AS is_private
                FROM
                    `Identities` i
                LEFT JOIN
                    `Users` u
                ON
                    u.user_id = i.user_id
                WHERE
                    i.username = ?
                LIMIT
                    0, 1';
        /** @var array{identity_id: int, is_private: bool|int, main_identity_id: int|null}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$username]);
        if (empty($rs)) {
            return null;
        }

        return [
            'identity_id' => $rs['identity_id'],
            'is_private' => boolval($rs['is_private']),
            'main_identity_id' => $rs['main_identity_id'],
        ];
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public static function findByUsernameOrName(
        string $usernameOrName,
        int $rowcount = 100
    ) {
        $sql = "SELECT
                    sq.name,
                    sq.username,
                    SUM(sq.relevance) AS relevance
                FROM (
                    SELECT
                        i.name,
                        i.username,
                        IFNULL(MATCH(name, username) AGAINST (? IN BOOLEAN MODE), 0) AS relevance
                    FROM
                        Identities i
                    WHERE
                        MATCH(name, username) AGAINST (? IN BOOLEAN MODE)
                    UNION DISTINCT
                    SELECT DISTINCT
                        i.name,
                        i.username,
                        0 AS relevance
                    FROM
                        Identities i
                    WHERE
                        (
                            i.username LIKE CONCAT('%', ?, '%') OR
                            i.name LIKE CONCAT('%', ?, '%')
                        ) AND
                        i.username NOT REGEXP 'teams:[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+'
                ) AS sq
            GROUP BY
                username, name
            ORDER BY
                relevance DESC
            LIMIT
                ?;";
        $args = [
            $usernameOrName,
            $usernameOrName,
            $usernameOrName,
            $usernameOrName,
            $rowcount,
        ];

        /** @var list<array{name: null|string, relevance: float|null, username: string}> $rs */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
        $result = [];
        foreach ($rs as $user) {
            $username = $user['username'];
            $result[] = [
                'key' => $username,
                'value' => $user['name'] ?? $username,
            ];
        }
        return $result;
    }

    public static function findByUserId(int $userId): ?\OmegaUp\DAO\VO\Identities {
        $sql = 'SELECT
                  ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        ) . '
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
        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$userId]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    public static function resolveAssociatedIdentity(
        string $usernameOrEmail,
        \OmegaUp\DAO\VO\Identities $currentIdentity
    ): ?\OmegaUp\DAO\VO\Identities {
        if (is_null($currentIdentity->identity_id)) {
            return null;
        }
        $sql = '(
                    SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        ) . '
                    FROM
                        Identities i
                    INNER JOIN
                        Emails e
                    ON
                        e.user_id = i.user_id
                    WHERE
                        i.user_id = ?
                        AND (i.username = ? OR e.email = ?)
                )
                UNION
                (
                    SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        ) . '
                    FROM
                        Identities i
                    INNER JOIN
                        Teams t
                    ON
                        i.identity_id = t.identity_id
                    INNER JOIN
                        Team_Users tu
                    ON
                        tu.team_id = t.team_id
                    WHERE
                        tu.identity_id = ?
                        AND i.username = ?
                )
                LIMIT 1;';
        $args = [
            $currentIdentity->user_id,
            $usernameOrEmail,
            $usernameOrEmail,
            $currentIdentity->identity_id,
            $usernameOrEmail,
        ];

        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null $rs */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $args);
        if (is_null($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    public static function savePassword(\OmegaUp\DAO\VO\Identities $identities): int {
        $sql = '
            UPDATE
                `Identities`
            SET
                `password` = ?,
                `username` = ?
            WHERE
                `identity_id` = ?;';
        $params = [
            $identities->password,
            $identities->username,
            $identities->identity_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return array{birth_date: \OmegaUp\Timestamp|null, email: null|string, last_login: \OmegaUp\Timestamp|null, username: string, verified: bool, within_last_day: bool}|null
     */
    public static function getExtraInformation(string $usernameOrEmail): ?array {
        $sql = 'SELECT
                  u.reset_sent_at,
                  u.verified,
                  u.birth_date,
                  IFNULL(i.username, "") AS `username`,
                  IFNULL(e.email, "") AS `email`,
                  (
                    SELECT
                      MAX(ill.time)
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
                  (i.username = ? OR e.email = ?)
                ORDER BY
                  u.user_id DESC
                LIMIT
                  0, 1';
        /** @var array{birth_date: null|string, email: string, last_login: \OmegaUp\Timestamp|null, reset_sent_at: \OmegaUp\Timestamp|null, username: string, verified: bool}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$usernameOrEmail, $usernameOrEmail]
        );
        if (empty($rs)) {
            return null;
        }
        return [
            'within_last_day' => (
                !is_null($rs['reset_sent_at']) &&
                (
                    \OmegaUp\Time::get() - intval($rs['reset_sent_at']->time)
                ) < 60 * 60 * 24
            ),
            'verified' => $rs['verified'] == 1,
            'username' => $rs['username'],
            'email' => $rs['email'],
            'birth_date' => \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $rs['birth_date']
            ),
            'last_login' => $rs['last_login'],
        ];
    }

    public static function isVerified(int $identityId): bool {
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
        $params = [$identityId];
        return boolval(
            /** @var bool|null */
            \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params)
        );
    }

    /**
     * @return array{birth_date: \OmegaUp\Timestamp|null, classname: string, country: string, email: null|string, gender: null|string, graduation_date: \OmegaUp\Timestamp|null, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool, locale: null|string, scholar_degree: null|string, school: null|string, state: null|string, verified: bool|null}|null
     */
    final public static function getExtendedProfileDataByPk(?int $identityId): ?array {
        if (is_null($identityId)) {
            return null;
        }
        $sql = 'SELECT
                    IFNULL(c.`name`, "xx") AS country,
                    s.`name` AS state,
                    sc.`name` AS school,
                    isc.`graduation_date` AS graduation_date,
                    e.`email`,
                    l.`name` AS locale,
                    u.`birth_date`,
                    u.`scholar_degree`,
                    u.`has_learning_objective`,
                    u.`has_teaching_objective`,
                    u.`has_scholar_objective`,
                    u.`has_competitive_objective`,
                    u.`hide_problem_tags`,
                    u.`verified`,
                    i.`gender`,
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Identities i
                LEFT JOIN
                    Users u ON u.user_id = i.user_id
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
                    i.`identity_id` = ?
                LIMIT
                    1;';
        /** @var array{birth_date: null|string, classname: string, country: string, email: null|string, gender: null|string, graduation_date: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool|null, locale: null|string, scholar_degree: null|string, school: null|string, state: null|string, verified: bool|null}|null */
        $identity = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$identityId]
        );
        if (is_null($identity)) {
            return null;
        }

        $identity['has_learning_objective'] = boolval(
            $identity['has_learning_objective']
        );
        $identity['has_teaching_objective'] = boolval(
            $identity['has_teaching_objective']
        );
        $identity['has_scholar_objective'] = boolval(
            $identity['has_scholar_objective']
        );
        $identity['has_competitive_objective'] = boolval(
            $identity['has_competitive_objective']
        );
        $identity['hide_problem_tags'] = boolval(
            $identity['hide_problem_tags']
        );
        $identity['birth_date'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $identity['birth_date']
        );
        $identity['graduation_date'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $identity['graduation_date']
        );

        return $identity;
    }

    public static function isUserAssociatedWithIdentityOfGroup(
        int $userId,
        int $identityId
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
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

        return (
            /** @var array{associated: int} */
            \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $args)
        ) > 0;
    }

    public static function getUnassociatedIdentity(
        string $username
    ): ?\OmegaUp\DAO\VO\Identities {
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        ) . '
            FROM
                Identities i
            WHERE
                i.username = ?
            LIMIT 1;';
        $args = [$username];

        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $args);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($rs);
    }

    /**
     * @return list<array{username: string, default: bool}>
     */
    public static function getAssociatedIdentities(
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        if (is_null($identity->user_id)) {
            return [];
        }
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
            UNION
            SELECT
                i.username,
                i.identity_id,
                u.main_identity_id
            FROM
                Identities i
            INNER JOIN
                Teams t
            ON
                i.identity_id = t.identity_id
            INNER JOIN
                Team_Users tu
            ON
                tu.team_id = t.team_id
            INNER JOIN
                Identities it
            ON
                tu.identity_id = it.identity_id
            INNER JOIN
                Users u
            ON
                it.user_id = u.user_id
            WHERE
                it.identity_id = ?
                ';

        /** @var list<array{identity_id: int, main_identity_id: int|null, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identity->user_id, $identity->identity_id]
        );
        $result = [];
        foreach ($rs as $identity) {
            $result[] = [
                'username' => $identity['username'],
                'default' => $identity['identity_id'] == $identity['main_identity_id'],
            ];
        }
        return $result;
    }

    public static function associateIdentityWithUser(
        int $userId,
        int $identityId
    ): int {
        $sql = '
            UPDATE
                Identities
            SET
                user_id = ?
            WHERE
                identity_id = ?
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$userId, $identityId]
        );

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return array{gender: string, users: int}[]
     */
    public static function countActiveUsersByGender(
        int $startTimestamp,
        int $endTimestamp
    ): array {
        $start = \OmegaUp\DAO\DAO::toMySQLTimestamp($startTimestamp);
        $end   = \OmegaUp\DAO\DAO::toMySQLTimestamp($endTimestamp);
        $sql = '
            SELECT
                "total" AS gender,
                COUNT(DISTINCT ill.identity_id) AS users
            FROM
                Identity_Login_Log ill
            WHERE
                ill.time BETWEEN ? AND ?
            UNION
            SELECT
                IFNULL(i.gender, "unknown") AS gender,
                COUNT(DISTINCT ill.identity_id) AS users
            FROM
                Identity_Login_Log ill
            INNER JOIN
                Identities i ON i.identity_id = ill.identity_id
            WHERE
                ill.time BETWEEN ? AND ?
            GROUP BY
                gender;
            ';
        /** @var array{gender: string, users: int}[] */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$start, $end, $start, $end]
        );
    }

    public static function isMainIdentity(
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        $sql = 'SELECT
                    COUNT(*) AS main_identity
                FROM
                    Users u
                WHERE
                    u.main_identity_id = ?
                LIMIT 1;';

        return (
            /** @var array{main_identity: int} */
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                $sql,
                [$identity->identity_id]
            )
        ) > 0;
    }

    /**
     * @return array{classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null
     */
    public static function getTeamIdentity(
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'ti'
        ) . ',
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Identities i
                INNER JOIN
                    Team_Users tu ON tu.identity_id = i.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                INNER JOIN
                    Teams t ON t.team_id = tu.team_id
                INNER JOIN
                    Identities ti ON ti.identity_id = t.identity_id
                WHERE
                    i.identity_id = ?
                LIMIT 1;';

        /** @var array{classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$identity->identity_id]
        );
    }
}
