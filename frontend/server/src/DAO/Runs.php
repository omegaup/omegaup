<?php

namespace OmegaUp\DAO;

/**
 * Runs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Runs}.
 * @access public
 * @package docs
 */
class Runs extends \OmegaUp\DAO\Base\Runs {
    /** @var string */
    private static $ctesubmissionFeedbackForProblemset = 'WITH ssff AS (
        SELECT
            ss.submission_id,
            COUNT(*) AS suggestions
        FROM
            Submission_Feedback sf
        INNER JOIN
            Submissions ss
        ON
            ss.submission_id = sf.submission_id
        GROUP BY
            ss.submission_id
    )';

    /**
     * Gets an array of the best solving runs for a problem.
     * @return list<array{classname: string, username: string, language: string, runtime: float, memory: float, time: \OmegaUp\Timestamp}>
     */
    final public static function getBestSolvingRunsForProblem(
        int $problemId
    ): array {
        $sql = '
            SELECT
                i.username,
                s.language,
                r.runtime,
                r.memory,
                s.`time`,
                IFNULL(ur.classname, "user-rank-unranked") `classname`,
                ROW_NUMBER() OVER(
                    PARTITION BY i.identity_id ORDER BY r.runtime ASC, s.submission_id ASC
                ) AS per_identity_rank
            FROM
                Submissions s
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            WHERE
                s.problem_id = ? AND
                s.status = "ready" AND
                s.verdict = "AC" AND
                s.type = "normal"
            ORDER BY
                per_identity_rank ASC, r.runtime ASC, s.submission_id ASC
            LIMIT 0, 10;
        ';
        $val = [$problemId];

        $result = [];
        /** @var array{classname: string, language: string, memory: int, per_identity_rank: int, runtime: int, time: \OmegaUp\Timestamp, username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            if ($row['per_identity_rank'] != 1) {
                // This means that there were fewer than 10 distinct identities
                // that solved this problem, and the rest of the rows are
                // repeated users.
                break;
            }
            unset($row['per_identity_rank']);
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Gets an array of the guids of the pending runs
     *
     * @return list<string>
     */
    final public static function getPendingRunGuidsOfProblemset(
        int $problemsetId
    ): array {
        $sql = '
            SELECT
                s.guid
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problemset_id = ? AND r.status != "ready" AND s.type = "normal";
        ';
        $val = [$problemsetId];

        $result = [];
        /** @var array{guid: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            $result[] = $row['guid'];
        }
        return $result;
    }

    /**
     * Returns the SELECT column expressions for the run extra fields.
     * These reference the `rg_agg` alias produced by getRunExtraFieldsJoin().
     *
     * @return string
     */
    final public static function getRunExtraFields() {
        /** @var string */
        return '
            COALESCE(rg_agg.output, "OUTPUT_INTERRUPTED") AS output,
            COALESCE(rg_agg.execution, "EXECUTION_COMPILATION_ERROR") AS execution,
            COALESCE(rg_agg.status_runtime, "RUNTIME_NOT_AVAILABLE") AS status_runtime,
            COALESCE(rg_agg.status_memory, "MEMORY_NOT_AVAILABLE") AS status_memory';
    }

    /**
     * Returns a LEFT JOIN clause with a derived subquery that aggregates
     * all 4 run-extra-status fields from Runs_Groups in a single pass
     * per run_id, replacing the previous 4 correlated subqueries.
     *
     * @return string
     */
    final public static function getRunExtraFieldsJoin() {
        /** @var string */
        return '
        LEFT JOIN (
            SELECT
                run_id,
                ELT(
                    MIN(FIELD(
                        IF(verdict IN ("OLE", "OL"), "OUTPUT_EXCEEDED",
                        IF(verdict IN ("WA", "PA"), "OUTPUT_INCORRECT",
                        IF(verdict IN ("JE", "VE", "CE", "FO", "RFE", "RE", "RTE", "MLE", "TLE"), "OUTPUT_INTERRUPTED",
                        "OUTPUT_CORRECT"))),
                        "OUTPUT_EXCEEDED", "OUTPUT_INCORRECT", "OUTPUT_INTERRUPTED", "OUTPUT_CORRECT"
                    )),
                    "OUTPUT_EXCEEDED", "OUTPUT_INCORRECT", "OUTPUT_INTERRUPTED", "OUTPUT_CORRECT"
                ) AS output,
                ELT(
                    MIN(FIELD(
                        IF(verdict = "JE", "EXECUTION_JUDGE_ERROR",
                        IF(verdict = "VE", "EXECUTION_VALIDATOR_ERROR",
                        IF(verdict = "CE", "EXECUTION_COMPILATION_ERROR",
                        IF(verdict IN ("OF", "RFE"), "EXECUTION_RUNTIME_FUNCTION_ERROR",
                        IF(verdict IN ("RE", "RTE"), "EXECUTION_RUNTIME_ERROR",
                        IF(verdict IN ("ML", "MLE", "TLE", "OLE", "TO", "OL"), "EXECUTION_INTERRUPTED",
                        "EXECUTION_FINISHED")))))),
                        "EXECUTION_JUDGE_ERROR", "EXECUTION_VALIDATOR_ERROR",
                        "EXECUTION_COMPILATION_ERROR", "EXECUTION_RUNTIME_FUNCTION_ERROR",
                        "EXECUTION_RUNTIME_ERROR", "EXECUTION_INTERRUPTED", "EXECUTION_FINISHED"
                    )),
                    "EXECUTION_JUDGE_ERROR", "EXECUTION_VALIDATOR_ERROR",
                    "EXECUTION_COMPILATION_ERROR", "EXECUTION_RUNTIME_FUNCTION_ERROR",
                    "EXECUTION_RUNTIME_ERROR", "EXECUTION_INTERRUPTED", "EXECUTION_FINISHED"
                ) AS execution,
                ELT(
                    MIN(FIELD(
                        IF(verdict IN ("JE", "CE"), "RUNTIME_NOT_AVAILABLE",
                        IF(verdict IN ("TLE", "TO"), "RUNTIME_EXCEEDED",
                        "RUNTIME_AVAILABLE")),
                        "RUNTIME_NOT_AVAILABLE", "RUNTIME_EXCEEDED", "RUNTIME_AVAILABLE"
                    )),
                    "RUNTIME_NOT_AVAILABLE", "RUNTIME_EXCEEDED", "RUNTIME_AVAILABLE"
                ) AS status_runtime,
                ELT(
                    MIN(FIELD(
                        IF(verdict IN ("JE", "CE"), "MEMORY_NOT_AVAILABLE",
                        IF(verdict IN ("ML", "MLE"), "MEMORY_EXCEEDED",
                        "MEMORY_AVAILABLE")),
                        "MEMORY_NOT_AVAILABLE", "MEMORY_EXCEEDED", "MEMORY_AVAILABLE"
                    )),
                    "MEMORY_NOT_AVAILABLE", "MEMORY_EXCEEDED", "MEMORY_AVAILABLE"
                ) AS status_memory
            FROM
                Runs_Groups
            GROUP BY
                run_id
        ) AS rg_agg ON rg_agg.run_id = r.run_id';
    }

    /**
     * @return array{runs: list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: string, guid: string, language: string, memory: int, output: string, penalty: int, run_id: int, runtime: int, score: float, status: string, status_memory: string, status_runtime: string, submit_delay: int, suggestions: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}>, totalRuns: int}
     */
    final public static function getAllRuns(
        ?int $problemsetId,
        ?string $status,
        ?string $verdict,
        ?int $problemId,
        ?string $language,
        ?int $identityId,
        ?int $offset = 0,
        ?int $rowCount = 100,
        ?string $execution = null,
        ?string $output = null,
    ): array {
        $where = [];
        $val = [];
        $cteSubmissionsFeedback = '';
        $suggestionsCountField = '0 AS suggestions';
        $suggestionsJoin = '';

        if (!is_null($problemsetId)) {
            $cteSubmissionsFeedback = self::$ctesubmissionFeedbackForProblemset;

            $suggestionsCountField = 'IFNULL(ssff.suggestions, 0) AS suggestions';
            $suggestionsJoin = 'LEFT JOIN ssff ON ssff.submission_id = s.submission_id';

            $where[] = 's.problemset_id = ?';
            $val[] = $problemsetId;
        }
        if (!is_null($problemId)) {
            $where[] = 's.problem_id = ?';
            $val[] = $problemId;
        }
        if (!is_null($language)) {
            $where[] = 's.language = ?';
            $val[] = $language;
        }
        if (!is_null($identityId)) {
            $where[] = 's.identity_id = ?';
            $val[] = $identityId;
        }

        if (!is_null($status)) {
            $where[] = 's.status = ?';
            $val[] = $status;
        }
        if (!is_null($verdict)) {
            if ($verdict === 'NO-AC') {
                $where[] = 's.verdict <> ?';
                $val[] = 'AC';
            } else {
                $where[] = 's.verdict = ?';
                $val[] = $verdict;
            }
        } else {
            if (!is_null($execution)) {
                $executionArgs = \OmegaUp\Controllers\Run::EXECUTION[$execution];
                $placeholders = array_fill(0, count($executionArgs), '?');
                $placeholders = join(',', $placeholders);
                $where[] = "s.verdict IN ({$placeholders})";
                $val = array_merge($val, $executionArgs);
            }

            if (!is_null($output)) {
                $outputArgs = \OmegaUp\Controllers\Run::OUTPUT[$output];
                $placeholders = array_fill(0, count($outputArgs), '?');
                $placeholders = join(',', $placeholders);
                $where[] = "s.verdict IN ({$placeholders})";
                $val = array_merge($val, $outputArgs);
            }
        }

        $sqlCount = "{$cteSubmissionsFeedback}
            SELECT
                COUNT(*) AS total
            FROM
                Submissions s
        ";
        if (!empty($where)) {
            $sqlCount .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount,
            $val,
        );

        if (is_null($offset) || $offset < 0) {
            $offset = 0;
        }
        if (is_null($rowCount)) {
            $rowCount = 100;
        }

        $extraFields = self::getRunExtraFields();
        $extraFieldsJoin = self::getRunExtraFieldsJoin();

        $sql = "{$cteSubmissionsFeedback}
            SELECT
                `r`.`run_id`,
                `s`.`guid`,
                `s`.`language`,
                `r`.`status`,
                `r`.`verdict`,
                `r`.`runtime`,
                `r`.`penalty`,
                `r`.`memory`,
                IF(
                    `c`.`score_mode` = 'all_or_nothing' AND `r`.`score` <> 1,
                        0,
                        `r`.`score`
                ) AS `score`,
                IF(
                    `c`.`score_mode` = 'all_or_nothing' AND `r`.`score` <> 1,
                        0,
                        `r`.`contest_score`
                ) AS `contest_score`,
                `s`.`time`,
                `s`.`submit_delay`,
                `s`.`type`,
                `i`.`username`,
                `p`.`alias`,
                IFNULL(`i`.`country_id`, 'xx') `country`,
                `c`.`alias` AS `contest_alias`,
                IFNULL(ur.classname, 'user-rank-unranked') `classname`,
                {$extraFields},
                {$suggestionsCountField}
            FROM
                Submissions s
            {$suggestionsJoin}
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            LEFT JOIN
                Contests c ON c.problemset_id = s.problemset_id
            {$extraFieldsJoin}
        ";
        if (!empty($where)) {
            $sql .= 'WHERE ' . implode(' AND ', $where);
        }
        $sql .= '
            ORDER BY s.submission_id DESC
            LIMIT ?, ?;
        ';
        $val[] = $offset * $rowCount;
        $val[] = $rowCount;

        /** @var list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: string, guid: string, language: string, memory: int, output: string, penalty: int, run_id: int, runtime: int, score: float, status: string, status_memory: string, status_runtime: string, submit_delay: int, suggestions: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}> */
        $runs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);

        return [
            'runs' => $runs,
            'totalRuns' => $totalRows,
        ];
    }

    /**
     * Gets an array of the guids of the pending runs
     *
     * @return list<string>
     */
    final public static function getPendingRunsOfProblem(
        int $problemId
    ) {
        $sql = '
            SELECT
                s.guid
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ? AND r.status != "ready" AND s.`type` = "normal";';
        $val = [$problemId];

        /** @var list<string> */
        $result = [];
        /** @var array{guid: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            $result[] = $row['guid'];
        }
        return $result;
    }

    /**
     * Gets the count of total runs sent to a given contest by verdict
     */
    final public static function countTotalRunsOfProblemsetByVerdict(
        int $problemsetId,
        string $verdict
    ): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problemset_id = ? AND s.verdict = ? AND s.status = "ready" AND s.`type` = "normal";
        ';
        $val = [$problemsetId, $verdict];

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Gets the count of total runs sent to a given contest by verdict
     */
    final public static function countTotalRunsOfProblemByVerdict(
        int $problemId,
        string $verdict
    ): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problem_id = ? AND s.status = "ready" AND s.verdict = ? AND s.`type` = "normal";
        ';
        $val = [$problemId, $verdict];

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Gets the count of total runs sent to a given contest by verdict and by period of time
     *
     * @return list<array{date: null|string, runs: int, verdict: string}>
     */
    final public static function countRunsOfIdentityPerDatePerVerdict(
        int $identityId
    ): array {
        $sql = '
            SELECT
                DATE(s.time) AS date,
                s.verdict AS verdict,
                COUNT(*) AS runs
            FROM
                Submissions s
            WHERE
                s.identity_id = ? AND s.status = "ready" AND s.`type` = "normal"
            GROUP BY
                date, verdict
            ORDER BY
                date ASC;
        ';
        $val = [$identityId];

        /** @var list<array{date: null|string, runs: int, verdict: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * Gets the largest queued time of a run in seconds.
     *
     * @return array{guid: string, time: \OmegaUp\Timestamp}|null
     */
    final public static function getLargestWaitTimeOfProblemset(
        int $problemsetId
    ) {
        $sql = '
            SELECT
                s.guid, s.`time`
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problemset_id = ? AND r.status != "ready" AND s.`type` = "normal"
            ORDER BY
                s.submission_id ASC
            LIMIT 1;
        ';
        $val = [$problemsetId];

        /** @var array{guid: string, time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $val);
        if (empty($row)) {
            return null;
        }
        return $row;
    }

    /**
     * Get all relevant identities for a problemset.
     *
     * @return list<array{identity_id: int, username: string, name: null|string, country_id: string, is_invited: bool, classname: string}>
     */
    final public static function getAllRelevantIdentities(
        int $problemsetId,
        int $aclId,
        bool $showAllRuns = false,
        ?string $filterUsersBy = null,
        ?int $groupId = null,
        ?bool $excludeAdmin = true
    ): array {
        $classNameQuery = '
            IFNULL(
                (
                    SELECT
                        ur.classname
                    FROM
                        User_Rank ur
                    WHERE
                        ur.user_id = i.user_id
                ),
                "user-rank-unranked"
            ) AS classname';
        // Build SQL statement
        if ($showAllRuns) {
            if (is_null($groupId)) {
                $sql = "
                    SELECT
                        i.identity_id,
                        i.username,
                        i.name,
                        IFNULL(i.country_id, 'xx') AS country_id,
                        CAST(IFNULL(ri.is_invited, FALSE) AS UNSIGNED) AS is_invited,
                        $classNameQuery
                    FROM
                        (
                            SELECT
                                raw_identities.identity_id,
                                CAST(MAX(raw_identities.is_invited) AS UNSIGNED) AS is_invited
                            FROM
                                (
                                    SELECT
                                        pi.identity_id,
                                        CAST(pi.is_invited AS UNSIGNED) AS is_invited
                                    FROM
                                        Problemset_Identities pi
                                    WHERE
                                        pi.problemset_id = ?
                                    UNION
                                    SELECT
                                        gi.identity_id,
                                        TRUE AS is_invited
                                    FROM
                                        Group_Roles gr
                                    INNER JOIN
                                        Groups_Identities gi
                                    ON
                                        gi.group_id = gr.group_id
                                    WHERE
                                        gr.acl_id = ? AND gr.role_id = ?
                                ) AS raw_identities
                            GROUP BY
                                raw_identities.identity_id
                        ) AS ri
                    INNER JOIN
                        Identities i ON i.identity_id = ri.identity_id
                    WHERE
                        (
                            i.user_id NOT IN (
                                SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (?, ?) AND ur.role_id = ?
                            )
                            AND i.identity_id NOT IN (
                                SELECT
                                    gi.identity_id
                                FROM
                                    Group_Roles gr
                                INNER JOIN
                                    Groups_Identities gi ON gi.group_id = gr.group_id
                                WHERE
                                    gr.acl_id IN (?, ?) AND gr.role_id = ?
                            )
                ";
                $val = [
                    $problemsetId,
                    $aclId,
                    \OmegaUp\Authorization::CONTESTANT_ROLE,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                ];
                if ($excludeAdmin) {
                    $sql .= ' AND i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?)';
                    $val[] =  $aclId;
                }
                $sql .= ' OR i.user_id IS NULL);';
            } else {
                $sql = "
                    SELECT
                        i.identity_id,
                        i.username,
                        i.name,
                        IFNULL(i.country_id, 'xx') AS country_id,
                        FALSE AS is_invited,
                        $classNameQuery
                    FROM
                        Identities i
                    INNER JOIN
                        Groups_Identities gi ON i.identity_id = gi.identity_id
                    WHERE
                        gi.group_id = ? AND
                        (i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?) AND
                        i.user_id NOT IN (SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (?, ?) AND ur.role_id = ?)
                        AND i.identity_id NOT IN (
                            SELECT
                                gi.identity_id
                            FROM
                                Group_Roles gr
                            INNER JOIN
                                Groups_Identities gi ON gi.group_id = gr.group_id
                            WHERE
                                gr.acl_id IN (?, ?) AND gr.role_id = ?
                        )
                    OR i.user_id IS NULL);";
                $val = [
                    $groupId,
                    $aclId,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                ];
            }
        } else {
            $val = [$problemsetId];
            if (is_null($filterUsersBy)) {
                $sqlUserFilter = '';
            } else {
                $sqlUserFilter = ' AND i.username LIKE ?';
                $val[] = $filterUsersBy . '%';
            }
            $sql = "
                SELECT
                    i.identity_id,
                    i.username,
                    i.name,
                    IFNULL(i.country_id, 'xx') AS country_id,
                    FALSE AS is_invited,
                    IFNULL(ur.classname, 'user-rank-unranked') AS classname
                FROM
                    Submissions s
                INNER JOIN
                    Identities i ON i.identity_id = s.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                WHERE
                    s.problemset_id = ? AND
                    s.type = 'normal' AND
                    s.status = 'ready' AND
                    s.verdict NOT IN ('CE', 'JE', 'VE')
                    $sqlUserFilter
                GROUP BY
                    s.identity_id;";
        }

        $result = [];
        /** @var array{classname: string, country_id: string, identity_id: int, is_invited: int, name: null|string, username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            $row['is_invited'] = boolval($row['is_invited']);
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @return array{solved: bool, tried: bool}
     */
    public static function getSolvedAndTriedProblemByIdentity(
        int $problemId,
        int $identityId
    ): array {
        $sql = '
            SELECT
                (
                SELECT
                    COUNT(1) AS total
                FROM
                    Submissions s
                WHERE
                    s.verdict NOT IN (\'CE\', \'JE\', \'VE\')
                    AND s.problem_id = ?
                    AND s.identity_id = ?
                ) AS tried,
                (
                SELECT
                    COUNT(1) AS total
                FROM
                    Submissions s
                WHERE
                    s.verdict IN (\'AC\')
                    AND s.problem_id = ?
                    AND s.identity_id = ?
                ) AS solved;
        ';

        /** @var array{solved: int|null, tried: int|null} */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemId, $identityId, $problemId, $identityId]
        );

        return [
            'tried' => boolval($result['tried']),
            'solved' => boolval($result['solved']),
        ];
    }

    /**
     * @return list<array{contest_score: float, guid: string, identity_id: int, penalty: int, problem_id: int, score: float, score_by_group: null|string, submit_delay: int, time: \OmegaUp\Timestamp, type: string}>
     */
    final public static function getProblemsetRuns(
        int $problemsetId,
        bool $onlyAC = false
    ): array {
        $verdictCondition = ($onlyAC ?
            "s.verdict IN ('AC') " :
            "s.verdict NOT IN ('CE', 'JE', 'VE') "
        );

        $sql = "
            SELECT
                IF(
                    c.score_mode = 'all_or_nothing' AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                r.penalty,
                IFNULL(
                    IF(
                        c.score_mode = 'all_or_nothing' AND r.score <> 1,
                            0,
                            r.contest_score
                    ),
                    0.0
                ) AS contest_score,
                s.problem_id,
                s.identity_id,
                IFNULL(s.`type`, 'normal') AS `type`,
                s.`time`,
                s.submit_delay,
                s.guid,
                JSON_OBJECTAGG(
                    IFNULL(rg.group_name, ''),
                    rg.score
                ) AS score_by_group
            FROM
                Problemset_Problems pp
            INNER JOIN
                Submissions s
            ON
                s.problemset_id = pp.problemset_id AND
                s.problem_id = pp.problem_id
            INNER JOIN
                Runs r ON s.current_run_id = r.run_id
            LEFT JOIN
                Contests c ON c.problemset_id = pp.problemset_id
            LEFT JOIN
                Runs_Groups rg ON r.run_id = rg.run_id
            WHERE
                pp.problemset_id = ? AND
                s.status = 'ready' AND
                s.`type` = 'normal' AND
                $verdictCondition
            GROUP BY
                score_mode,
                r.score,
                r.penalty,
                r.contest_score,
                s.problemset_id,
                s.problem_id,
                s.identity_id,
                s.time,
                s.submit_delay,
                s.guid
            ORDER BY
                s.submission_id;";

        /** @var list<array{contest_score: float, guid: string, identity_id: int, penalty: int, problem_id: int, score: float, score_by_group: null|string, submit_delay: int, time: \OmegaUp\Timestamp, type: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * Get best contest score of a user for a problem in a problemset.
     */
    final public static function getBestProblemScoreInProblemset(
        int $problemsetId,
        int $problemId,
        int $identityId
    ): ?float {
        $sql = '
            SELECT
                IF(
                    c.score_mode = "all_or_nothing" AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = s.problemset_id
            WHERE
                s.identity_id = ? AND s.problemset_id = ? AND s.problem_id = ? AND
                r.status = "ready" AND s.`type` = "normal"
            ORDER BY
                r.contest_score DESC, r.penalty ASC
            LIMIT 1;
        ';
        $val = [$identityId, $problemsetId, $problemId];
        /** @var float|null|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Returns best score for the given identity and problem, between 0 and 100
     */
    final public static function getBestProblemScore(
        int $problemId,
        int $identityId
    ): ?float {
        $sql = '
            SELECT
                r.score * 100
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                s.identity_id = ? AND s.problem_id = ? AND
                r.status = "ready" AND s.`type` = "normal"
            ORDER BY
                r.score DESC, r.penalty ASC
            LIMIT 1;
        ';
        $val = [$identityId, $problemId];
        /** @var float|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * @return list<array{alias: string, contest_score: float|null, guid: string, language: string, username: string, verdict: string}>
     */
    final public static function getByProblemset(int $problemsetId): array {
        $sql = '
            SELECT
                s.guid,
                s.language,
                s.verdict,
                IF(
                    c.score_mode = "all_or_nothing" AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                i.username,
                p.alias
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = s.problem_id
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = s.problemset_id
            WHERE
                s.problemset_id = ?
            ORDER BY
                s.`time` DESC;
        ';

        /** @var list<array{alias: string, contest_score: float|null, guid: string, language: string, username: string, verdict: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * @return array{commit: string, contest_score: float|null, execution: null|string, judged_by: null|string, memory: int, output: null|string, penalty: int, run_id: int, runtime: int, score: float, status: string, status_memory: null|string, status_runtime: null|string, submission_id: int, time: \OmegaUp\Timestamp, verdict: string, version: string}|null
     */
    final public static function getByGUID(string $guid) {
        $extraFields = self::getRunExtraFields();
        $extraFieldsJoin = self::getRunExtraFieldsJoin();

        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Runs::FIELD_NAMES,
            'r'
        ) . ',
            ' . $extraFields . '
        FROM
            `Runs` `r`
        INNER JOIN
            `Submissions` `s`
        ON
            `r`.`submission_id` = `s`.`submission_id`
        ' . $extraFieldsJoin . '
        WHERE
            `s`.`guid` = ?
        LIMIT
            1;
        ';

        /** @var array{commit: string, contest_score: float|null, execution: string, judged_by: null|string, memory: int, output: string, penalty: int, run_id: int, runtime: int, score: float, status: string, status_memory: string, status_runtime: string, submission_id: int, time: \OmegaUp\Timestamp, verdict: string, version: string}|null */
        $run = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$guid]);

        if (is_null($run)) {
            return null;
        }

        return $run;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Runs>
     */
    final public static function getByProblem(
        int $problemId
    ) {
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Runs::FIELD_NAMES,
            'r'
        ) . '
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ?;
        ';
        $params = [$problemId];
        /** @var list<array{commit: string, contest_score: float, judged_by: string, memory: int, penalty: int, run_id: int, runtime: int, score: float, submission_id: int, status: string, time: int, verdict: string, version: string}> $rs */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
        $runs = [];
        foreach ($rs as $row) {
            $runs[] = new \OmegaUp\DAO\VO\Runs($row);
        }
        return $runs;
    }

    /**
     * @return list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions: int, time: \OmegaUp\Timestamp, type: string, username: string, verdict: string}>
     */
    final public static function getForProblemDetails(
        int $problemId,
        ?int $problemsetId,
        int $identityId
    ) {
        $extraFields = self::getRunExtraFields();
        $extraFieldsJoin = self::getRunExtraFieldsJoin();
        $cteSubmissionsFeedback = '';
        $suggestionsCountField = '0 AS suggestions';
        $suggestionsJoin = '';
        $whereClause = '';
        $params = [$problemId, $identityId];

        if (!is_null($problemsetId)) {
            $cteSubmissionsFeedback = self::$ctesubmissionFeedbackForProblemset;
            $suggestionsCountField = 'IFNULL(ssff.suggestions, 0) AS suggestions';
            $suggestionsJoin = 'LEFT JOIN ssff ON ssff.submission_id = s.submission_id';
            $whereClause = ' AND s.problemset_id = ?';
            $params = [$problemId, $identityId, $problemsetId];
        }

        $sql = "{$cteSubmissionsFeedback}
            SELECT
                p.alias,
                s.guid,
                s.language,
                s.status,
                s.verdict,
                r.runtime,
                r.penalty,
                r.memory,
                IF(
                    c.score_mode = 'all_or_nothing' AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                IF(
                    c.score_mode = 'all_or_nothing' AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                s.`time`,
                s.submit_delay,
                i.username, IFNULL(i.country_id, 'xx') AS country,
                c.alias AS contest_alias, IFNULL(s.`type`, 'normal') AS `type`,
                IFNULL(ur.classname, 'user-rank-unranked') AS classname,
                {$extraFields},
                JSON_OBJECTAGG(
                    IFNULL(rg.group_name, ''),
                    rg.score
                ) AS score_by_group,
                {$suggestionsCountField}
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            {$suggestionsJoin}
            LEFT JOIN
                Runs_Groups rg ON r.run_id = rg.run_id
            {$extraFieldsJoin}
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            LEFT JOIN
                User_Rank ur
            ON
                ur.user_id = i.user_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = s.problem_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = s.problemset_id
            WHERE
                s.problem_id = ? AND s.identity_id = ? {$whereClause}
            GROUP BY
                c.score_mode,
                c.alias,
                s.guid
        ;";

        /** @var list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: string, guid: string, language: string, memory: int, output: string, penalty: int, runtime: int, score: float, score_by_group: null|string, status: string, status_memory: string, status_runtime: string, submit_delay: int, suggestions: int, time: \OmegaUp\Timestamp, type: string, username: string, verdict: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $runs = [];
        foreach ($rs as &$record) {
            /** @var array<string, float|null>|null */
            $record['score_by_group'] = json_decode(
                $record['score_by_group'] ?? '',
                associative: true
            );
            if (is_null($record['score_by_group'])) {
                unset($record['score_by_group']);
            }
            $runs[] = $record;
        }

        return $runs;
    }

    /**
     * @return list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, feedback_author: null|string, feedback_author_classname: string, feedback_content: null|string, feedback_date: \OmegaUp\Timestamp|null, guid: string, language: string, memory: int, penalty: int, runtime: int, score: float, status: string, submit_delay: int, time: \OmegaUp\Timestamp, type: string, username: string, verdict: string}>
     */
    final public static function getForCourseProblemDetails(
        int $problemId,
        ?int $problemsetId,
        int $identityId
    ): array {
        $sql = '
            SELECT
                p.alias,
                s.guid,
                s.language,
                s.status,
                s.verdict,
                r.runtime,
                r.penalty,
                r.memory,
                IF(
                    c.score_mode = "all_or_nothing" AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                IF(
                    c.score_mode = "all_or_nothing" AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                s.`time`,
                s.submit_delay,
                i.username, IFNULL(i.country_id, "xx") AS country,
                c.alias AS contest_alias, IFNULL(s.`type`, "normal") AS `type`,
                IFNULL(
                    (
                        SELECT
                            ur.classname
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = i.user_id
                    ),
                    "user-rank-unranked"
                ) AS classname,
                sf.feedback as feedback_content,
                ii.username as feedback_author,
                IFNULL(
                    (
                        SELECT
                            ur.classname
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = ii.user_id
                    ),
                    "user-rank-unranked"
                ) AS feedback_author_classname,
                sf.date as feedback_date,
                sf.`range_bytes_start` as start_feedback_range,
                sf.`range_bytes_end` as end_feedback_range
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = s.problem_id
            LEFT JOIN
                Submission_Feedback sf
            ON
                sf.submission_id = s.submission_id
            LEFT JOIN
                Identities ii
            ON
                ii.identity_id = sf.identity_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = s.problemset_id
            WHERE
                s.problem_id = ? AND s.identity_id = ?
        ';
        $params = [$problemId, $identityId];
        if (!is_null($problemsetId)) {
            $sql .= ' AND s.problemset_id = ?';
            $params[] = $problemsetId;
        }
        /** @var list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, end_feedback_range: int|null, feedback_author: null|string, feedback_author_classname: string, feedback_content: null|string, feedback_date: \OmegaUp\Timestamp|null, guid: string, language: string, memory: int, penalty: int, runtime: int, score: float, start_feedback_range: int|null, status: string, submit_delay: int, time: \OmegaUp\Timestamp, type: string, username: string, verdict: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Returns the time of the next submission to the current problem.
     * The calculation logic for the next submission time is delegated to
     * `getTimeGap`.
     */
    final public static function nextSubmissionTimestamp(
        ?\OmegaUp\DAO\VO\Contests $contest = null,
        ?\OmegaUp\Timestamp $lastSubmissionTime = null
    ): \OmegaUp\Timestamp {
        $submissionGap = \OmegaUp\Controllers\Run::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submissionGap = max(
                $submissionGap,
                intval($contest->submissions_gap)
            );
        }
        return self::getTimeGap($submissionGap, $lastSubmissionTime);
    }

    /**
     * Returns the time of the next execution to the current problem.
     * The calculation logic for the next execution time is delegated to
     * `getTimeGap`.
     */
    final public static function nextExecutionTimestamp(
        ?\OmegaUp\Timestamp $lastExecutionTime = null
    ): \OmegaUp\Timestamp {
        return self::getTimeGap(
            \OmegaUp\Controllers\Run::$defaultExecutionGap,
            $lastExecutionTime
        );
    }

    /**
     * Calculates the timestamp for the next activity based on a specified
     * time gap.
     */
    public static function getTimeGap(
        int $initialGap,
        ?\OmegaUp\Timestamp $lastActivityTime = null
    ): \OmegaUp\Timestamp {
        if (is_null($lastActivityTime)) {
            return new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        }

        return new \OmegaUp\Timestamp($lastActivityTime->time + $initialGap);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Runs>
     */
    final public static function searchWithRunIdGreaterThan(
        int $problemId,
        int $submissionId
    ): array {
        $sql = '
            SELECT
                r.commit,
                IF(
                    c.score_mode = "all_or_nothing" AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                r.judged_by,
                r.memory,
                r.penalty,
                r.run_id,
                r.runtime,
                IF(
                    c.score_mode = "all_or_nothing" AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                s.status,
                r.submission_id,
                r.time,
                s.verdict,
                r.version
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = s.problemset_id
            WHERE
                s.problem_id = ? AND s.submission_id >= ?
            ORDER BY
                s.submission_id ASC;
        ';

        $result = [];
        /** @var array{commit: string, contest_score: float|null, judged_by: null|string, memory: int, penalty: int, run_id: int, runtime: int, score: float, status: string, submission_id: int, time: \OmegaUp\Timestamp, verdict: string, version: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$problemId, $submissionId]
            ) as $row
        ) {
            $result[] = new \OmegaUp\DAO\VO\Runs($row);
        }
        return $result;
    }

    /**
     * Recalculate the contest_score of all problemset and problem Runs
     */
    public static function recalculateScore(
        int $problemsetId,
        int $problemId,
        float $currentPoints,
        float $originalPoints
    ): int {
        $sql = '
            UPDATE
              Runs r
            INNER JOIN
              Submissions s
              ON s.submission_id = r.submission_id
            SET
              r.contest_score = r.score * ?
            WHERE
              s.problemset_id = ? AND
              s.problem_id = ?;
        ';

        $params = [
            $currentPoints,
            $problemsetId,
            $problemId
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Recalculate contest runs with the following rules:
     *
     * + If penaltyType is none then:
     *   - penalty = 0.
     * + If penaltyType is runtime then:
     *   - penalty = runtime.
     * + If penaltyType is anything else then:
     *   - penalty = submit_delay
     */
    public static function recalculatePenaltyForContest(
        \OmegaUp\DAO\VO\Contests $contest
    ): int {
        $penaltyType = $contest->penalty_type;
        if ($penaltyType == 'none') {
            $sql = '
                UPDATE
                    Runs r
                INNER JOIN
                    Submissions s
                    ON s.submission_id = r.submission_id
                SET
                    r.penalty = 0
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penaltyType == 'runtime') {
            $sql = '
                UPDATE
                    Runs r
                INNER JOIN
                    Submissions s
                    ON s.submission_id = r.submission_id
                SET
                    r.penalty = r.runtime
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penaltyType == 'contest_start') {
            $sql = '
                UPDATE
                    `Runs` r
                INNER JOIN
                    `Submissions` s
                    ON s.submission_id = r.submission_id
                INNER JOIN `Contests` c ON (c.problemset_id = s.problemset_id)
                SET
                    r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(s.time, c.start_time))/60)
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penaltyType == 'problem_open') {
            $sql = '
                UPDATE
                    `Runs` r
                INNER JOIN
                    `Submissions` s
                    ON s.submission_id = r.submission_id
                INNER JOIN
                    `Problemset_Problem_Opened` ppo
                    ON (ppo.problemset_id = s.problemset_id
                        AND ppo.problem_id = s.problem_id
                        AND ppo.identity_id = s.identity_id)
                SET
                    r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(s.time, ppo.open_time))/60)
                WHERE
                    s.problemset_id = ?;
            ';
        } else {
            return 0;
        }
        $params = [$contest->problemset_id];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Creates any necessary runs for submissions against a problem, given a
     * problem version.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     */
    final public static function createRunsForVersion(
        \OmegaUp\DAO\VO\Problems $problem
    ): void {
        $sql = '
            INSERT IGNORE INTO
                Runs (
                    submission_id, version, commit, verdict
                )
            SELECT
                s.submission_id, ?, ?, "JE"
            FROM
                Submissions s
            WHERE
                s.problem_id = ?
            ORDER BY
                s.submission_id;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [
            $problem->current_version,
            $problem->commit,
            $problem->problem_id,
        ]);
    }

    /**
     * Update the version of the non-problemset runs of a problem to the
     * current version.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     */
    final public static function updateVersionToCurrent(\OmegaUp\DAO\VO\Problems $problem): void {
        $sql = '
            UPDATE
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.submission_id = s.submission_id
            SET
                s.current_run_id = r.run_id
            WHERE
                s.problemset_id IS NULL AND
                r.version = ? AND
                s.problem_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$problem->current_version, $problem->problem_id]
        );
    }

    /**
     * Gets the runs that were inserted due to a version change.
     *
     * @return list<\OmegaUp\DAO\VO\Runs>
     */
    final public static function getNewRunsForVersion(\OmegaUp\DAO\VO\Problems $problem): array {
        $sql = '
            SELECT
                r.run_id
            FROM
                Runs r
            INNER JOIN
                Submissions s
            ON
                s.submission_id = r.submission_id
            WHERE
                r.status = "new" AND
                r.version = ? AND
                s.problem_id = ?;
        ';
        $params = [$problem->current_version, $problem->problem_id];

        $result = [];
        /** @var array{run_id: int} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $row
        ) {
            $result[] = new \OmegaUp\DAO\VO\Runs($row);
        }
        return $result;
    }

    /**
     * Gets a report of the runs that could have score changes due to a version
     * change.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem      the problem.
     * @param ?int     $problemsetId the optional problemset.
     * @param string   $oldVersion   the old version.
     * @param string   $newVersion   the new version.
     *
     * @return list<array{guid: string, new_score: float|null, new_status: string|null, new_verdict: string|null, old_score: float|null, old_status: string|null, old_verdict: string|null, problemset_id: int|null, username: string}>
     */
    final public static function getRunsDiffsForVersion(
        \OmegaUp\DAO\VO\Problems $problem,
        ?int $problemsetId,
        string $oldVersion,
        string $newVersion
    ): array {
        $sql = '
            SELECT
                i.username,
                s.guid,
                s.problemset_id,
                old_runs.status AS old_status,
                old_runs.verdict AS old_verdict,
                old_runs.score AS old_score,
                new_runs.status AS new_status,
                new_runs.verdict AS new_verdict,
                new_runs.score AS new_score
            FROM
                Submissions s
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            LEFT JOIN
                Runs old_runs
            ON
                old_runs.submission_id = s.submission_id AND
                old_runs.version = ?
            LEFT JOIN
                Runs new_runs
            ON
                new_runs.submission_id = s.submission_id AND
                new_runs.version = ?
        ';
        $params = [$oldVersion, $newVersion];

        $clauses = ['s.problem_id = ?'];
        $params[] = $problem->problem_id;

        if (is_null($problemsetId)) {
            $clauses[] = 's.problemset_id IS NULL';
        } else {
            $clauses[] = 's.problemset_id = ?';
            $params[] = $problemsetId;
        }

        $sql .= ' WHERE ' . implode(' AND ', $clauses) . ' ';
        $sql .= '
            ORDER BY
                s.submission_id
            LIMIT 0, 1000;
        ';

        /** @var list<array{guid: string, new_score: float|null, new_status: null|string, new_verdict: null|string, old_score: float|null, old_status: null|string, old_verdict: null|string, problemset_id: int|null, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }
}
