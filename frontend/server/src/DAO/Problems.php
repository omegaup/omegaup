<?php

namespace OmegaUp\DAO;

/**
 * Problems Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Problems}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Problems extends \OmegaUp\DAO\Base\Problems {
    /**
     * @param list<string> $tags
     */
    final private static function addTagFilter(
        string $identityType,
        ?int $identityId,
        array $tags,
        bool $requireAllTags,
        string &$sql,
        array &$args,
        array &$clauses
    ): void {
        // Look for problems matching ALL tags or not
        $havingClause = $requireAllTags ? 'HAVING (COUNT(pt.tag_id) = ?)' : '';
        $placeholders = array_fill(0, count($tags), '?');
        $placeholders = join(',', $placeholders);
        $sql .= "
            INNER JOIN (
                SELECT
                    pt.problem_id,
                    BIT_AND(pt.public) as public
                FROM
                    Problems_Tags pt
                INNER JOIN
                    Problems pp
                ON
                    pp.problem_id = pt.problem_id
                WHERE pt.tag_id IN (
                    SELECT t.tag_id
                    FROM Tags t
                    WHERE t.name in ($placeholders)
                )
                AND (pp.allow_user_add_tags = '1' OR pt.source <> 'voted')
                GROUP BY
                    pt.problem_id
                {$havingClause}
            ) ptp ON ptp.problem_id = p.problem_id";
        $args = array_merge($args, $tags);
        if ($requireAllTags) {
            $args[] = count($tags);
        }

        if ($identityType === IDENTITY_NORMAL && !is_null($identityId)) {
            array_push(
                $clauses,
                [
                    '(ptp.public OR id.identity_id = ?)',
                    [$identityId],
                ]
            );
        } elseif ($identityType !== IDENTITY_ADMIN) {
            array_push(
                $clauses,
                [
                    'ptp.public',
                    [],
                ]
            );
        }
    }

    /**
     * @param null|array{0: int, 1: int} $difficultyRange
     * @param list<string> $programmingLanguages
     * @param list<string> $tags
     * @return array{problems: list<array{alias: string, difficulty: float|null, quality_seal: bool, difficulty_histogram: list<int>, points: float, quality: float|null, quality_histogram: list<int>, ratio: float, score: float, tags: list<array{name: string, source: string}>, title: string, visibility: int}>, count: int}
     */
    final public static function byIdentityType(
        string $identityType,
        ?string $language,
        string $orderBy,
        string $order,
        int $offset,
        int $rowcount,
        ?string $query,
        ?int $identityId,
        ?int $userId,
        array $tags,
        int $minVisibility,
        bool $requireAllTags,
        array $programmingLanguages,
        ?array $difficultyRange
    ) {
        // Just in case.
        if ($order !== 'asc' && $order !== 'desc') {
            $order = 'desc';
        }

        $languageJoin = '';
        if (!is_null($language) && $language !== 'all') {
            $languageJoin = '
                INNER JOIN
                    Problems_Languages ON Problems_Languages.problem_id = p.problem_id
                INNER JOIN
                    Languages ON Problems_Languages.language_id = Languages.language_id
                    AND Languages.name = \'' . $language . '\'
            ';
        }

        // Use BINARY mode to force case sensitive comparisons when ordering by title.
        $collation = ($orderBy === 'title') ? 'COLLATE utf8mb4_bin' : '';
        $select = '';
        $sql = '';
        $args = [];

        // Clauses is an array of 2-tuples that contains a chunk of SQL and the
        // arguments that are needed for that chunk.
        /** @var list<array{0: string, 1: list<string>}> */
        $clauses = [];
        foreach ($programmingLanguages as $programmingLanguage) {
            $clauses[] = [
                'FIND_IN_SET(?, p.languages) > 0',
                [$programmingLanguage],
            ];
        }
        if (!is_null($difficultyRange) && count($difficultyRange) === 2) {
            $conditions = 'p.difficulty >= ? AND p.difficulty <= ?';
            if ($difficultyRange[0] === 0) {
                $conditions = '(p.difficulty IS NULL OR (p.difficulty >= ? AND p.difficulty <= ?))';
            }

            $clauses[] = [
                $conditions,
                $difficultyRange,
            ];
        }

        if (!is_null($query)) {
            $clauses[] = [
                "(p.title LIKE CONCAT('%', ?, '%') OR p.alias LIKE CONCAT('%', ?, '%'))",
                [$query, $query],
            ];
        }

        if ($identityType === IDENTITY_ADMIN) {
            $args = [$identityId];
            $select = '
                SELECT
                    ROUND(100 / LOG2(GREATEST(accepted, 1) + 1), 2)   AS points,
                    accepted / GREATEST(1, submissions)     AS ratio,
                    ROUND(100 * IFNULL(ps.score, 0.0))      AS score,
                    p.*
            ';
            $sql = '
                FROM
                    Problems p
                LEFT JOIN (
                    SELECT
                        Problems.problem_id,
                        MAX(Runs.score) AS score
                    FROM
                        Problems
                    INNER JOIN
                        Submissions ON Submissions.problem_id = Problems.problem_id
                    INNER JOIN
                        Runs ON Runs.run_id = Submissions.current_run_id
                    INNER JOIN
                        Identities ON Identities.identity_id = ? AND
                        Submissions.identity_id = Identities.identity_id
                    GROUP BY
                        Problems.problem_id
                    ) ps ON ps.problem_id = p.problem_id ' . $languageJoin;

            $clauses[] = [
                'p.visibility > ?',
                [\OmegaUp\ProblemParams::VISIBILITY_DELETED],
            ];
        } elseif ($identityType === IDENTITY_NORMAL && !is_null($identityId)) {
            $select = '
                SELECT
                    ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                    p.accepted / GREATEST(1, p.submissions)     AS ratio,
                    ROUND(100 * IFNULL(ps.score, 0), 2)   AS score,
                    p.*
            ';
            $sql = '
                FROM
                    Problems p
                INNER JOIN
                    ACLs a
                ON
                    a.acl_id = p.acl_id
                LEFT JOIN (
                    SELECT
                        pi.problem_id,
                        s.identity_id,
                        MAX(r.score) AS score
                    FROM
                        Problems pi
                    INNER JOIN
                        Submissions s ON s.problem_id = pi.problem_id
                    INNER JOIN
                        Runs r ON r.run_id = s.current_run_id
                    INNER JOIN
                        Identities i ON i.identity_id = ? AND s.identity_id = i.identity_id
                    GROUP BY
                        pi.problem_id, s.identity_id
                ) ps ON ps.problem_id = p.problem_id
                LEFT JOIN
                    User_Roles ur ON ur.user_id = ? AND p.acl_id = ur.acl_id AND ur.role_id = ?
                LEFT JOIN
                    Identities id ON id.identity_id = ? AND a.owner_id = id.user_id
                LEFT JOIN (
                    SELECT DISTINCT
                        gr.acl_id
                    FROM
                        Groups_Identities gi
                    INNER JOIN
                        Group_Roles gr ON gr.group_id = gi.group_id
                    WHERE gi.identity_id = ? AND gr.role_id = ?
                ) gr ON p.acl_id = gr.acl_id ' . $languageJoin;
            $args[] = $identityId;
            $args[] = $userId;
            $args[] = \OmegaUp\Authorization::ADMIN_ROLE;
            $args[] = $identityId;
            $args[] = $identityId;
            $args[] = \OmegaUp\Authorization::ADMIN_ROLE;

            $clauses[] = [
                '(p.visibility >= ? OR id.identity_id = ? OR ur.acl_id IS NOT NULL OR gr.acl_id IS NOT NULL)',
                [
                    max(
                        \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
                        $minVisibility
                    ),
                    $identityId,
                ],
            ];
            $clauses[] = [
                'p.visibility > ?',
                [\OmegaUp\ProblemParams::VISIBILITY_DELETED],
            ];
        } elseif ($identityType === IDENTITY_ANONYMOUS) {
            $select = '
                    SELECT
                        0.0 AS score,
                        ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                        accepted / GREATEST(1, p.submissions)  AS ratio,
                        p.* ';
            $sql = '
                    FROM
                        Problems p ' . $languageJoin;

            $clauses[] = [
                'p.visibility >= ?',
                [max(
                    \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
                    $minVisibility
                )],
            ];
        }

        if (!empty($tags)) {
            self::addTagFilter(
                $identityType,
                $identityId,
                $tags,
                $requireAllTags,
                $sql,
                $args,
                $clauses
            );
        }

        // Finally flatten all WHERE clauses, and add a 'WHERE' if applicable.
        if (!empty($clauses)) {
            $sql .= "\nWHERE\n" . implode(
                ' AND ',
                array_map(
                    /** @param array{0: string, 1: list<string>} $clause */
                    function (array $clause): string {
                        return $clause[0];
                    },
                    $clauses
                )
            );
            /** @var array{0: string, 1: list<string>} $clause */
            foreach ($clauses as $clause) {
                $args = array_merge($args, $clause[1]);
            }
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "SELECT COUNT(*) $sql",
            $args
        );

        // Reset the offset to 0 if out of bounds.
        if ($offset < 0 || $offset > $count) {
            $offset = 0;
        }

        if ($orderBy == 'problem_id') {
            $sql .= " ORDER BY p.problem_id {$collation} {$order} ";
        } elseif ($orderBy == 'points' && $order == 'desc') {
            $sql .= ' ORDER BY `points` DESC, `accepted` ASC, `submissions` DESC ';
        } elseif (($orderBy == 'difficulty' || $orderBy == 'quality') && $order == 'asc') {
            $sql .= " ORDER BY p.{$orderBy} IS NULL, p.{$orderBy} ASC";
        } else {
            $sql .= " ORDER BY `{$orderBy}` {$collation} {$order} ";
        }
        $sql .= ' LIMIT ?, ? ';
        $args[] = $offset;
        $args[] = $rowcount;

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, points: float|null, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, ratio: float|null, score: float, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql};",
            $args
        );

        // Only these fields (plus score, points and ratio) will be returned.
        $filters = [
            'title','quality', 'difficulty', 'alias', 'visibility',
            'quality_histogram', 'difficulty_histogram', 'quality_seal',
        ];
        $problems = [];
        $hiddenTags = $identityType !== IDENTITY_ANONYMOUS ? \OmegaUp\DAO\Users::getHideTags(
            $identityId
        ) : false;
        foreach ($result as $row) {
            $problemObject = new \OmegaUp\DAO\VO\Problems(
                array_intersect_key(
                    $row,
                    \OmegaUp\DAO\VO\Problems::FIELD_NAMES
                )
            );
            /** @var array{title: string, quality: null|float, difficulty: null|float, alias: string, visibility: int,quality_histogram: list<int>, difficulty_histogram: list<int>, quality_seal: bool} */
            $problem = $problemObject->asFilteredArray($filters);

            // score, points and ratio are not actually fields of a Problems object.
            $problem['score'] = floatval($row['score']);
            $problem['points'] = floatval($row['points']);
            $problem['ratio'] = floatval($row['ratio']);
            $problem['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problemObject,
                /*$public=*/true,
                /*$showUserTags=*/$row['allow_user_add_tags']
            );
            $problems[] = $problem;
        }
        return [
            'problems' => $problems,
            'count' => $count,
        ];
    }

    final public static function getByAlias(
        string $alias
    ): ?\OmegaUp\DAO\VO\Problems {
        $sql = 'SELECT * FROM Problems WHERE (alias = ? ) LIMIT 1;';
        $params = [$alias];

        /** @var array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
                return null;
        }

        return new \OmegaUp\DAO\VO\Problems($rs);
    }

    /**
     * @return list<array{name: string, source: string}>
     */
    final public static function getTagsForProblem(
        \OmegaUp\DAO\VO\Problems $problem,
        bool $public,
        bool $showUserTags
    ): array {
        $sql = 'SELECT
            t.name,
            pt.source
        FROM
            Problems_Tags pt
        INNER JOIN
            Tags t ON t.tag_id = pt.tag_id
        WHERE
            pt.problem_id = ?';
        if ($public) {
            $sql .= ' AND pt.public = 1';
        }
        if (!$showUserTags) {
            $sql .= ' AND pt.source <> \'voted\'';
        }
        $sql .= ';';

        /** @var list<array{name: string, source: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problem->problem_id]
        );
    }

    final public static function getPracticeDeadline(int $problemId): ?\OmegaUp\Timestamp {
        $sql = '
            SELECT
                MAX(finish_time)
            FROM
                Contests c
            INNER JOIN
                Problemset_Problems pp USING(problemset_id)
            WHERE
                pp.problem_id = ?;
        ';
        /** @var \OmegaUp\Timestamp|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$problemId]
        );
    }

    public static function getProblemsSolvedCount(\OmegaUp\DAO\VO\Identities $identity): int {
        $sql = 'SELECT
            COUNT(*)
        FROM
            Problems p
        INNER JOIN
            Submissions s ON s.problem_id = p.problem_id
        INNER JOIN
            Runs r ON r.run_id = s.current_run_id
        WHERE
            r.verdict = "AC" AND s.type = "normal" AND s.identity_id = ?
        ORDER BY
            p.problem_id DESC;';

        $args = [$identity->identity_id];

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getProblemsSolved(int $identityId): array {
        $sql = '
            SELECT DISTINCT
                p.*
            FROM
                Problems p
            INNER JOIN
                Submissions s ON s.problem_id = p.problem_id
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            WHERE
                r.verdict = "AC" AND s.type = "normal" AND s.identity_id = ?
            ORDER BY
                p.problem_id DESC;
        ';
        $val = [$identityId];

        $problems = [];
        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $problems;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getProblemsUnsolvedByIdentity(
        int $identityId
    ): array {
        $sql = "
            SELECT DISTINCT
                p.*
            FROM
                Identities i
            INNER JOIN
                Submissions s ON s.identity_id = i.identity_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            WHERE
                i.identity_id = ?
            AND
                (SELECT
                    COUNT(*)
                 FROM
                    Submissions ss
                 INNER JOIN
                    Runs r ON r.run_id = ss.current_run_id
                 WHERE
                    ss.identity_id = i.identity_id AND
                    ss.problem_id = p.problem_id AND
                    r.verdict = 'AC'
                ) = 0";

        $params = [$identityId];

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $r) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($r);
        }
        return $problems;
    }

    /**
     * Returns the list of problems created by a certain identity
     *
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getPublicProblemsCreatedByIdentity(
        int $identityId
    ): array {
        $sql = '
            SELECT DISTINCT
                p.*
            FROM
                Identities i
            INNER JOIN
                Users u ON u.user_id = i.user_id
            INNER JOIN
                ACLs a ON a.owner_id = u.user_id
            INNER JOIN
                Problems p ON p.acl_id = a.acl_id
            WHERE
                p.visibility = ? AND
                i.identity_id = ?;';

        $params = [\OmegaUp\ProblemParams::VISIBILITY_PUBLIC, $identityId];

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $r) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($r);
        }
        return $problems;
    }

    /**
     * @return list<array{alias: string, title: string, username: string}>
     */
    final public static function getSolvedProblemsByUsersOfCourse(
        string $courseAlias
    ): array {
        $sql = "
            SELECT
                rp.alias,
                rp.title,
                i.username
            FROM
                Courses c
            INNER JOIN
                Groups_Identities gi
            ON
                c.group_id = gi.group_id
            INNER JOIN
                Identities i
            ON
                gi.identity_id = i.identity_id
            INNER JOIN
                (
                SELECT
                    p.problem_id,
                    p.alias,
                    p.title,
                    s.identity_id
                FROM
                    Submissions s
                INNER JOIN
                    Runs r
                ON
                    r.run_id = s.current_run_id
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = s.problem_id
                WHERE
                    r.verdict = 'AC'
                    AND p.visibility = ?
                GROUP BY
                    p.problem_id, s.identity_id
                ) rp
            ON
                rp.identity_id = i.identity_id
            WHERE
                c.alias = ?
                AND gi.accept_teacher = true
            ORDER BY
                i.username ASC,
                rp.problem_id DESC;";

        /** @var list<array{alias: string, title: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\ProblemParams::VISIBILITY_PUBLIC, $courseAlias]
        );
    }

    /**
     * @return list<array{alias: string, title: string, username: string}>
     */
    final public static function getUnsolvedProblemsByUsersOfCourse(
        string $courseAlias
    ): array {
        $sql = '
            SELECT
                rp.alias,
                rp.title,
                i.username
            FROM
                Identities i
            INNER JOIN
                Groups_Identities gi
            ON
                gi.identity_id = i.identity_id
            INNER JOIN
                Courses c
            ON
                c.group_id = gi.group_id
            INNER JOIN
                (
                SELECT
                    pp.problem_id,
                    pp.alias,
                    pp.title,
                    s.identity_id,
                    MAX(r.score) AS max_score
                FROM
                    Submissions s
                INNER JOIN
                    Runs r
                ON
                    r.run_id = s.current_run_id
                INNER JOIN
                    Problems pp
                ON
                    pp.problem_id = s.problem_id
                WHERE
                    pp.visibility = ?
                GROUP BY
                    pp.problem_id, s.identity_id
                HAVING
                    max_score < 1
                ) rp
            ON
                rp.identity_id = i.identity_id
            INNER JOIN
                Problems p
            ON
                rp.problem_id = p.problem_id
            WHERE
                c.alias = ?
                AND gi.accept_teacher = true
            ORDER BY
                i.username ASC,
                rp.problem_id DESC;';

        /** @var list<array{alias: string, title: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\ProblemParams::VISIBILITY_PUBLIC, $courseAlias]
        );
    }

    final public static function hasTriedToSolveProblem(
        \OmegaUp\DAO\VO\Problems $problem,
        int $identityId
    ): bool {
        $sql = '
            SELECT
                COUNT(r.run_id)
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ? AND s.identity_id = ? AND
                r.verdict NOT IN ("AC", "CE", "JE");
        ';
        return (
            /** @var int */
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                $sql,
                [$problem->problem_id, $identityId]
            )
        ) > 0;
    }

    final public static function isProblemSolved(
        \OmegaUp\DAO\VO\Problems $problem,
        int $identityId
    ): bool {
        $sql = '
            SELECT
                COUNT(r.run_id)
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ? AND s.identity_id = ? AND r.verdict = "AC";
        ';

        return (
            /** @var int */
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                $sql,
                [$problem->problem_id, $identityId]
            )
        ) > 0;
    }

    public static function getPrivateCount(\OmegaUp\DAO\VO\Users $user): ?int {
        if (is_null($user->user_id)) {
            return 0;
        }
        $sql = 'SELECT
            COUNT(*) as total
        FROM
            Problems AS p
        INNER JOIN
            ACLs AS a
        ON
            a.acl_id = p.acl_id
        WHERE
            p.visibility <= 0 and a.owner_id = ?;';

        /** @var null|int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$user->user_id]
        );
    }

    /**
     * @return list<string>
     */
    public static function getExplicitAdminEmails(
        \OmegaUp\DAO\VO\Problems $problem
    ): array {
        $sql = '
            SELECT DISTINCT
                e.email
            FROM
                (
                    SELECT
                        p.problem_id, a.owner_id AS user_id
                    FROM
                        Problems AS p
                    INNER JOIN
                        ACLs AS a
                    ON
                        a.acl_id = p.acl_id
                    WHERE p.problem_id = ?
                ) AS a
            INNER JOIN
                Users u
            ON
                u.user_id = a.user_id
            INNER JOIN
                Emails e
            ON
                e.user_id = u.main_email_id;
        ';

        $params = [$problem->problem_id];

        $result = [];
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $row
        ) {
            $result[] = strval($row['email']);
        }
        return $result;
    }

    /**
     * @return null|array{name: string, email: string}
     */
    public static function getAdminUser(\OmegaUp\DAO\VO\Problems $problem): ?array {
        $sql = '
            SELECT DISTINCT
                e.email,
                i.name
            FROM
                ACLs a
            INNER JOIN
                Users u
            ON
                a.owner_id = u.user_id
            INNER JOIN
                Identities i
            ON
                i.user_id = u.user_id AND i.identity_id = u.main_identity_id
            INNER JOIN
                Emails e
            ON
                e.email_id = u.main_email_id
            WHERE
               a.acl_id = ?
            LIMIT
               1;
        ';
        $params = [$problem->acl_id];
        /** @var array{email: null|string, name: null|string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return [
            'name' => strval($row['name']),
            'email' => strval($row['email']),
        ];
    }

    /**
     * @return array{problems: list<\OmegaUp\DAO\VO\Problems>, count: int}
     */
    public static function getAllWithCount(
        int $page,
        int $pageSize
    ) {
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT COUNT(*) FROM `Problems`'
        );

        $problems = \OmegaUp\DAO\Problems::getAll(
            $page,
            $pageSize,
            'problem_id',
            'DESC'
        );

        return [
            'problems' => $problems,
            'count' => $count,
        ];
    }

    /**
     * Returns all problems that an identity can manage.
     *
     * @return array{problems: list<\OmegaUp\DAO\VO\Problems>, count: int}
     */
    final public static function getAllProblemsAdminedByIdentity(
        int $identityId,
        int $page,
        int $pageSize
    ): array {
        $offset = ($page - 1) * $pageSize;
        $select = '
            SELECT
                p.*';
        $sql = '
            FROM
                Problems AS p
            INNER JOIN
                ACLs AS a ON a.acl_id = p.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = p.acl_id
            LEFT JOIN
                Identities uri ON ur.user_id = uri.user_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = p.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                (ai.identity_id = ? OR
                (ur.role_id = ? AND uri.identity_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)) AND
                p.visibility > ?';
        $limits = '
            GROUP BY
                p.problem_id
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';

        $params = [
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\ProblemParams::VISIBILITY_DELETED,
        ];

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "SELECT COUNT(*) {$sql}",
            $params
        );

        $params[] = $offset;
        $params[] = $pageSize;

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits};",
            $params
        );

        $problems = [];
        foreach ($rs as $row) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($row);
        }

        return [
            'problems' => $problems,
            'count' => $count,
        ];
    }

    /**
     * Returns all problems owned by a user.
     *
     * @return array{problems: list<\OmegaUp\DAO\VO\Problems>, count: int}
     */
    final public static function getAllProblemsOwnedByUser(
        int $userId,
        int $page,
        int $pageSize
    ) {
        $offset = ($page - 1) * $pageSize;
        $select = '
            SELECT
                p.*';
        $sql = '
            FROM
                Problems AS p
            INNER JOIN
                ACLs AS a ON a.acl_id = p.acl_id
            WHERE
                a.owner_id = ? AND
                p.visibility > ?';
        $limits = '
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';

        $params = [
            $userId,
            \OmegaUp\ProblemParams::VISIBILITY_DELETED,
        ];

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "SELECT COUNT(*) {$sql}",
            $params
        );

        $params[] = $offset;
        $params[] = $pageSize;

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$limits};",
            $params
        );

        $problems = [];
        foreach ($rs as $row) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($row);
        }

        return [
            'problems' => $problems,
            'count' => $count,
        ];
    }

    /**
     * Return all problems, except deleted
     *
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getAllProblems(
        ?int $page,
        int $colsPerPage,
        ?string $order,
        string $orderType
    ) {
        $sql = 'SELECT * from Problems where `visibility` > ? ';
        if (!is_null($order)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape(
                $order
            ) . '` ' . ($orderType == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($page)) {
            $sql .= ' LIMIT ' . (($page - 1) * $colsPerPage) . ', ' . intval(
                $colsPerPage
            );
        }
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\ProblemParams::VISIBILITY_DELETED]
        );
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $allData;
    }

    /**
     * @return list<string>
     */
    final public static function getIdentitiesInGroupWhoAttemptedProblem(
        int $groupId,
        int $problemId
    ): array {
        $sql = '
            SELECT
                i.username
            FROM
                Identities i
            WHERE
                i.identity_id
            IN (
                SELECT DISTINCT
                    gi.identity_id
                FROM
                    Submissions s
                INNER JOIN
                    Groups_Identities gi
                ON
                    s.identity_id = gi.identity_id
                WHERE
                    gi.group_id = ?
                    AND s.problem_id = ?
            );';
        $params = [$groupId, $problemId];

        /** @var array{username: string}[] */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $identities = [];
        foreach ($rs as $row) {
            $identities[] = $row['username'];
        }
        return $identities;
    }

    final public static function isVisible(\OmegaUp\DAO\VO\Problems $problem): bool {
        return intval($problem->visibility) >= 1;
    }

    public static function deleteProblem(int $problemId): int {
        $sql = 'UPDATE
                    `Problems`
                SET
                    `visibility` = ?
                WHERE
                    `problem_id` = ?;';
        $params = [
            \OmegaUp\ProblemParams::VISIBILITY_DELETED,
            $problemId,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    public static function hasBeenUsedInCoursesOrContests(
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problemset_id IS NOT NULL
                AND s.problem_id = ?;
        ';
        return (
            /** @var int */
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                $sql,
                [$problem->problem_id]
            )
        ) > 0;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getByContest(int $contestId): array {
        $sql = 'SELECT
                    p.*
                FROM
                    Problems p
                INNER JOIN
                    Problemset_Problems pp
                ON
                    p.problem_id = pp.problem_id
                INNER JOIN
                    Contests c
                ON
                    c.problemset_id = pp.problemset_id
                WHERE
                    c.contest_id = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$contestId]
        );

        $problems = [];
        foreach ($rs as $row) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $problems;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getByTitle(string $title): array {
        $sql = 'SELECT
                    *
                FROM
                    Problems
                WHERE
                    title = ?;';

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$title]);

        $problems = [];
        foreach ($rs as $row) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $problems;
    }
}
