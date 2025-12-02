<?php

namespace OmegaUp\DAO;

/**
 * Contests Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Contests}.
 * @access public
 * @package docs
 *
 * @psalm-type Contest=array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, original_finish_time: \OmegaUp\Timestamp, score_mode: string, problemset_id: int, recommended: bool, rerun_id: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}
 * @psalm-type Contestv2=array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, score_mode: string, participating: bool, problemset_id: int, recommended: bool, rerun_id: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}
 * @psalm-type ContestListItem=array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: bool, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode?: string, scoreboard_url?: string, scoreboard_url_admin?: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}
 */
class Contests extends \OmegaUp\DAO\Base\Contests {
    /** @var string */
    private static $getContestsColumns = '
                                Contests.contest_id,
                                Contests.problemset_id,
                                Contests.title,
                                Contests.description,
                                Contests.finish_time AS original_finish_time,
                                Contests.start_time,
                                Contests.finish_time,
                                Contests.admission_mode,
                                Contests.score_mode,
                                Contests.alias,
                                Contests.recommended,
                                Contests.window_length,
                                Contests.last_updated,
                                Contests.rerun_id
                                ';

    /** @var string */
    private static $cteContestContestants = 'WITH pic AS (
        SELECT
            pp.contest_id,
            COUNT(*) AS contestants
        FROM
            Problemsets pp
        INNER JOIN
            Problemset_Identities pi
        ON
            pp.problemset_id = pi.problemset_id
        GROUP BY
            pp.contest_id
    )';

    final public static function getByAlias(string $alias): ?\OmegaUp\DAO\VO\Contests {
        $sql = 'SELECT ' .
        join(', ', array_keys(\OmegaUp\DAO\VO\Contests::FIELD_NAMES)) . ' ' .
        'FROM Contests ' . 'WHERE alias = ? LIMIT 1;';

        /** @var array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, check_plagiarism: bool, contest_for_teams: bool|null, contest_id: int, default_show_all_contestants_in_scoreboard: bool|null, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, plagiarism_threshold: bool, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Contests($rs);
    }

    /**
     * @return array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, contest_for_teams: bool|null, contest_id: int, description: string, director: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: string, last_updated: \OmegaUp\Timestamp, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, default_show_all_contestants_in_scoreboard: bool, show_penalty: bool, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null
     */
    final public static function getByAliasWithDirector(string $alias) {
        $sql = 'SELECT
                    i.username AS director,
                    c.contest_id,
                    c.problemset_id,
                    c.acl_id,
                    c.title,
                    c.description,
                    c.start_time,
                    c.finish_time,
                    c.last_updated,
                    c.window_length,
                    c.rerun_id,
                    c.admission_mode,
                    c.alias,
                    c.scoreboard,
                    c.points_decay_factor,
                    c.submissions_gap,
                    c.feedback,
                    c.penalty,
                    c.penalty_type,
                    c.penalty_calc_policy,
                    c.default_show_all_contestants_in_scoreboard,
                    c.show_scoreboard_after,
                    IF(c.penalty <> 0 OR c.penalty_type <> \'none\', 1, 0) AS show_penalty,
                    c.urgent,
                    c.recommended,
                    c.archived,
                    c.certificate_cutoff,
                    c.certificates_status,
                    c.contest_for_teams,
                    c.score_mode,
                    c.languages
                FROM
                    Contests c
                INNER JOIN
                    ACLs acl
                ON
                    acl.acl_id = c.acl_id
                INNER JOIN
                    Identities i
                ON
                    i.user_id = acl.owner_id
                WHERE
                    c.alias = ?
                LIMIT
                    1;';

        /** @var array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, contest_for_teams: bool|null, contest_id: int, default_show_all_contestants_in_scoreboard: bool|null, description: string, director: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, show_penalty: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($rs)) {
            return null;
        }
        $rs['default_show_all_contestants_in_scoreboard'] = boolval(
            $rs['default_show_all_contestants_in_scoreboard']
        );
        $rs['show_penalty'] = boolval($rs['show_penalty']);
        $rs['languages'] = strval($rs['languages']);
        return $rs;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Contests>
     */
    final public static function getByTitle(string $title) {
        $sql = 'SELECT ' .
        join(', ', array_keys(\OmegaUp\DAO\VO\Contests::FIELD_NAMES)) . ' ' .
        'FROM Contests ' . 'WHERE title = ? and archived = 0;';

        /** @var list<array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, check_plagiarism: bool, contest_for_teams: bool|null, contest_id: int, default_show_all_contestants_in_scoreboard: bool|null, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, plagiarism_threshold: bool, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$title]);

        $contests = [];
        foreach ($rs as $row) {
            $contests[] = new \OmegaUp\DAO\VO\Contests($row);
        }
        return $contests;
    }

    /**
     * @return array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, contest_for_teams: bool|null, contest_id: int, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, default_show_all_contestants_in_scoreboard: bool|null, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null
     */
    final public static function getByAliasWithExtraInformation(string $alias): ?array {
        $fields = join(
            '',
            array_map(
                fn (string $field): string => "c.{$field}, ",
                array_keys(
                    \OmegaUp\DAO\VO\Contests::FIELD_NAMES
                )
            )
        );
        $sql = '
                SELECT
                    ' . $fields . '
                    p.scoreboard_url,
                    p.scoreboard_url_admin
                FROM
                    Contests c
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = c.problemset_id
                WHERE
                    c.alias = ?
                LIMIT 1;';
        $params = [$alias];

        /** @var array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, check_plagiarism: bool, contest_for_teams: bool|null, contest_id: int, default_show_all_contestants_in_scoreboard: bool|null, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, plagiarism_threshold: bool, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return $rs;
    }

    final public static function getByProblemset(
        int $problemsetId
    ): ?\OmegaUp\DAO\VO\Contests {
        $fields = join(', ', array_keys(\OmegaUp\DAO\VO\Contests::FIELD_NAMES));
        $sql = 'SELECT
                    ' . $fields . '
                FROM
                    Contests
                WHERE
                    problemset_id = ?
                LIMIT 0, 1;';
        /** @var array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificate_cutoff: int|null, certificates_status: string, check_plagiarism: bool, contest_for_teams: bool|null, contest_id: int, default_show_all_contestants_in_scoreboard: bool|null, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, languages: null|string, last_updated: \OmegaUp\Timestamp, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, plagiarism_threshold: bool, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard: int, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, urgent: bool, window_length: int|null}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemsetId]
        );
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Contests($row);
    }

    public static function getPrivateContestsCount(
        \OmegaUp\DAO\VO\Users $user
    ): int {
        if (is_null($user->user_id)) {
            return 0;
        }
        $sql = 'SELECT
           COUNT(c.contest_id) as total
        FROM
            Contests AS c
        INNER JOIN
            ACLs AS a
        ON
            a.acl_id = c.acl_id
        WHERE
            admission_mode = \'private\'
            AND a.owner_id = ?
            AND archived = 0;';
        $params = [$user->user_id];
        /** @var array{total: int} */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);

        if (!array_key_exists('total', $rs)) {
            return 0;
        }

        return intval($rs['total']);
    }

    public static function hasStarted(\OmegaUp\DAO\VO\Contests $contest): bool {
        return \OmegaUp\Time::get() >= $contest->start_time->time;
    }

    public static function hasFinished(\OmegaUp\DAO\VO\Contests $contest): bool {
        return \OmegaUp\Time::get() >= $contest->finish_time->time;
    }

    /**
     * @return list<array{contest: \OmegaUp\DAO\VO\Contests, problemset: \OmegaUp\DAO\VO\Problemsets}>
     */
    public static function getContestsParticipated(int $identityId) {
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Contests::FIELD_NAMES,
            'c'
        ) . ',
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problemsets::FIELD_NAMES,
            'p'
        ) . '
            FROM
                Contests c
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = c.problemset_id
            WHERE c.contest_id IN (
                SELECT DISTINCT
                    c2.contest_id
                FROM
                    Submissions s
                INNER JOIN
                    Contests c2
                ON
                    c2.problemset_id = s.problemset_id
                WHERE
                    s.identity_id = ?
                    AND s.type= \'normal\'
                    AND s.problemset_id IS NOT NULL
                    AND archived = 0
            )
            ORDER BY
                c.contest_id DESC;';

        /** @var list<array{access_mode: string, acl_id: int, acl_id: int, admission_mode: string, alias: string, archived: bool, assignment_id: int|null, certificate_cutoff: int|null, certificates_status: string, check_plagiarism: bool, contest_for_teams: bool|null, contest_id: int, contest_id: int|null, default_show_all_contestants_in_scoreboard: bool|null, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, interview_id: int|null, languages: null|string, languages: null|string, last_updated: \OmegaUp\Timestamp, needs_basic_information: bool, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, plagiarism_threshold: bool, points_decay_factor: float, problemset_id: int, problemset_id: int, recommended: bool, requests_user_information: string, rerun_id: int|null, score_mode: string, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, type: string, urgent: bool, window_length: int|null}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId]
        );

        /** @var list<array{contest: \OmegaUp\DAO\VO\Contests, problemset: \OmegaUp\DAO\VO\Problemsets}> */
        $response = [];
        foreach ($result as $contestProblemset) {
            $response[] = [
                'contest' => new \OmegaUp\DAO\VO\Contests(
                    array_intersect_key(
                        $contestProblemset,
                        \OmegaUp\DAO\VO\Contests::FIELD_NAMES
                    )
                ),
                'problemset' => new \OmegaUp\DAO\VO\Problemsets(
                    array_intersect_key(
                        $contestProblemset,
                        \OmegaUp\DAO\VO\Problemsets::FIELD_NAMES
                    )
                ),
            ];
        }
        return $response;
    }

    /**
     * Returns all contests that an identity can manage.
     *
     * @return list<Contest>
     */
    final public static function getAllContestsAdminedByIdentity(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000
    ) {
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $sql = "
            SELECT
                $columns,
                ps.scoreboard_url,
                ps.scoreboard_url_admin
            FROM
                Contests
            INNER JOIN
                Problemsets AS ps ON ps.problemset_id = Contests.problemset_id
            INNER JOIN
                ACLs AS a ON a.acl_id = Contests.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = Contests.acl_id
            LEFT JOIN
                Identities uri ON uri.user_id = ur.user_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = Contests.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                ai.identity_id = ? OR
                (ur.role_id = ? AND uri.identity_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)
                AND archived = 0
            GROUP BY
                Contests.contest_id
            ORDER BY
                Contests.contest_id DESC
            LIMIT ?, ?;";

        $params = [
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            max(0, $page - 1) * $pageSize,
            $pageSize,
        ];

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Get relevant columns of all contests, including scoreboard_url columns
     *
     * @return list<Contest>
     */
    final public static function getAllContestsWithScoreboard(
        ?int $page = 1,
        int $pageSize = 1000,
        ?string $order = null,
        string $orderType = 'ASC',
        bool $showArchived = false
    ) {
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $sql = "
            SELECT
                $columns,
                ps.scoreboard_url,
                ps.scoreboard_url_admin
            FROM
                Contests
            INNER JOIN
                Problemsets ps ON ps.problemset_id = Contests.problemset_id
            WHERE
                archived = ?";

        if (!is_null($order)) {
            $sql .= ' ORDER BY `Contests`.`' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' .
                    ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($page)) {
            $sql .= ' LIMIT ' . (($page - 1) * $pageSize) . ', ' . intval(
                $pageSize
            );
        }

        /** @var list<Contest> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$showArchived]
        );
    }

    /**
     * Returns all contests owned by a user.
     *
     * @return array{contests: list<Contest>, count: int}
     */
    final public static function getAllContestsOwnedByUser(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000,
        bool $showArchived = false
    ): array {
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;

        $sqlCount = 'SELECT
                        COUNT(*)
                    ';

        $select = "SELECT
                        $columns,
                        p.scoreboard_url,
                        p.scoreboard_url_admin";

        $sql = '
            FROM
                Contests
            INNER JOIN
                ACLs a ON a.acl_id = Contests.acl_id
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            INNER JOIN
                Problemsets p ON p.problemset_id = Contests.problemset_id
            WHERE
                u.main_identity_id = ?
                AND archived = ?';

        $params = [
            $identityId,
            $showArchived,
        ];

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount} {$sql}",
            $params
        );

        $limits = '
            ORDER BY
                Contests.contest_id DESC
            LIMIT ?, ?;';
        $params[] = max(0, $page - 1) * $pageSize;
        $params[] = intval($pageSize);

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        $contests = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits}",
            $params
        );
        return [
            'contests' => $contests,
            'count' => $count,
        ];
    }

    /**
     * Returns the list of contests created by a certain identity
     *
     * @return list<Contest>
     */
    final public static function getContestsCreatedByIdentity(
        int $identityId
    ) {
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $sql = "
            SELECT
                $columns,
                p.scoreboard_url,
                p.scoreboard_url_admin
            FROM
                Contests
            INNER JOIN
                ACLs a ON a.acl_id = Contests.acl_id
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            INNER JOIN
                Problemsets p ON p.problemset_id = Contests.problemset_id
            WHERE
                u.main_identity_id = ?
                AND archived = false
            ORDER BY
                Contests.contest_id DESC;";

        $params = [
            $identityId,
        ];

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Returns all contests where a user is participating in.
     *
     * @return array{contests: list<Contestv2>, count: int}
     */
    final public static function getContestsParticipating(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000,
        int $active = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        ?string $query = null,
        int $orderBy = 0
    ) {
        $activeCondition = \OmegaUp\DAO\Enum\ActiveStatus::sql(
            $active
        );
        $recommendedCondition = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ALL
        );
        $filter = self::formatSearch($query);
        $queryCondition = \OmegaUp\DAO\Enum\FilteredStatus::sql(
            $filter['type']
        );
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $cteCountContestants = self::$cteContestContestants;

        $sqlCount = "{$cteCountContestants}
                    SELECT
                        COUNT(*)
                    ";

        $select = "{$cteCountContestants}
                    SELECT
                        $columns,
                        p.scoreboard_url,
                        p.scoreboard_url_admin,
                        COALESCE(contestants, 0) AS contestants,
                        TIMESTAMPDIFF(MINUTE, start_time, finish_time) AS duration_minutes,
                        1 AS participating,
                        ANY_VALUE(organizer.username) AS organizer";

        $sql = "
            FROM
                (SELECT
                    pi.problemset_id
                FROM
                    Problemset_Identities pi
                WHERE
                    pi.identity_id = ?
                UNION DISTINCT
                SELECT
                    p.problemset_id
                FROM
                    Groups_Identities gi
                INNER JOIN
                    Group_Roles gr ON gi.group_id = gr.group_id
                INNER JOIN
                    Problemsets p ON gr.acl_id = p.acl_id
                WHERE
                    gi.identity_id = ? AND gr.role_id = ?
                UNION DISTINCT
                SELECT
                    p.problemset_id
                FROM
                    Teams t
                INNER JOIN
                    Teams_Group_Roles tgr ON t.team_group_id = tgr.team_group_id
                INNER JOIN
                    Problemsets p ON tgr.acl_id = p.acl_id
                WHERE
                    t.identity_id = ? AND tgr.role_id = ?
                ) pps
            INNER JOIN
                Contests
            ON
                Contests.problemset_id = pps.problemset_id
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = Contests.problemset_id
            INNER JOIN
                ACLs AS a
            ON
                Contests.acl_id = a.acl_id
            INNER JOIN
                Identities AS organizer
            ON
                a.owner_id = organizer.user_id
            LEFT JOIN
                pic
            ON
                pic.contest_id = Contests.contest_id
            WHERE
                $recommendedCondition AND
                $activeCondition AND
                $queryCondition AND
                archived = 0
            GROUP BY Contests.contest_id, pic.contestants
        ";
        $params = [
            // Direct participation
            $identityId,
            // Group participation
            $identityId,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            // Team participation
            $identityId,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
        ];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount} {$sql}",
            $params
        );

        $order = self::getOrder($orderBy, 'finish_time');

        $limits = '
            ORDER BY ';
        if ($orderBy === \OmegaUp\DAO\Enum\ContestOrderStatus::NONE) {
            $limits .= 'recommended DESC, ';
        }
        $limits .= "
                {$order}
            LIMIT ?, ?;
        ";
        $params[] = max(0, $page - 1) * $pageSize;
        $params[] = intval($pageSize);

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: int, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits}",
            $params
        );

        $contests = [];
        foreach ($rs as $row) {
            $row['participating'] = boolval($row['participating']);
            $contests[] = $row;
        }
        return [
            'contests' => $contests,
            'count' => $count,
        ];
    }

    /**
     * Returns the next contest (active or future) a user registered for, when it is
     * a future contest it can be filtered by a limit of days between the start of the
     * next registered contest and the current date using the $dayLimit param.
     *
     * @return ContestListItem|null
     */
    final public static function getNextRegisteredContestForUser(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?int $dayLimit = 15
    ) {
        if (is_null($identity) || is_null($identity->identity_id)) {
            return null;
        }

        $activeCondition = \OmegaUp\DAO\Enum\ActiveStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE
        );
        $futureCondition = \OmegaUp\DAO\Enum\ActiveStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::FUTURE
        );
        $withinDayLimitCondition = 'TRUE';
        if (!is_null($dayLimit)) {
            $withinDayLimitCondition = "DATEDIFF(start_time, NOW()) < $dayLimit";
        }

        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;

        $select = "SELECT
                        $columns,
                        p.scoreboard_url,
                        p.scoreboard_url_admin,
                        0 AS contestants,
                        TIMESTAMPDIFF(MINUTE, start_time, finish_time) AS duration_minutes,
                        ANY_VALUE(organizer.username) AS organizer";

        $sql = "
            FROM
                (SELECT
                    pi.problemset_id
                FROM
                    Problemset_Identities pi
                WHERE
                    pi.identity_id = ?
                UNION DISTINCT
                SELECT
                    p.problemset_id
                FROM
                    Groups_Identities gi
                INNER JOIN
                    Group_Roles gr ON gi.group_id = gr.group_id
                INNER JOIN
                    Problemsets p ON gr.acl_id = p.acl_id
                WHERE
                    gi.identity_id = ? AND gr.role_id = ?
                UNION DISTINCT
                SELECT
                    p.problemset_id
                FROM
                    Teams t
                INNER JOIN
                    Teams_Group_Roles tgr ON t.team_group_id = tgr.team_group_id
                INNER JOIN
                    Problemsets p ON tgr.acl_id = p.acl_id
                WHERE
                    t.identity_id = ? AND tgr.role_id = ?
                ) pps
            INNER JOIN
                Contests
            ON
                Contests.problemset_id = pps.problemset_id
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = Contests.problemset_id
            INNER JOIN
                ACLs AS a
            ON
                Contests.acl_id = a.acl_id
            INNER JOIN
                Identities AS organizer
            ON
                a.owner_id = organizer.user_id
            WHERE
                ($activeCondition OR
                ($futureCondition AND $withinDayLimitCondition)) AND
                archived = 0
            GROUP BY Contests.contest_id
        ";
        $params = [
            // Direct participation
            $identity->identity_id,
            // Group participation
            $identity->identity_id,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            // Team participation
            $identity->identity_id,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
        ];

        $limits = '
            ORDER BY
                start_time ASC,
                finish_time ASC
            LIMIT 1;
        ';

        /** @var array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}|null */
        $contest = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            "{$select} {$sql} {$limits}",
            $params
        );

        if (!is_null($contest)) {
            $contest['participating'] = true;
        }

        return $contest;
    }

    /**
     * Returns all recent public contests.
     *
     * @return array{contests: list<ContestListItem>, count: int}
     */
    final public static function getRecentPublicContests(
        int $identity_id,
        int $page = 1,
        int $pageSize = 1000,
        ?string $query = null,
        int $orderBy = 0
    ) {
        $endCheck = \OmegaUp\DAO\Enum\ActiveStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE
        );
        $recommendedCheck = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            \OmegaUp\DAO\Enum\ActiveStatus::ALL
        );
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $filter = self::formatSearch($query);
        $queryCheck = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);
        $cteCountContestants = self::$cteContestContestants;

        $sqlCount = "{$cteCountContestants}
                    SELECT
                        COUNT(*)
                    ";

        $select = "{$cteCountContestants}
                SELECT
                    $columns,
                    COALESCE(contestants, 0) AS contestants,
                    ANY_VALUE(organizer.username) AS organizer,
                    TIMESTAMPDIFF(MINUTE, start_time, finish_time) AS duration_minutes,
                    (participating.identity_id IS NOT NULL) AS `participating`";

        $sql = "
            FROM
                Contests
            INNER JOIN
                ACLs AS a
            ON
                Contests.acl_id = a.acl_id
            INNER JOIN
                Identities AS organizer
            ON
                a.owner_id = organizer.user_id
            LEFT JOIN
                Problemset_Identities participating
            ON
                Contests.problemset_id = participating.problemset_id AND
                participating.identity_id = ?
            LEFT JOIN
                pic
            ON
                pic.contest_id = Contests.contest_id
            WHERE
                $recommendedCheck  AND $endCheck AND $queryCheck
                AND `admission_mode` != 'private'
                AND archived = 0";

        $params = [$identity_id];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount} {$sql}",
            $params
        );

        $order = self::getOrder($orderBy);

        $limits = "
            ORDER BY
                {$order},
                `last_updated` DESC,
                `recommended` DESC,
                `finish_time` DESC,
                `contest_id` DESC
            LIMIT ?, ?;";
        $params[] = max(0, $page - 1) * $pageSize;
        $params[] = intval($pageSize);

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: int, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits}",
            $params
        );

        $contests = [];
        foreach ($rs as $row) {
            $row['participating'] = boolval($row['participating']);
            $contests[] = $row;
        }
        return [
            'contests' => $contests,
            'count' => $count,
        ];
    }

    /**
     * Regresa todos los concursos que una identidad puede ver.
     *
     * Explicación:
     *
     * La estructura de este query optimiza el uso de índices en mysql.
     *
     * El primer SELECT transforma las columnas a como las espera la API.
     * Luego:
     *
     * Todos los concursos privados donde la identidad fue el creador
     * UNION
     * Todos los concursos privados a los que la identidad ha sido invitada
     * UNION
     * Todos los concursos privados a los que la identidad es ADMIN
     * UNION
     * Todos los concursos privados donde la identidad pertenece a un grupo que es ADMIN del concurso
     * UNION
     * Todos los concursos públicos.
     *
     * @return array{contests: list<ContestListItem>, count: int}
     */
    final public static function getAllContestsForIdentity(
        int $identityId,
        int $page = 1,
        int $rowsPerPage = 1000,
        int $activeContests = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        int $recommendedContests = \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
        ?string $query = null,
        int $orderBy = 0
    ): array {
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $endCheck = \OmegaUp\DAO\Enum\ActiveStatus::sql($activeContests);
        $recommendedCheck = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            $recommendedContests
        );
        $filter = self::formatSearch($query);
        $queryCheck = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);
        $cteCountContestants = self::$cteContestContestants;

        $sqlRelevantContests = "
        -- Organizer
        (SELECT
            c.contest_id,
            FALSE AS participating
        FROM
            Identities organizer
        INNER JOIN
            ACLs a ON a.owner_id = organizer.user_id
        INNER JOIN
            Contests c ON c.acl_id = a.acl_id
        WHERE
            organizer.identity_id = ?
        )
        -- Direct participant
        UNION DISTINCT
        (SELECT
            c.contest_id,
            TRUE AS participating
        FROM
            Problemset_Identities pi
        INNER JOIN
            Contests c ON c.problemset_id = pi.problemset_id
        WHERE
            pi.identity_id = ?
        )
        -- Participant via Group
        UNION DISTINCT
        (SELECT
            p.contest_id,
            TRUE AS participating
        FROM
            Groups_Identities gi
        INNER JOIN
            Group_Roles gr ON gi.group_id = gr.group_id
        INNER JOIN
            Problemsets p ON gr.acl_id = p.acl_id
        WHERE
            gi.identity_id = ? AND gr.role_id = ?
        )
        -- Participating via Teams group
        UNION DISTINCT
        (SELECT
            p.contest_id,
            TRUE AS participating
        FROM
            Teams t
        INNER JOIN
            Teams_Group_Roles tgr ON t.team_group_id = tgr.team_group_id
        INNER JOIN
            Problemsets p ON tgr.acl_id = p.acl_id
        WHERE
            t.identity_id = ? AND tgr.role_id = ?
        )
        -- Admin
        UNION DISTINCT
        (SELECT
            contest_id,
            FALSE AS participating
        FROM
            Identities i
        INNER JOIN
            User_Roles ur ON ur.user_id = i.user_id
        INNER JOIN
            Contests c ON c.acl_id = ur.acl_id
        WHERE
            i.identity_id = ? AND ur.role_id = ?
        )
        -- Admin via Group
        UNION DISTINCT
        (SELECT
            contest_id,
            FALSE AS participating
        FROM
            Groups_Identities gi
        INNER JOIN
            Group_Roles gr ON gi.group_id = gr.group_id
        INNER JOIN
            Contests c ON c.acl_id = gr.acl_id
        WHERE
            gi.identity_id = ? AND gr.role_id = ?
        )
        -- Public
        UNION DISTINCT
        (SELECT
            contest_id,
            (participating.identity_id IS NOT NULL) AS participating
        FROM
            Contests
        LEFT JOIN
            Problemset_Identities participating
        ON
            participating.problemset_id = Contests.problemset_id AND
            participating.identity_id = ?
        WHERE
            admission_mode <> 'private'
        )
        ";

        $sqlCount = "{$cteCountContestants}
                    SELECT
                        COUNT(*) AS number_of_rows
                    ";

        $select = "{$cteCountContestants}
                    SELECT
                        $columns,
                        COALESCE(contestants, 0) AS contestants,
                        ANY_VALUE(organizer.username) AS organizer,
                        IF(
                            window_length IS NULL,
                            TIMESTAMPDIFF(
                                MINUTE, start_time,
                                finish_time
                            ),
                            window_length
                        ) AS duration_minutes,
                        BIT_OR(rc.participating) AS participating";
        $sql = "
        FROM
            ($sqlRelevantContests) rc
        INNER JOIN
            Contests ON Contests.contest_id = rc.contest_id
        INNER JOIN
            ACLs a ON a.acl_id = Contests.acl_id
        INNER JOIN
            Identities organizer ON organizer.user_id = a.owner_id
        LEFT JOIN
            pic ON pic.contest_id = Contests.contest_id
        WHERE
            $recommendedCheck AND $endCheck AND $queryCheck
            AND archived = 0
        GROUP BY
            Contests.contest_id, pic.contestants
        ";

        $params = [
            $identityId,    // Organizer
            $identityId,    // Direct participant
            $identityId,    // Participant via Group
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            $identityId,    // Participant via Teams Group
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            $identityId,    // Admin
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,    // Admin via Group
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,    // Participant check
        ];

        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        /** @var list<array{number_of_rows: int}> */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$sqlCount} {$sql}",
            $params
        );

        $order = self::getOrder($orderBy);

        $limits = '
            ORDER BY ';
        if ($orderBy === \OmegaUp\DAO\Enum\ContestOrderStatus::NONE) {
            $limits .= 'recommended DESC, ';
        }
        $limits .= "
                {$order},
                CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC
            LIMIT ?, ?";

        $params[] = max(0, $page - 1) * $rowsPerPage;
        $params[] = intval($rowsPerPage);
        /** @var list<array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: int, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits}",
            $params
        );

        $contests = [];
        foreach ($rs as $row) {
            $row['participating'] = boolval($row['participating']);
            $contests[] = $row;
        }
        return [
            'contests' => $contests,
            'count' => count($count),
        ];
    }

    /**
     * @return array{contests: list<ContestListItem>, count: int}
     */
    final public static function getAllPublicContests(
        int $page = 1,
        int $rowsPerPage = 1000,
        int $activeContests = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        int $recommendedContests = \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
        ?string $query = null,
        int $orderBy = 0
    ): array {
        $endCheck = \OmegaUp\DAO\Enum\ActiveStatus::sql($activeContests);
        $recommendedCheck = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            $recommendedContests
        );
        $filter = self::formatSearch($query);
        $queryCheck = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);

        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $cteCountContestants = self::$cteContestContestants;

        $sqlCount = "{$cteCountContestants}
                    SELECT
                        COUNT(*)
                    ";

        $select = "{$cteCountContestants}
                    SELECT
                        $columns,
                        COALESCE(contestants, 0) AS contestants,
                        ANY_VALUE(organizer.username) AS organizer,
                        IF(
                            window_length IS NULL,
                            TIMESTAMPDIFF(
                                MINUTE,
                                start_time,
                                finish_time
                            ),
                            window_length
                        ) AS duration_minutes,
                        FALSE AS `participating`
                        ";
        $sql = "
                FROM
                    `Contests`
                INNER JOIN
                    ACLs AS a
                ON
                    Contests.acl_id = a.acl_id
                INNER JOIN
                    Identities AS organizer
                ON
                    a.owner_id = organizer.user_id
                LEFT JOIN
                    pic
                ON
                    pic.contest_id = Contests.contest_id
                WHERE
                    `admission_mode` <> 'private'
                    AND $recommendedCheck
                    AND $endCheck
                    AND $queryCheck
                    AND archived = 0";

        $params = [];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount} {$sql}",
            $params
        );

        $order = self::getOrder($orderBy);

        $limits = '
            ORDER BY ';
        if ($orderBy === \OmegaUp\DAO\Enum\ContestOrderStatus::NONE) {
            $limits .= '`recommended` DESC, ';
        }
        $limits .= "
                {$order},
                CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC
            LIMIT ?, ?";
        $params[] = max(0, $page - 1) * $rowsPerPage;
        $params[] = intval($rowsPerPage);
        /** @var list<array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: int, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits}",
            $params
        );

        $contests = [];
        foreach ($rs as $row) {
            $row['participating'] = boolval($row['participating']);
            $contests[] = $row;
        }
        return [
            'contests' => $contests,
            'count' => $count,
        ];
    }

    /** @return array{contests: list<ContestListItem>, count: int}
     */
    final public static function getAllContests(
        int $page = 1,
        int $rowsPerPage = 1000,
        int $activeContests = \OmegaUp\DAO\Enum\ActiveStatus::ALL,
        int $recommendedContests = \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
        ?string $query = null,
        int $orderBy = 0
    ) {
        $columns = \OmegaUp\DAO\Contests::$getContestsColumns;
        $endCheck = \OmegaUp\DAO\Enum\ActiveStatus::sql($activeContests);
        $recommendedCheck = \OmegaUp\DAO\Enum\RecommendedStatus::sql(
            $recommendedContests
        );
        $filter = self::formatSearch($query);
        $queryCheck = \OmegaUp\DAO\Enum\FilteredStatus::sql($filter['type']);
        $cteCountContestants = self::$cteContestContestants;

        $sqlCount = "{$cteCountContestants}
                    SELECT
                        COUNT(*)
                    ";

        $select = "{$cteCountContestants}
                    SELECT
                        $columns,
                        COALESCE(contestants, 0) AS contestants,
                        ANY_VALUE(organizer.username) AS organizer,
                        TIMESTAMPDIFF(MINUTE, start_time, finish_time) AS duration_minutes,
                        TRUE AS participating";
        $sql = "
                FROM
                    Contests
                INNER JOIN
                    ACLs AS a
                ON
                    Contests.acl_id = a.acl_id
                INNER JOIN
                    Identities AS organizer
                ON
                    a.owner_id = organizer.user_id
                LEFT JOIN
                    pic
                ON
                    pic.contest_id = Contests.contest_id
                WHERE $recommendedCheck AND $endCheck AND $queryCheck AND archived = 0
                ";

        $params = [];
        if ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT) {
            $params[] = $filter['query'];
        } elseif ($filter['type'] === \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE) {
            $params[] = $filter['query'];
            $params[] = $filter['query'];
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount} {$sql}",
            $params
        );

        $order = self::getOrder($orderBy);

        $limits = '
            ORDER BY ';
        if ($orderBy === \OmegaUp\DAO\Enum\ContestOrderStatus::NONE) {
            $limits .= '`recommended` DESC, ';
        }
        $limits .= "
                {$order},
                CASE WHEN original_finish_time > NOW() THEN 1 ELSE 0 END DESC
            LIMIT ?, ?";

        $params[] = max(0, $page - 1) * $rowsPerPage;
        $params[] = intval($rowsPerPage);
        /** @var list<array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: int, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits}",
            $params
        );

        $contests = [];
        foreach ($rs as $row) {
            $row['participating'] = boolval($row['participating']);
            $contests[] = $row;
        }
        return [
            'contests' => $contests,
            'count' => $count,
        ];
    }

    public static function getContestForProblemset(?int $problemsetId): ?\OmegaUp\DAO\VO\Contests {
        if (is_null($problemsetId)) {
            return null;
        }

        return \OmegaUp\DAO\Contests::getByProblemset($problemsetId);
    }

    public static function getNumberOfContestants(int $contestId): int {
        $sql = 'SELECT
                    COUNT(*) AS contestants
                FROM
                    Problemsets p
                INNER JOIN
                    Problemset_Identities pi
                ON
                    p.problemset_id = pi.problemset_id
                WHERE
                    p.contest_id = ?
                GROUP BY
                    p.contest_id
                ;';

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$contestId]
        ) ?? 0;
    }

    /**
     * @return array{needsBasicInformation: bool, requestsUserInformation: string}
     */
    public static function getNeedsInformation(int $problemsetId): array {
        $sql = '
                SELECT
                    needs_basic_information,
                    requests_user_information
                FROM
                    Problemsets
                WHERE
                    problemset_id = ?
                LIMIT 1
                ';

        $params = [$problemsetId];

        /** @var array{needs_basic_information: bool, requests_user_information: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        return [
            'needsBasicInformation' => boolval($rs['needs_basic_information']),
            'requestsUserInformation' => $rs['requests_user_information']
        ];
    }

    /**
     * Generate alias of virtual contest / ghost mode
     *
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @param \OmegaUp\DAO\VO\Users $user
     * @return string of unique virtual contest alias
     */
    public static function generateAlias(\OmegaUp\DAO\VO\Contests $contest): string {
        // Virtual contest alias format (alias-virtual-random)
        return (
            substr(strval($contest->alias), 0, 20) .
            '-virtual-' .
            \OmegaUp\SecurityTools::randomString(3)
        );
    }

    /**
     * Check if contest is virtual contest
     */
    public static function isVirtual(\OmegaUp\DAO\VO\Contests $contest): bool {
        return !is_null($contest->rerun_id);
    }

    /**
     * @param null|string $query
     * @return array{type: int, query: string}
     */
    private static function formatSearch(?string $query) {
        if (empty($query)) {
            return ['type' => \OmegaUp\DAO\Enum\FilteredStatus::ALL, 'query' => ''];
        }
        $query = preg_replace('/\s+/', ' ', $query);
        $result = [];
        foreach (explode(' ', $query) as $token) {
            if (strlen($token) <= 3) {
                return ['type' => \OmegaUp\DAO\Enum\FilteredStatus::SIMPLE, 'query' => $query];
            }
            $result[] = '+' . urlencode($token) . '*';
        }
        return ['type' => \OmegaUp\DAO\Enum\FilteredStatus::FULLTEXT, 'query' => join(
            ' ',
            $result
        )];
    }

    /**
     * @return list<array{name: null|string, username: string, email: null|string, gender: null|string, state: null|string, country: null|string, school: null|string}>
     */
    public static function getContestantsInfo(
        int $contestId
    ): array {
        $sql = '
            SELECT
                i.name,
                i.username,
                i.gender,
                IF(pi.share_user_information, e.email, NULL) AS email,
                IF(pi.share_user_information, st.name, NULL) AS state,
                IF(pi.share_user_information, cn.name, NULL) AS country,
                IF(pi.share_user_information, sc.name, NULL) AS school
            FROM
                Users u
            INNER JOIN
                Identities i ON u.main_identity_id = i.identity_id
            INNER JOIN
                Emails e ON e.email_id = u.main_email_id
            LEFT JOIN
                States st ON st.state_id = i.state_id AND st.country_id = i.country_id
            LEFT JOIN
                Countries cn ON cn.country_id = i.country_id
            LEFT JOIN
                Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
            LEFT JOIN
                Schools sc ON sc.school_id = isc.school_id
            INNER JOIN
                Problemset_Identities pi ON pi.identity_id = i.identity_id
            INNER JOIN
                Contests c ON c.problemset_id = pi.problemset_id
            WHERE
                c.contest_id = ?
                AND archived = 0;
        ';

        /** @var list<array{country: null|string, email: null|string, gender: null|string, name: null|string, school: null|string, state: null|string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$contestId]
        );
    }

    public static function requestsUserInformation(int $contestId): bool {
        $sql = '
            SELECT
                requests_user_information
            FROM
                Problemsets p
            WHERE
                contest_id = ?
            LIMIT 1;
        ';
        /** @var string */
        $requestsUsersInfo = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$contestId]
        );

        return $requestsUsersInfo === 'required' || $requestsUsersInfo ===  'optional';
    }

    /**
     * @return array{activity: list<array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int, name: null|string, time: \OmegaUp\Timestamp, username: string}>, totalRows: int}
     */
    public static function getActivityReport(
        \OmegaUp\DAO\VO\Contests $contest,
        int $page,
        int $rowsPerPage
    ) {
        $sql = '(
            SELECT
                i.username,
                NULL AS alias,
                pal.ip,
                pal.`time`,
                IFNULL(ur.classname, "user-rank-unranked") AS classname,
                "open" AS event_type,
                NULL AS clone_result,
                NULL AS clone_token_payload,
                NULL AS name
            FROM
                Problemset_Access_Log pal
            INNER JOIN
                Identities i ON i.identity_id = pal.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            WHERE
                pal.problemset_id = ?
        ) UNION ALL (
            SELECT
                i.username,
                p.alias,
                sl.ip,
                sl.`time`,
                IFNULL(ur.classname, "user-rank-unranked") AS classname,
                "submit" AS event_type,
                NULL AS clone_result,
                NULL AS clone_token_payload,
                NULL AS name
            FROM
                Submission_Log sl
            INNER JOIN
                Identities i ON i.identity_id = sl.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            INNER JOIN
                Submissions s ON s.submission_id = sl.submission_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            WHERE
                sl.problemset_id = ?
        )';

        $sqlOrder = ' ORDER BY time DESC';

        $sqlCount = "
            SELECT
                COUNT(*)
            FROM
                ({$sql}) AS total";

        $sqlLimit = ' LIMIT ?, ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount,
            [$contest->problemset_id, $contest->problemset_id]
        );

        /** @var list<array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int, name: null|string, time: \OmegaUp\Timestamp, username: string}> */
        $activity = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql . $sqlOrder . $sqlLimit,
            [
                $contest->problemset_id,
                $contest->problemset_id,
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
            ]
        );

        return [
            'activity' => $activity,
            'totalRows' => $totalRows,
        ];
    }

    private static function getOrder(
        int $orderBy,
        string $defaultOrder = '`original_finish_time`',
        string $orderMode = 'DESC'
    ): string {
        $order = \OmegaUp\DAO\Enum\ContestOrderStatus::sql(
            $orderBy
        ) ?: $defaultOrder;


        $result = "{$order} {$orderMode}";

        return $result;
    }
}
