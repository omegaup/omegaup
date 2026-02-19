<?php

namespace OmegaUp\DAO;

/**
 * Problems Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Problems}.
 * @access public
 * @package docs
 */
class Problems extends \OmegaUp\DAO\Base\Problems {
    /**
     * @param list<string> $tags
     */
    private static function addTagFilter(
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
        // Use direct JOIN on Tags instead of IN (subselect) for better query plan optimization
        $sql .= "
            INNER JOIN (
                SELECT
                    pt.problem_id,
                    BIT_AND(t.public) as public
                FROM
                    Problems_Tags pt
                INNER JOIN
                    Tags t
                ON
                    pt.tag_id = t.tag_id
                    AND t.name IN ($placeholders)
                INNER JOIN
                    Problems pp
                ON
                    pp.problem_id = pt.problem_id
                WHERE
                    (pp.allow_user_add_tags = '1' OR pt.source <> 'voted')
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
     * @return list<int>
     */
    private static function getAccessibleAclIds(
        ?int $identityId,
        ?int $userId
    ): array {
        if (is_null($identityId) || is_null($userId)) {
            return [];
        }

        $sql = '
            SELECT acl_id
            FROM User_Roles
            WHERE user_id = ? AND role_id = ?

            UNION

            SELECT gr.acl_id
            FROM Groups_Identities gi
            INNER JOIN Group_Roles gr ON gr.group_id = gi.group_id
            WHERE gi.identity_id = ? AND gr.role_id = ?
        ';

        /** @var list<array{acl_id: int}> */
        $results = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                $userId,
                \OmegaUp\Authorization::ADMIN_ROLE,
                $identityId,
                \OmegaUp\Authorization::ADMIN_ROLE
            ]
        );

        return array_map(fn($row) => $row['acl_id'], $results);
    }

    /**
     * @param list<string> $usernames
     * @return list<int>
     */
    private static function getUserIdsByUsername(array $usernames): array {
        if (empty($usernames)) {
            return [];
        }
        // Create placeholders (?, ?, ?) for each username
        $placeholders = implode(',', array_fill(0, count($usernames), '?'));
        $sql = "SELECT user_id FROM User_Rank WHERE username IN ({$placeholders})";

        /** @var list<array{user_id: int}> */
        $results = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $usernames
        );

