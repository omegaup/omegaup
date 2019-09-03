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
    final private static function addTagFilter(
        string $identityType,
        ?int $identityId,
        $tag,
        bool $requireAllTags,
        string &$sql,
        array &$args,
        array &$clauses
    ) : void {
        if (is_string($tag)) {
            $sql .= '
                INNER JOIN
                    Problems_Tags ptp ON ptp.problem_id = p.problem_id
                INNER JOIN
                    Tags t ON ptp.tag_id = t.tag_id
            ';
            array_push(
                $clauses,
                [
                    't.name = ?',
                    [$tag],
                ]
            );
        } elseif (is_array($tag)) {
            // Look for problems matching ALL tags or not
            $havingClause = $requireAllTags ? 'HAVING (COUNT(pt.tag_id) = ?)' : '';
            $placeholders = array_fill(0, count($tag), '?');
            $placeholders = join(',', $placeholders);
            $sql .= "
                INNER JOIN (
                    SELECT
                        pt.problem_id,
                        BIT_AND(pt.public) as public
                    FROM
                        Problems_Tags pt
                    WHERE pt.tag_id IN (
                        SELECT t.tag_id
                        FROM Tags t
                        WHERE t.name in ($placeholders)
                    )
                    GROUP BY
                        pt.problem_id
                    {$havingClause}
                ) ptp ON ptp.problem_id = p.problem_id";
            $args = array_merge($args, $tag);
            if ($requireAllTags) {
                $args[] = count($tag);
            }
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
        $tag,
        int $minVisibility,
        bool $requireAllTags,
        $programmingLanguages,
        $difficultyRange,
        int &$total
    ) {
        // Just in case.
        if ($order !== 'asc' && $order !== 'desc') {
            $order = 'desc';
        }

        $languageJoin = '';
        if (!is_null($language)) {
            $languageJoin = '
                INNER JOIN
                    Problems_Languages ON Problems_Languages.problem_id = p.problem_id
                INNER JOIN
                    Languages ON Problems_Languages.language_id = Languages.language_id
                    AND Languages.name = \'' . $language . '\'
            ';
        }

        // Use BINARY mode to force case sensitive comparisons when ordering by title.
        $collation = ($orderBy === 'title') ? 'COLLATE utf8_bin' : '';
        $select = '';
        $sql= '';
        $args = [];

        // Clauses is an array of 2-tuples that contains a chunk of SQL and the
        // arguments that are needed for that chunk.
        $clauses = [];
        if (is_array($programmingLanguages)) {
            foreach ($programmingLanguages as $programmingLanguage) {
                array_push(
                    $clauses,
                    [
                        'FIND_IN_SET(?, p.languages) > 0',
                        [$programmingLanguage],
                    ]
                );
            }
        }
        if (is_array($difficultyRange) && count($difficultyRange) == 2) {
            array_push(
                $clauses,
                [
                    'p.difficulty >= ? AND p.difficulty <= ?',
                    $difficultyRange,
                ]
            );
        }
        if (!is_null($query)) {
            array_push(
                $clauses,
                [
                    "(p.title LIKE CONCAT('%', ?, '%') OR p.alias LIKE CONCAT('%', ?, '%'))",
                    [$query, $query],
                ]
            );
        }

        if ($identityType === IDENTITY_ADMIN) {
            $args = [$identityId];
            $select = '
                SELECT
                    ROUND(100 / LOG2(GREATEST(accepted, 1) + 1), 2)   AS points,
                    accepted / GREATEST(1, submissions)     AS ratio,
                    ROUND(100 * COALESCE(ps.score, 0))      AS score,
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

            array_push(
                $clauses,
                [
                    'p.visibility > ?',
                    [\OmegaUp\Controllers\Problem::VISIBILITY_DELETED],
                ]
            );
        } elseif ($identityType === IDENTITY_NORMAL && !is_null($identityId)) {
            $select = '
                SELECT
                    ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                    p.accepted / GREATEST(1, p.submissions)     AS ratio,
                    ROUND(100 * COALESCE(ps.score, 0), 2)   AS score,
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

            array_push(
                $clauses,
                [
                    '(p.visibility >= ? OR id.identity_id = ? OR ur.acl_id IS NOT NULL OR gr.acl_id IS NOT NULL)',
                    [
                        max(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $minVisibility),
                        $identityId,
                    ],
                ]
            );
            array_push(
                $clauses,
                [
                    'p.visibility > ?',
                    [\OmegaUp\Controllers\Problem::VISIBILITY_DELETED],
                ]
            );
        } elseif ($identityType === IDENTITY_ANONYMOUS) {
            $select = '
                    SELECT
                        0 AS score,
                        ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                        accepted / GREATEST(1, p.submissions)  AS ratio,
                        p.* ';
            $sql = '
                    FROM
                        Problems p ' . $languageJoin;

            array_push(
                $clauses,
                [
                    'p.visibility >= ?',
                    [max(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $minVisibility)],
                ]
            );
        }

        if (!empty($tag)) {
            self::addTagFilter(
                $identityType,
                $identityId,
                $tag,
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
                    function ($clause) {
                        return $clause[0];
                    },
                    $clauses
                )
            );
            foreach ($clauses as $clause) {
                $args = array_merge($args, $clause[1]);
            }
        }

        $total = \OmegaUp\MySQLConnection::getInstance()->GetOne("SELECT COUNT(*) $sql", $args);

        // Reset the offset to 0 if out of bounds.
        if ($offset < 0 || $offset > $total) {
            $offset = 0;
        }

        if ($orderBy == 'problem_id') {
            $sql .= " ORDER BY p.problem_id {$collation} {$order} ";
        } elseif ($orderBy == 'points' && $order == 'desc') {
            $sql .= ' ORDER BY `points` DESC, `accepted` ASC, `submissions` DESC ';
        } else {
            $sql .= " ORDER BY `{$orderBy}` {$collation} {$order} ";
        }
        $sql .= ' LIMIT ?, ? ';
        $args[] = (int)$offset;
        $args[] = (int)$rowcount;
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll("{$select} {$sql};", $args);
        if (is_null($result)) {
            return [];
        }

        // Only these fields (plus score, points and ratio) will be returned.
        $filters = [
            'title','quality', 'difficulty', 'alias', 'visibility',
            'quality_histogram', 'difficulty_histogram',
        ];
        $problems = [];
        $hiddenTags = $identityType !== IDENTITY_ANONYMOUS ? \OmegaUp\DAO\Users::getHideTags($identityId) : false;
        if (!is_null($result)) {
            foreach ($result as $row) {
                $temp = new \OmegaUp\DAO\VO\Problems(array_intersect_key($row, \OmegaUp\DAO\VO\Problems::FIELD_NAMES));
                $problem = $temp->asFilteredArray($filters);

                // score, points and ratio are not actually fields of a Problems object.
                $problem['score'] = $row['score'];
                $problem['points'] = $row['points'];
                $problem['ratio'] = $row['ratio'];
                $problem['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem($temp, true);
                array_push($problems, $problem);
            }
        }
        return $problems;
    }

    final public static function getByAlias(
        string $alias
    ) : ?\OmegaUp\DAO\VO\Problems {
        $sql = 'SELECT * FROM Problems WHERE (alias = ? ) LIMIT 1;';
        $params = [$alias];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
                return null;
        }

        return new \OmegaUp\DAO\VO\Problems($rs);
    }

    final public static function searchByAlias($alias) {
        $quoted = \OmegaUp\MySQLConnection::getInstance()->Quote($alias);

        if (strpos($quoted, "'") !== false) {
            $quoted = substr($quoted, 1, strlen($quoted) - 2);
        }

        $sql = "SELECT * FROM Problems WHERE (alias LIKE '%$quoted%' OR title LIKE '%$quoted%') LIMIT 0,10;";
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);

        $result = [];

        foreach ($rs as $r) {
            array_push($result, new \OmegaUp\DAO\VO\Problems($r));
        }

        return $result;
    }

    final public static function getTagsForProblem($problem, $public) {
        $sql = 'SELECT
            t.name,
            pt.autogenerated
        FROM
            Problems_Tags pt
        INNER JOIN
            Tags t ON t.tag_id = pt.tag_id
        WHERE
            pt.problem_id = ?';
        if ($public) {
            $sql .= ' AND pt.public = 1';
        }
        $sql .= ';';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problem->problem_id]);
        $result = [];

        foreach ($rs as $r) {
            $result[] = ['name' => $r['name'], 'autogenerated' => $r['autogenerated']];
        }

        return $result;
    }

    final public static function getPracticeDeadline($id) {
        $sql = 'SELECT COALESCE(UNIX_TIMESTAMP(MAX(finish_time)), 0) FROM Contests c INNER JOIN Problemset_Problems pp USING(problemset_id) WHERE pp.problem_id = ?';
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$id]);
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

        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    final public static function getProblemsSolved($identityId) {
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

        $result = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val) as $row) {
            array_push($result, new \OmegaUp\DAO\VO\Problems($row));
        }
        return $result;
    }

    final public static function getProblemsUnsolvedByIdentity(
        $identityId
    ) {
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

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $r) {
            array_push($problems, new \OmegaUp\DAO\VO\Problems($r));
        }
        return $problems;
    }

    final public static function getSolvedProblemsByUsersOfCourse($course_alias) {
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

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $course_alias]);
    }

    /**
     * @return array{alias: string, title: string, username: string}[]
     */
    final public static function getUnsolvedProblemsByUsersOfCourse(
        string $courseAlias
    ) {
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

        /** @var array{alias: string, title: string, username: string}[] */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $courseAlias]
        );
    }

    final public static function isProblemSolved(
        \OmegaUp\DAO\VO\Problems $problem,
        int $identityId
    ) : bool {
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

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$problem->problem_id, $identityId]) > 0;
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
                r.verdict <> "AC" AND r.verdict <> "CE" AND r.verdict <> "JE";
        ';

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$problem->problem_id, $identityId]) > 0;
    }

    public static function getPrivateCount(\OmegaUp\DAO\VO\Users $user) : int {
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
        $params = [$user->user_id];

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
    }

    /**
     * @return string[]
     */
    public static function getExplicitAdminEmails(
        \OmegaUp\DAO\VO\Problems $problem
    ) : array {
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

        /** @var string[] */
        $result = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params) as $row) {
            $result[] = strval($row['email']);
        }

        return $result;
    }

    /**
     * @return null|array{name: string, email: string}
     */
    public static function getAdminUser(\OmegaUp\DAO\VO\Problems $problem) {
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
        /** @var null|array{email?: string, name?: string} */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (is_null($row)
            || !array_key_exists('name', $row)
            || !array_key_exists('email', $row)
        ) {
            return null;
        }

        return [
            'name' => strval($row['name']),
            'email' => strval($row['email']),
        ];
    }

    /**
     * Returns all problems that an identity can manage.
     */
    final public static function getAllProblemsAdminedByIdentity(
        $identity_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                p.*
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
                p.visibility > ?
            GROUP BY
                p.problem_id
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';
        $params = [
            $identity_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identity_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identity_id,
            \OmegaUp\Controllers\Problem::VISIBILITY_DELETED,
            (int)$offset,
            (int)$pageSize,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new \OmegaUp\DAO\VO\Problems($row));
        }
        return $problems;
    }

    /**
     * Returns all problems owned by a user.
     */
    final public static function getAllProblemsOwnedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                p.*
            FROM
                Problems AS p
            INNER JOIN
                ACLs AS a ON a.acl_id = p.acl_id
            WHERE
                a.owner_id = ? AND
                p.visibility > ?
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';
        $params = [
            $user_id,
            \OmegaUp\Controllers\Problem::VISIBILITY_DELETED,
            (int)$offset,
            (int)$pageSize,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new \OmegaUp\DAO\VO\Problems($row));
        }
        return $problems;
    }

    /**
     * Return all problems, except deleted
     */
    final public static function getAllProblems($page, $cols_per_page, $order, $order_type) {
        $sql = 'SELECT * from Problems where `visibility` > ? ';
        if (!is_null($order)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($order) . '` ' . ($order_type == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($page)) {
            $sql .= ' LIMIT ' . (($page - 1) * $cols_per_page) . ', ' . (int)$cols_per_page;
        }
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [\OmegaUp\Controllers\Problem::VISIBILITY_DELETED]);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $allData;
    }

    final public static function getIdentitiesInGroupWhoAttemptedProblem(
        $group_id,
        $problem_id
    ) {
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
        $params = [$group_id, $problem_id];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $identities = [];
        foreach ($rs as $row) {
            $identities[] = $row['username'];
        }
        return $identities;
    }

    final public static function isVisible(\OmegaUp\DAO\VO\Problems $problem) {
        return ((int) $problem->visibility) >= 1;
    }

    public static function deleteProblem($problem_id) {
        $sql = 'UPDATE
                    `Problems`
                SET
                    `visibility` = ?
                WHERE
                    `problem_id` = ?;';
        $params = [
            \OmegaUp\Controllers\Problem::VISIBILITY_DELETED,
            $problem_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    public static function hasBeenUsedInCoursesOrContests(\OmegaUp\DAO\VO\Problems $problem) {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problemset_id IS NOT NULL
                AND s.problem_id = ?;
        ';
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$problem->problem_id]);
    }

    final public static function getByContest($contest_id) {
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

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$contest_id]);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new \OmegaUp\DAO\VO\Problems($row));
        }
        return $problems;
    }

    final public static function getByTitle($title) {
        $sql = 'SELECT
                    *
                FROM
                    Problems
                WHERE
                    title = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$title]);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new \OmegaUp\DAO\VO\Problems($row));
        }
        return $problems;
    }
}
