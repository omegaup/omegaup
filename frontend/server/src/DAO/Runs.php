<?php

namespace OmegaUp\DAO;

/**
 * Runs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Runs}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Runs extends \OmegaUp\DAO\Base\Runs {
    /**
     * Gets an array of the guids of the pending runs
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
                IFNULL(
                    (
                        SELECT `urc`.`classname` FROM
                            `User_Rank_Cutoffs` `urc`
                        WHERE
                            `urc`.`score` <= (
                                    SELECT
                                        `ur`.`score`
                                    FROM
                                        `User_Rank` `ur`
                                    WHERE
                                        `ur`.`user_id` = `i`.`user_id`
                                )
                        ORDER BY
                            `urc`.`percentile` ASC
                        LIMIT
                            1
                    ),
                    "user-rank-unranked"
                ) `classname`
            FROM
                (SELECT
                    MIN(s.submission_id) submission_id, s.identity_id, r.runtime
                FROM
                    Submissions s
                INNER JOIN
                    Runs r
                ON
                    r.run_id = s.current_run_id
                INNER JOIN
                    (
                        SELECT
                            ss.identity_id, MIN(rr.runtime) AS runtime
                        FROM
                            Submissions ss
                        INNER JOIN
                            Runs rr
                        ON
                            rr.run_id = ss.current_run_id
                        WHERE
                            ss.problem_id = ? AND rr.status = "ready" AND rr.verdict = "AC" AND ss.type = "normal"
                        GROUP BY
                            ss.identity_id
                    ) AS sr ON sr.identity_id = s.identity_id AND sr.runtime = r.runtime
                WHERE
                    s.problem_id = ? AND r.status = "ready" AND r.verdict = "AC" AND s.type= "normal"
                GROUP BY
                    s.identity_id, r.runtime
                ORDER BY
                    r.runtime, submission_id
                LIMIT 0, 10) as runs
            INNER JOIN
                Identities i ON i.identity_id = runs.identity_id
            INNER JOIN
                Submissions s ON s.submission_id = runs.submission_id
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id;';
        $val = [$problemId, $problemId];

        /** @var list<array{classname: string, language: string, memory: int, runtime: int, time: \OmegaUp\Timestamp, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
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
     * @return list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, guid: string, language: string, memory: int, penalty: int, run_id: int, runtime: int, score: float, status: string, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}>
     */
    final public static function getAllRuns(
        ?int $problemset_id,
        ?string $status,
        ?string $verdict,
        ?int $problem_id,
        ?string $language,
        ?int $identity_id,
        ?int $offset,
        ?int $rowcount
    ): array {
        $sql = '
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
                    COALESCE(`c`.`partial_score`, 1) = 0 AND `r`.`score` <> 1,
                        0,
                        `r`.`score`
                ) AS `score`,
                IF(
                    COALESCE(`c`.`partial_score`, 1) = 0 AND `r`.`score` <> 1,
                        0,
                        `r`.`contest_score`
                ) AS `contest_score`,
                `s`.`time`,
                `s`.`submit_delay`,
                `s`.`type`,
                `i`.`username`,
                `p`.`alias`,
                IFNULL(`i`.`country_id`, "xx") `country`,
                `c`.`alias` AS `contest_alias`,
                IFNULL(
                    (
                        SELECT `urc`.`classname` FROM
                            `User_Rank_Cutoffs` `urc`
                        WHERE
                            `urc`.`score` <= (
                                    SELECT
                                        `ur`.`score`
                                    FROM
                                        `User_Rank` `ur`
                                    WHERE
                                        `ur`.`user_id` = `i`.`user_id`
                                )
                        ORDER BY
                            `urc`.`percentile` ASC
                        LIMIT
                            1
                    ),
                    "user-rank-unranked"
                ) `classname`
            FROM
                Submissions s
            USE INDEX(PRIMARY)
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                Contests c ON c.problemset_id = s.problemset_id
        ';
        $where = [];
        $val = [];

        if (!is_null($problemset_id)) {
            $where[] = 's.problemset_id = ?';
            $val[] = $problemset_id;
        }

        if (!is_null($status)) {
            $where[] = 'r.status = ?';
            $val[] = $status;
        }
        if (!is_null($verdict)) {
            if ($verdict === 'NO-AC') {
                $where[] = 'r.verdict <> ?';
                $val[] = 'AC';
            } else {
                $where[] = 'r.verdict = ?';
                $val[] = $verdict;
            }
        }
        if (!is_null($problem_id)) {
            $where[] = 's.problem_id = ?';
            $val[] = $problem_id;
        }
        if (!is_null($language)) {
            $where[] = 's.language = ?';
            $val[] = $language;
        }
        if (!is_null($identity_id)) {
            $where[] = 's.identity_id = ?';
            $val[] = $identity_id;
        }
        if (!empty($where)) {
            $sql .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $sql .= 'ORDER BY s.submission_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = intval($offset);
            $val[] = intval($rowcount);
        }

        /** @var list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, guid: string, language: string, memory: int, penalty: int, run_id: int, runtime: int, score: float, status: string, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
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
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problemset_id = ? AND r.verdict = ? AND r.status = "ready" AND s.`type` = "normal";
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
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                s.problem_id = ? AND r.status = "ready" AND r.verdict = ? AND s.`type` = "normal";
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
                r.verdict AS verdict,
                COUNT(*) AS runs
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.identity_id = ? AND r.status = "ready" AND s.`type` = "normal"
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
                    SELECT urc.classname
                    FROM User_Rank_Cutoffs urc
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
                    LIMIT 1
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
                        IFNULL(ri.is_invited, FALSE) AS is_invited,
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
                ";
                $val = [
                    $problemsetId,
                    $aclId,
                    \OmegaUp\Authorization::CONTESTANT_ROLE,
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
                        OR i.user_id IS NULL);";
                $val = [
                    $groupId,
                    $aclId,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                ];
            }
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
                    (SELECT DISTINCT
                        s.identity_id
                    FROM
                        Submissions s
                    INNER JOIN
                        Runs r
                    ON
                        r.run_id = s.current_run_id
                    WHERE
                        r.verdict NOT IN ('CE', 'JE', 'VE') AND
                        s.problemset_id = ? AND
                        r.status = 'ready' AND
                        s.type = 'normal'
                    ) rc ON i.identity_id = rc.identity_id";
            $val = [$problemsetId];
            if (!is_null($filterUsersBy)) {
                $sql .= ' WHERE i.username LIKE ?';
                $val[] = $filterUsersBy . '%';
            }
            $sql .= ';';
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
                INNER JOIN
                    Runs r
                ON
                    s.current_run_id = r.run_id
                WHERE
                    r.verdict NOT IN (\'CE\', \'JE\', \'VE\')
                    AND s.problem_id = ?
                    AND s.identity_id = ?
                ) AS tried,
                (
                SELECT
                    COUNT(1) AS total
                FROM
                    Submissions s
                INNER JOIN
                    Runs r
                ON
                    s.current_run_id = r.run_id
                WHERE
                    r.verdict IN (\'AC\')
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
     * @return list<array{score: float, penalty: int, contest_score: float|null, problem_id: int, identity_id: int, type: string|null, time: \OmegaUp\Timestamp, submit_delay: int, guid: string}>
     */
    final public static function getProblemsetRuns(
        \OmegaUp\DAO\VO\Problemsets $problemset,
        bool $onlyAC = false
    ): array {
        $sql = '
            SELECT
                IF(
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                r.penalty,
                IF(
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                s.problem_id,
                s.identity_id,
                s.type,
                s.`time`,
                s.submit_delay,
                s.guid
            FROM
                Problemset_Problems pp
            INNER JOIN
                Submissions s
            ON
                s.problemset_id = pp.problemset_id AND
                s.problem_id = pp.problem_id
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = pp.problemset_id
            WHERE
                pp.problemset_id = ? AND
                r.status = \'ready\' AND
                s.type = \'normal\' AND ' .
                ($onlyAC ?
                    "r.verdict IN ('AC') " :
                    "r.verdict NOT IN ('CE', 'JE', 'VE') "
                ) .
            ' ORDER BY s.submission_id;';

        /** @var list<array{contest_score: float|null, guid: string, identity_id: int, penalty: int, problem_id: int, score: float, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemset->problemset_id]
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
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
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
                r.verdict,
                IF(
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
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
     * @return \OmegaUp\DAO\VO\Runs|null
     */
    final public static function getByGUID(string $guid) {
        $sql = '
            SELECT
                `r`.*
            FROM
                `Runs` `r`
            INNER JOIN
                `Submissions` `s`
            ON
                `r`.`submission_id` = `s`.`submission_id`
            WHERE
                `s`.`guid` = ?
            LIMIT
                1;
        ';

        /** @var array{commit: string, contest_score: float|null, judged_by: null|string, memory: int, penalty: int, run_id: int, runtime: int, score: float, status: string, submission_id: int, time: \OmegaUp\Timestamp, verdict: string, version: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$guid]);

        if (is_null($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Runs($row);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Runs>
     */
    final public static function getByProblem(
        int $problemId
    ) {
        $sql = '
            SELECT
                r.*
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
     * @return list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, guid: string, language: string, memory: int, penalty: int, runtime: int, score: float, status: string, submit_delay: int, time: \OmegaUp\Timestamp, type: string, username: string, verdict: string}>
     */
    final public static function getForProblemDetails(
        int $problemId,
        ?int $problemsetId,
        int $identityId
    ): array {
        $sql = '
            SELECT
                p.alias,
                s.guid,
                s.language,
                r.status,
                r.verdict,
                r.runtime,
                r.penalty,
                r.memory,
                IF(
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                IF(
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                s.`time`,
                s.submit_delay,
                i.username, IFNULL(i.country_id, "xx") AS country,
                c.alias AS contest_alias, IFNULL(s.`type`, "normal") AS `type`,
                IFNULL(
                    (
                        SELECT urc.classname
                        FROM User_Rank_Cutoffs urc
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
                        LIMIT 1
                    ),
                    "user-rank-unranked"
                ) AS classname
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
                Problemsets ps
            ON
                ps.problemset_id = s.problemset_id
            LEFT JOIN
                Contests c
            ON
                c.problemset_id = ps.problemset_id
            WHERE
                s.problem_id = ? AND s.identity_id = ?
        ';
        $params = [$problemId, $identityId];
        if (!is_null($problemsetId)) {
            $sql .= ' AND s.problemset_id = ?';
            $params[] = $problemsetId;
        }
        /** @var list<array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, guid: string, language: string, memory: int, penalty: int, runtime: int, score: float, status: string, submit_delay: int, time: \OmegaUp\Timestamp, type: string, username: string, verdict: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Returns the time of the next submission to the current problem
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

        if (is_null($lastSubmissionTime)) {
            return new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        }

        return new \OmegaUp\Timestamp(
            $lastSubmissionTime->time + $submissionGap
        );
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
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
                        0,
                        r.contest_score
                ) AS contest_score,
                r.judged_by,
                r.memory,
                r.penalty,
                r.run_id,
                r.runtime,
                IF(
                    COALESCE(c.partial_score, 1) = 0 AND r.score <> 1,
                        0,
                        r.score
                ) AS score,
                r.status,
                r.submission_id,
                r.time,
                r.verdict,
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
     * + If penalty_type is none then:
     *   - penalty = 0.
     * + If penalty_type is runtime then:
     *   - penalty = runtime.
     * + If penalty_type is anything else then:
     *   - penalty = submit_delay
     */
    public static function recalculatePenaltyForContest(\OmegaUp\DAO\VO\Contests $contest): int {
        $penalty_type = $contest->penalty_type;
        if ($penalty_type == 'none') {
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
        } elseif ($penalty_type == 'runtime') {
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
        } elseif ($penalty_type == 'contest_start') {
            $sql = '
                UPDATE
                    `Runs` r
                INNER JOIN
                    `Submissions` s
                    ON s.submission_id = r.run_id
                INNER JOIN `Contests` c ON (c.problemset_id = s.problemset_id)
                SET
                    r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(s.time, c.start_time))/60)
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penalty_type == 'problem_open') {
            $sql = '
                UPDATE
                    `Runs` r
                INNER JOIN
                    `Submissions` s
                    ON s.submission_id = r.run_id
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