        return array_map(fn($row) => intval($row['user_id']), $results);
    }

    /**
     * @param null|array{0: int, 1: int} $difficultyRange
     * @param list<string> $programmingLanguages
     * @param list<string> $tags
     * @param list<string> $authors
     * @return array{count: int, problems: list<array{accepted: int, alias: string, difficulty: float|null, difficulty_histogram: list<int>, points: float, problem_id: int, quality: float|null, quality_histogram: list<int>, quality_seal: bool, ratio: float, score: float, submissions: int, tags: list<array{name: string, source: string}>, title: string, visibility: int}>}
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
        ?array $difficultyRange,
        bool $onlyQualitySeal,
        ?string $level,
        string $difficulty,
        array $authors
    ) {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        );
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

        $clauses = [];

        $levelJoin = '';
        if (!is_null($level)) {
            $levelJoin = '
            INNER JOIN
                Problems_Tags pt ON p.problem_id = pt.problem_id
            INNER JOIN
                Tags t ON t.tag_id = pt.tag_id
            ';
            $clauses[] = [
                't.name = ?', [$level]
            ];
        }

        // Use BINARY mode to force case sensitive comparisons when ordering by title.
        $collation = ($orderBy === 'title') ? 'COLLATE utf8mb4_bin' : '';
        $select = '';
        $sql = '';
        $args = [];

        // Clauses is an array of 2-tuples that contains a chunk of SQL and the
        // arguments that are needed for that chunk.
        /** @var list<array{0: string, 1: list<string>}> */
        foreach ($programmingLanguages as $programmingLanguage) {
            $clauses[] = [
                'FIND_IN_SET(?, p.languages) > 0',
                [$programmingLanguage],
            ];
        }

        // Convert the difficulty in text form to a range
        if (is_null($difficultyRange)) {
            $difficultyRange = [];
            switch ($difficulty) {
                case 'easy':
                    $difficultyRange[] = 0;
                    $difficultyRange[] = 1;
                    break;
                case 'medium':
                    $difficultyRange[] = 2;
                    $difficultyRange[] = 2;
                    break;
                case 'hard':
                    $difficultyRange[] = 3;
                    $difficultyRange[] = 4;
                    break;
            }
        }

        if (count($difficultyRange) === 2) {
            $difficultyBounds = [];
            switch ($difficultyRange[0]) {
                case '0':
                    $difficultyBounds[] = 0;
                    break;
                case '1':
                    $difficultyBounds[] = 0.5;
                    break;
                case '2':
                    $difficultyBounds[] = 1.5;
                    break;
                case '3':
                    $difficultyBounds[] = 2.5;
                    break;
                case '4':
                    $difficultyBounds[] = 3.5;
                    break;
            }
            switch ($difficultyRange[1]) {
                case '0':
                    $difficultyBounds[] = 0.5;
                    break;
                case '1':
                    $difficultyBounds[] = 1.5;
                    break;
                case '2':
                    $difficultyBounds[] = 2.5;
                    break;
                case '3':
                    $difficultyBounds[] = 3.5;
                    break;
                case '4':
                    $difficultyBounds[] = 4;
                    break;
            }

            if ($difficultyBounds[1] === 4) {
                $upperBoundComparison = '<=';
            } else {
                $upperBoundComparison = '<';
            }
            if ($difficultyBounds[0] === 0) {
                // If the lower limit is equal to 0, take into account problems
                // without difficulty.
                $conditions = "(p.difficulty IS NULL OR (p.difficulty >= ? AND p.difficulty {$upperBoundComparison} ?))";
            } else {
                $conditions = "p.difficulty >= ? AND p.difficulty {$upperBoundComparison} ?";
            }

            $clauses[] = [
                $conditions,
                $difficultyBounds,
            ];
        }

        if (!is_null($query)) {
            if (is_numeric($query)) {
                $clauses[] = [
                    "(
                    p.title LIKE CONCAT('%', ?, '%') OR
                    p.alias LIKE CONCAT('%', ?, '%') OR
                    p.problem_id = ?
                    )",
                    [$query, $query, intval($query)],
                ];
            } else {
                $clauses[] = [
                    "(p.title LIKE CONCAT('%', ?, '%') OR p.alias LIKE CONCAT('%', ?, '%'))",
                    [$query, $query],
                ];
            }
        }

        if ($identityType === IDENTITY_ADMIN) {
            $args[] = $identityId;
            $select = "
                SELECT
                    ROUND(100 / LOG2(GREATEST(accepted, 1) + 1), 2) AS points,
                    accepted / GREATEST(1, submissions) AS ratio,
                    ROUND(100 * IFNULL(ps.score, 0.0)) AS score,
                    {$fields}
            ";
            $sql = '
                FROM
                    Problems p
                LEFT JOIN (
                    SELECT
                        Submissions.problem_id,
                        MAX(Runs.score) AS score
                    FROM
                        Submissions
                    INNER JOIN
                        Runs ON Runs.run_id = Submissions.current_run_id
                    WHERE
                        Submissions.identity_id = ?
                    GROUP BY
                        Submissions.problem_id
                    ) ps ON ps.problem_id = p.problem_id ' . $languageJoin . $levelJoin;

            $clauses[] = [
                'p.visibility > ?',
                [\OmegaUp\ProblemParams::VISIBILITY_DELETED],
            ];
        } elseif ($identityType === IDENTITY_NORMAL && !is_null($identityId)) {
            $userKey = is_null($userId) ? 'null' : $userId;
            $callback = /** @return list<int> */ fn (): array =>
                self::getAccessibleAclIds($identityId, $userId);
            $cacheKey = "{$identityId}-{$userKey}";

            $accessibleAclIds = \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::PROBLEM_IDENTITY_TYPE,
                $cacheKey,
                $callback
            );

            $select = "
                SELECT
                    ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                    p.accepted / GREATEST(1, p.submissions) AS ratio,
                    ROUND(100 * IFNULL(ps.score, 0), 2) AS score,
                    {$fields}
            ";

            $sql = '
                FROM Problems p
                INNER JOIN ACLs a ON a.acl_id = p.acl_id
                LEFT JOIN Identities id ON id.identity_id = ? AND a.owner_id = id.user_id
                LEFT JOIN (
                    SELECT
                        s.problem_id,
                        MAX(r.score) AS score
                    FROM Submissions s
                    INNER JOIN Runs r ON r.run_id = s.current_run_id
                    WHERE s.identity_id = ?
                    GROUP BY s.problem_id
                ) ps ON ps.problem_id = p.problem_id ' . $languageJoin . $levelJoin;

            $args[] = $identityId;
            $args[] = $identityId;

            $visibilityThreshold = max(
                \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
                $minVisibility
            );

            $clause = '(p.visibility >= ? OR id.identity_id IS NOT NULL)';
            $argsForClause = [$visibilityThreshold];

            if (!empty($accessibleAclIds)) {
                $placeholders = implode(
                    ',',
                    array_fill(0, count($accessibleAclIds), '?')
                );
                $clause .= ' OR p.acl_id IN (' . $placeholders . ')';
                $argsForClause = array_merge($argsForClause, $accessibleAclIds);
            }

            $clauses[] = [
                '(' . $clause . ') AND p.visibility > ?',
                array_merge(
                    $argsForClause,
                    [\OmegaUp\ProblemParams::VISIBILITY_DELETED]
                ),
            ];
        } elseif ($identityType === IDENTITY_ANONYMOUS) {
            $select = "
                    SELECT
                        0.0 AS score,
                        ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                        accepted / GREATEST(1, p.submissions)  AS ratio,
                        {$fields}
                    ";
            $sql = '
                    FROM
                        Problems p ' . $languageJoin . $levelJoin;

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

        if (!empty($authors)) {
            $authorUserIds = self::getUserIdsByUsername($authors);

            if (!empty($authorUserIds)) {
                $placeholders = join(
                    ',',
                    array_fill(
                        0,
                        count(
                            $authorUserIds
                        ),
                        '?'
                    )
                );
                $sql .= '
                    INNER JOIN ACLs pa_acl ON pa_acl.acl_id = p.acl_id
                ';
                $clauses[] = [
                    'pa_acl.owner_id IN (' . $placeholders . ')',
                    $authorUserIds,
                ];
            }
        }

        if ($onlyQualitySeal) {
            $clauses[] = [
                'p.quality_seal = ?', [1]
            ];
        }

        // Finally flatten all WHERE clauses, and add a 'WHERE' if applicable.
        if (!empty($clauses)) {
            $sql .= "\nWHERE\n" . implode(
                ' AND ',
                array_map(
                    /** @param array{0: string, 1: list<string>} $clause */
                    fn (array $clause) => $clause[0],
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
            'accepted', 'title', 'quality', 'difficulty', 'alias', 'visibility', 'problem_id',
            'quality_histogram', 'difficulty_histogram', 'submissions', 'quality_seal',
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
            /** @var array{title: string, quality: null|float, difficulty: null|float, alias: string, accepted: int, visibility: int, quality_histogram: null|list<int>, difficulty_histogram: null|list<int>, quality_seal: bool, submissions: int, problem_id: int} */
            $problem = $problemObject->asFilteredArray($filters);

            // score, points and ratio are not actually fields of a Problems object.
            $problem['score'] = floatval($row['score']);
            $problem['points'] = floatval($row['points']);
            $problem['ratio'] = floatval($row['ratio']);
            $problem['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problemObject,
                public: true,
                showUserTags: $row['allow_user_add_tags']
            );
            $difficultyHistogram = [];
            if (!is_null($row['difficulty_histogram'])) {
                /** @var list<int> */
                $difficultyHistogram = json_decode(
                    $row['difficulty_histogram']
                );
                if (count($difficultyHistogram) !== 5) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'difficulty_histogram'
                    );
                }
            }
            $problem['difficulty_histogram'] = $difficultyHistogram;

            $qualityHistogram = [];
            if (!is_null($row['quality_histogram'])) {
                /** @var list<int> */
                $qualityHistogram = json_decode(
                    $row['quality_histogram']
                );
                if (count($qualityHistogram) !== 5) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'quality_histogram'
                    );
                }
            }
            $problem['quality_histogram'] = $qualityHistogram;
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
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'Problems'
        ) . ' FROM Problems WHERE (alias = ? ) LIMIT 1;';
        $params = [$alias];

        /** @var array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
                return null;
        }

        return new \OmegaUp\DAO\VO\Problems($rs);
    }

    /**
     * Gets multiple problems based on a list of aliases in a single query.
     *
     * @param list<string> $aliases
     * @return array<string, \OmegaUp\DAO\VO\Problems> Map of alias => Problems
     */
    final public static function getByAliases(
        array $aliases
    ): array {
        if (empty($aliases)) {
            return [];
        }

        // Deduplicate aliases to avoid redundant parameter binding and DB work
        $aliases = array_values(array_unique($aliases));
        $placeholders = join(',', array_fill(0, count($aliases), '?'));
        $sql = 'SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'Problems'
        ) . " FROM Problems WHERE alias IN ({$placeholders});";

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $aliases);

        $problems = [];
        foreach ($rs as $row) {
            $problem = new \OmegaUp\DAO\VO\Problems($row);
            if (is_null($problem->alias)) {
                continue;
            }
            $problems[$problem->alias] = $problem;
        }

        return $problems;
    }

    /**
     * Gets a certain problem based on its alias and the problemset
     * it is supposed to be part of.
     *
     * @return null|\OmegaUp\DAO\VO\Problems
     */
    final public static function getByAliasAndProblemset(
        string $alias,
        int $problemsetId
    ): ?\OmegaUp\DAO\VO\Problems {
        $sql = '
            SELECT
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        ) . '
            FROM
                Problems p
            INNER JOIN
                Problemset_Problems pp ON pp.problem_id = p.problem_id
            WHERE
                p.alias = ?
                AND pp.problemset_id = ?
            ';
        $params = [
            $alias,
            $problemsetId
        ];

        /** @var array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (is_null($rs)) {
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
            $sql .= ' AND t.public = 1';
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
        WHERE
            s.verdict = "AC" AND s.type = "normal" AND s.identity_id = ?
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
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        ) . '
            FROM
                Problems p
            INNER JOIN
                Submissions s ON s.problem_id = p.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                Problems_Forfeited pf ON pf.problem_id = p.problem_id
                    AND pf.user_id = i.user_id
                    AND i.user_id IS NOT NULL
            LEFT JOIN
                ACLs a ON a.acl_id = p.acl_id
                    AND a.owner_id = i.user_id
                    AND i.user_id IS NOT NULL
            WHERE
                s.verdict = "AC" AND s.type = "normal" AND s.identity_id = ?
                AND pf.problem_id IS NULL
                AND a.acl_id IS NULL
            GROUP BY
                p.problem_id
            ORDER BY
                min(s.time) DESC,
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
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        );
        $sql = "
            SELECT
                {$fields},
                SUM(s.verdict = 'AC') AS solved_count
            FROM
                Submissions s
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            WHERE
                s.identity_id = ?
            GROUP BY
                p.problem_id
            HAVING
                solved_count = 0;
        ";

        $params = [$identityId];

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, solved_count: float|null, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $row) {
            unset($row['solved_count']);
            $problems[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $problems;
    }

    /**
     * Get count of solved problems grouped by difficulty level for a user.
     * Difficulty mapping: Easy(0-1.5), Medium(1.5-2.5), Hard(2.5-4), Unlabelled(NULL)
     *
     * @return array{easy: int, medium: int, hard: int, unlabelled: int, total: int}
     */
    public static function getSolvedCountByDifficulty(int $identityId): array {
        $sql = "
            SELECT
                COUNT(DISTINCT CASE WHEN p.difficulty IS NOT NULL AND p.difficulty >= 0 AND p.difficulty < 1.5 THEN p.problem_id END) AS easy,
                COUNT(DISTINCT CASE WHEN p.difficulty IS NOT NULL AND p.difficulty >= 1.5 AND p.difficulty < 2.5 THEN p.problem_id END) AS medium,
                COUNT(DISTINCT CASE WHEN p.difficulty IS NOT NULL AND p.difficulty >= 2.5 AND p.difficulty <= 4 THEN p.problem_id END) AS hard,
                COUNT(DISTINCT CASE WHEN p.difficulty IS NULL THEN p.problem_id END) AS unlabelled,
                COUNT(DISTINCT p.problem_id) AS total
            FROM
                Problems p
            INNER JOIN
                Submissions s ON s.problem_id = p.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                Problems_Forfeited pf ON pf.problem_id = p.problem_id
                    AND pf.user_id = i.user_id
                    AND i.user_id IS NOT NULL
            LEFT JOIN
                ACLs a ON a.acl_id = p.acl_id
                    AND a.owner_id = i.user_id
                    AND i.user_id IS NOT NULL
            WHERE
                s.verdict = 'AC' AND s.type = 'normal' AND s.identity_id = ?
                AND pf.problem_id IS NULL
                AND a.acl_id IS NULL
        ";

        /** @var array{easy: int, hard: int, medium: int, total: int, unlabelled: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$identityId]
        );

        return [
            'easy' => intval($row['easy'] ?? 0),
            'medium' => intval($row['medium'] ?? 0),
            'hard' => intval($row['hard'] ?? 0),
            'unlabelled' => intval($row['unlabelled'] ?? 0),
            'total' => intval($row['total'] ?? 0),
        ];
    }

    /**
     * Get count of problems where the user has submissions but no AC verdict.
     * These are problems the user is "attempting" but hasn't solved yet.
     *
     * @return int
     */
    public static function getAttemptingCount(int $identityId): int {
        $sql = "
            SELECT
                COUNT(DISTINCT p.problem_id)
            FROM
                Submissions s
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                Problems_Forfeited pf ON pf.problem_id = p.problem_id
                    AND pf.user_id = i.user_id
                    AND i.user_id IS NOT NULL
            LEFT JOIN
                ACLs a ON a.acl_id = p.acl_id
                    AND a.owner_id = i.user_id
                    AND i.user_id IS NOT NULL
            LEFT JOIN
                Submissions s2 ON s2.problem_id = p.problem_id
                    AND s2.identity_id = s.identity_id
                    AND s2.verdict = 'AC'
            WHERE
                s.identity_id = ?
                AND s.type = 'normal'
                AND pf.problem_id IS NULL
                AND a.acl_id IS NULL
                AND s2.submission_id IS NULL;
        ";

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$identityId]
        );
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
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        ) . '
            FROM
                Identities i
            INNER JOIN
                Users u ON u.user_id = i.user_id
            INNER JOIN
                ACLs a ON a.owner_id = u.user_id
            INNER JOIN
                Problems p ON p.acl_id = a.acl_id
            WHERE
                (
                    p.visibility >= ? OR
                    p.visibility = ?
                ) AND
                i.identity_id = ?;';

        $params = [
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
            $identityId,
        ];

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $problems = [];
        foreach ($rs as $r) {
            $problems[] = new \OmegaUp\DAO\VO\Problems($r);
        }
        return $problems;
    }

    /**
     * @return list<array{alias: string, solved: bool, title: string, username: string}>
     */
    public static function getProblemsByUsersInACourse(string $courseAlias) {
        $sql  = '
           SELECT
                ANY_VALUE(p.alias) AS alias,
                ANY_VALUE(p.title) AS title,
                IFNULL(SUM(s.verdict = "AC"), 0) > 0 AS solved,
                ANY_VALUE(i.username) AS username
            FROM
                Identities i
            INNER JOIN
                Groups_Identities gi ON gi.identity_id = i.identity_id
            INNER JOIN
                Courses c ON c.group_id = gi.group_id
            INNER JOIN
                Submissions s ON s.identity_id = i.identity_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            WHERE
                c.alias = ?
                AND gi.accept_teacher = true
                AND p.visibility = ?
            GROUP BY
                i.user_id,
                p.problem_id
            ORDER BY
                username ASC,
                p.problem_id DESC;';

        $problemsUsers = [];
        /** @var array{alias: string, solved: int, title: string, username: string} $problemsUser */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$courseAlias, \OmegaUp\ProblemParams::VISIBILITY_PUBLIC]
            ) as $problemsUser
        ) {
            $problemsUser['solved'] = boolval($problemsUser['solved']);
            $problemsUsers[] = $problemsUser;
        }
        return $problemsUsers;
    }

    final public static function hasTriedToSolveProblem(
        \OmegaUp\DAO\VO\Problems $problem,
        int $identityId
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problem_id = ? AND s.identity_id = ? AND
                s.verdict NOT IN ("AC", "CE", "JE");
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
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problem_id = ? AND s.identity_id = ? AND s.verdict = "AC";
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
        /** @var array{email: string} $row */
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
     * @return null|array{name: string, email: string, user_id: int}
     */
    public static function getAdminUser(\OmegaUp\DAO\VO\Problems $problem): ?array {
        $sql = '
            SELECT DISTINCT
                e.email,
                i.name,
                u.user_id
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
        /** @var array{email: null|string, name: null|string, user_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return [
            'name' => strval($row['name']),
            'email' => strval($row['email']),
            'user_id' => intval($row['user_id']),
        ];
    }

    /**
     * @return array{problems: list<\OmegaUp\DAO\VO\Problems>, count: int}
     */
    public static function getAllWithCount(
        int $page,
        int $pageSize,
        string $query = ''
    ) {
        $params = [];

        $sqlCount = '
            SELECT
                COUNT(*)
        ';

        $select = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        );

        $sql = '
            FROM
                Problems AS p
        ';
        if (!empty($query)) {
            $sql .= '
                WHERE
                    p.`title` LIKE CONCAT("%", ?, "%") OR
                    p.`alias` LIKE CONCAT("%", ?, "%")
            ';
            $params[] = $query;
            $params[] = $query;
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "{$sqlCount} {$sql}",
            $params
        );

        $limits = '
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?
        ';
        $params[] = max(0, $page - 1) * $pageSize;
        $params[] = $pageSize;

        $problems = [];
        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                "{$select} {$sql} {$limits}",
                $params
            ) as $row
        ) {
            $problems[] = new \OmegaUp\DAO\VO\Problems(
                $row,
            );
        }

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
        int $pageSize,
        string $query = ''
    ): array {
        $select = '
            SELECT
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        );
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

        $sqlQuery = '';
        if (!empty($query)) {
            $sqlQuery = ' AND (
                p.title LIKE CONCAT("%", ?, "%") OR
                p.alias LIKE CONCAT("%", ?, "%")
            )';
            $params[] = $query;
            $params[] = $query;
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "SELECT COUNT(*) {$sql} {$sqlQuery}",
            $params
        );

        $params[] = max(0, $page - 1) * $pageSize;
        $params[] = $pageSize;

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$sqlQuery} {$limits};",
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
        int $pageSize,
        string $query = ''
    ) {
        $select = '
            SELECT
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        );
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

        $sqlQuery = '';
        if (!empty($query)) {
            $sqlQuery = ' AND (
                p.title LIKE CONCAT("%", ?, "%") OR
                p.alias LIKE CONCAT("%", ?, "%")
            )';
            $params[] = $query;
            $params[] = $query;
        }

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            "SELECT COUNT(*) {$sql} {$sqlQuery}",
            $params
        );

        $params[] = max(0, $page - 1) * $pageSize;
        $params[] = $pageSize;

        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$sqlQuery} {$limits};",
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
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'Problems'
        ) . ' from Problems p where `visibility` > ? ';
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
        return (intval(
            $problem->visibility
        ) >= \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING  || intval(
            $problem->visibility
        ) == \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED);
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

    public static function hasSubmissionsOrHasBeenUsedInCoursesOrContests(
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problem_id = ?;
        ';
        /** @var int */
        $submissions = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$problem->problem_id]
        );
        if ($submissions > 0) {
            return true;
        }
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Problemset_Problems pp
            WHERE
                pp.problem_id = ?;
        ';
        /** @var int */
        $inProblemset = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$problem->problem_id]
        );
        return $inProblemset > 0;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getByContest(int $contestId): array {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'p'
        ) . '
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
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'Problems'
        ) . '
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

    /**
     * @return list<array{name: string, problems_per_tag: int}>
     */
    final public static function getQualityProblemsPerTagCount(): array {
        $sql = "SELECT
                    t.name,
                    SUM(IF(p.quality_seal = 1, 1, 0)) AS problems_per_tag
                FROM
                    Tags t
                LEFT JOIN
                    Problems_Tags pt
                ON
                    t.tag_id = pt.tag_id
                LEFT JOIN
                    Problems p
                ON
                    p.problem_id = pt.problem_id
                WHERE
                    t.name LIKE CONCAT('problemLevel','%')
                GROUP BY
                    t.name;";

        /** @var list<array{name: string, problems_per_tag: float|null}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
        $problems = [];
        foreach ($result as $problem) {
            $problems[] = [
                'name' => $problem['name'],
                'problems_per_tag' => intval($problem['problems_per_tag']),
            ];
        }
        return $problems;
    }

    final public static function getRandomLanguageProblemAlias(): string {
        $sql = "SELECT
                    alias
                FROM
                    Problems p
                    INNER JOIN
                    Problems_Tags pt
                ON
                    p.problem_id = pt.problem_id
                INNER JOIN
                    Tags t
                ON
                    t.tag_id = pt.tag_id
                WHERE
                    quality_seal = 1
                    AND (t.name LIKE CONCAT('problemLevel','%') AND t.name NOT LIKE 'problemLevelBasicKarel')
                ORDER BY
                    RAND() LIMIT 1;";

        /** @var string */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql);
    }

    final public static function getRandomKarelProblemAlias(): string {
        $sql = "SELECT
                    alias
                FROM
                    Problems p
                    INNER JOIN
                    Problems_Tags pt
                ON
                    p.problem_id = pt.problem_id
                INNER JOIN
                    Tags t
                ON
                    t.tag_id = pt.tag_id
                WHERE
                    quality_seal = 1
                    AND t.name = 'problemLevelBasicKarel'
                ORDER BY
                    RAND() LIMIT 1;";

        /** @var string */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql);
    }

    /**
     * @return array{results: list<array{key: string, value: string}>}
     */
    final public static function byIdentityTypeForTypeahead(
        int $offset,
        int $rowcount,
        string $query,
        string $searchType,
        int $minVisibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC
    ) {
        $fields = 'alias, title';

        $args = [];
        $sql = '';
        $select = "SELECT
                        {$fields},";

        $groupByClause = '';
        $orderByClause = '';

        if (in_array($searchType, ['alias', 'title', 'problem_id'])) {
            $args[] = $query;
            $select .= ' 1.0 AS relevance
            ';
            $sql = "FROM
                        Problems p
                    WHERE
                        p.{$searchType} = ?";
        } else {
            $args = array_fill(0, 5, $query);
            $curatedQuery = preg_replace('/\W+/', ' ', $query);
            $args = array_merge($args, array_fill(0, 2, $curatedQuery));
            $select .= ' IFNULL(SUM(relevance), 0.0) AS relevance
            ';
            $sql = "FROM
                    (
                        SELECT
                            {$fields},
                            2.0 AS relevance
                        FROM
                            Problems p
                        WHERE
                            alias = ?
                        UNION ALL
                        SELECT
                            {$fields},
                            1.0 AS relevance
                        FROM
                            Problems p
                        WHERE
                            title = ?
                        UNION ALL
                        SELECT
                            {$fields},
                            0.1 AS relevance
                        FROM
                            Problems p
                        WHERE
                            (
                                title LIKE CONCAT('%', ?, '%') OR
                                alias LIKE CONCAT('%', ?, '%') OR
                                problem_id = ?
                            )
                        UNION ALL
                        SELECT
                            {$fields},
                            IFNULL(
                                MATCH(alias, title)
                                AGAINST (? IN BOOLEAN MODE), 0.0
                            ) AS relevance
                        FROM
                            Problems p
                        WHERE
                            MATCH(alias, title)
                            AGAINST (? IN BOOLEAN MODE)
                    ) AS p";
            $groupByClause = "
                        GROUP BY {$fields}
            ";
            $orderByClause = '
                        ORDER BY relevance DESC
            ';
        }

        $limits = ' LIMIT ?, ? ';
        $args[] = $offset;
        $args[] = $rowcount;

        /** @var list<array{alias: string, relevance: float, title: string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            "{$select} {$sql} {$groupByClause} {$orderByClause} {$limits};",
            $args
        );

        $problems = [];
        foreach ($result as $problem) {
            $problems[] = [
                'key' => $problem['alias'],
                'value' => $problem['title'],
            ];
        }
        return [
            'results' => $problems,
        ];
    }
}
