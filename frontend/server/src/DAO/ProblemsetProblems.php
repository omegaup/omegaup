<?php

namespace OmegaUp\DAO;

/**
 * ProblemsetProblems Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetProblems}.
 *
 * @access public
 */
class ProblemsetProblems extends \OmegaUp\DAO\Base\ProblemsetProblems {
    /**
     * @return array<string, array{name: string, description: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp|null, order: int, max_points: float, assignment_alias: string, assignment_type: string, publish_time_delay: int|null, problems: array{problem_alias: string, problem_id: int, order: int}[]}>
     */
    final public static function getProblemsAssignmentByCourseAlias(
        \OmegaUp\DAO\VO\Courses $course
    ): array {
        // Build SQL statement
        $sql = '
            SELECT
                a.name,
                a.alias AS assignment_alias,
                a.description,
                a.start_time,
                a.finish_time,
                a.assignment_type,
                a.order,
                a.max_points,
                p.alias AS problem_alias,
                a.publish_time_delay,
                p.problem_id,
                pp.order as problem_order
            FROM
                Problems p
            INNER JOIN
                Problemset_Problems pp ON pp.problem_id = p.problem_id
            INNER JOIN
                Assignments a ON pp.problemset_id = a.problemset_id
            INNER JOIN
                Courses c ON a.course_id = c.course_id
            WHERE
                c.alias = ?
            ORDER BY
                a.`assignment_id` ASC,
                pp.`order` ASC,
                `pp`.`problem_id` ASC;
        ';
        $val = [$course->alias];
        /** @var list<array{assignment_alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problem_alias: string, problem_id: int, problem_order: int, publish_time_delay: int|null, start_time: \OmegaUp\Timestamp}> $problemsAssignments */
        $problemsAssignments = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $val
        );

        $result = [];

        foreach ($problemsAssignments as $assignment) {
            $assignmentAlias = strval($assignment['assignment_alias']);
            if (!isset($result[$assignmentAlias])) {
                $result[$assignmentAlias] = [
                    'name' => $assignment['name'],
                    'description' => $assignment['description'],
                    'start_time' => $assignment['start_time'],
                    'finish_time' => $assignment['finish_time'],
                    'order' => $assignment['order'],
                    'max_points' => $assignment['max_points'],
                    'assignment_alias' => $assignment['assignment_alias'],
                    'assignment_type' => $assignment['assignment_type'],
                    'publish_time_delay' => $assignment['publish_time_delay'],
                    'problems' => [],
                ];
            }
            $result[$assignmentAlias]['problems'][] = [
                'problem_alias' => $assignment['problem_alias'],
                'problem_id' => $assignment['problem_id'],
                'order' => $assignment['problem_order'],
            ];
        }

        return $result;
    }

    /*
     * Get number of problems in problemset.
     */
    final public static function countProblemsetProblems(
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): int {
        $sql = '
            SELECT
                COUNT(pp.problem_id)
            FROM
                Problemset_Problems pp
            WHERE
                pp.problemset_id = ?;
        ';
        $val = [$problemset->problemset_id];
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Get problemset problems including problemset alias, points, and order
     *
     * @return list<array{accepted: int, alias: string, commit: string, difficulty: float, input_limit: int, languages: string, order: int, points: float, problem_id: int, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}>
     */
    final public static function getProblemsByProblemset(
        int $problemsetId
    ): array {
        // Build SQL statement
        $sql = 'SELECT
                    p.title,
                    p.problem_id,
                    p.alias,
                    p.visibility,
                    p.visits,
                    p.submissions,
                    p.accepted,
                    p.quality_seal,
                    p.input_limit,
                    IFNULL(p.difficulty, 0.0) AS difficulty,
                    pp.order,
                    p.languages,
                    pp.points,
                    pp.commit,
                    pp.version
                FROM
                    Problems p
                INNER JOIN
                    Problemset_Problems pp
                ON
                    pp.problem_id = p.problem_id
                WHERE
                    pp.problemset_id = ?
                ORDER BY
                    pp.order, pp.problem_id ASC;';

        /** @var list<array{accepted: int, alias: string, commit: string, difficulty: float, input_limit: int, languages: string, order: int, points: float, problem_id: int, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * @return list<array{accepted: int, alias: string, commit: string, difficulty: float, input_limit: int, languages: string, order: int, points: float, problem_id: int, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}>
     */
    final public static function getProblemsByAssignmentAlias(
        string $assignmentAlias,
        string $courseAlias
    ): array {
        // Build SQL statement
        $sql = 'SELECT
                    p.title,
                    p.problem_id,
                    p.alias,
                    p.visibility,
                    p.visits,
                    p.submissions,
                    p.accepted,
                    p.quality_seal,
                    p.input_limit,
                    IFNULL(p.difficulty, 0.0) AS difficulty,
                    pp.order,
                    p.languages,
                    pp.points,
                    pp.commit,
                    pp.version
                FROM
                    Problems p
                INNER JOIN
                    Problemset_Problems pp
                ON
                    pp.problem_id = p.problem_id
                INNER JOIN
                    Assignments a
                ON
                    a.problemset_id = pp.problemset_id
                INNER JOIN
                    Courses c
                ON
                    c.course_id = a.course_id
                WHERE
                    a.alias = ?
                    AND c.alias = ?
                ORDER BY
                    pp.order, pp.problem_id ASC;';

        /** @var list<array{accepted: int, alias: string, commit: string, difficulty: float, input_limit: int, languages: string, order: int, points: float, problem_id: int, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$assignmentAlias, $courseAlias]
        );
    }

    /*
     * Get problemset problems including problemset alias, points, and order
     *
     * @return list<\OmegaUp\DAO\VO\ProblemsetProblems>
     */
    final public static function getByProblemset(int $problemsetId): array {
        // Build SQL statement
        $sql = 'SELECT
                    *
                FROM
                    Problemset_Problems
                WHERE
                    problemset_id = ?
                ORDER BY
                    `order`, `problem_id` ASC;';

        /** @var list<array{commit: string, order: int, points: float, problem_id: int, problemset_id: int, version: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );

        $problemsetProblems = [];
        foreach ($rs as $row) {
            $problemsetProblems[] = new \OmegaUp\DAO\VO\ProblemsetProblems(
                $row
            );
        }
        return $problemsetProblems;
    }

    /*
     * Get relevant problems including problemset alias
     * @return list<\OmegaUp\DAO\VO\Problems>
     */
    final public static function getRelevantProblems(
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): array {
        // Build SQL statement
        $sql = '
            SELECT
                p.problem_id, p.alias, pp.version AS current_version
            FROM
                Problemset_Problems pp
            INNER JOIN
                Problems p ON p.problem_id = pp.problem_id
            WHERE
                pp.problemset_id = ?
            ORDER BY pp.`order`, `pp`.`problem_id` ASC;';
        $val = [$problemset->problemset_id];
        $result = [];
        /** @var array{alias: string, current_version: string, problem_id: int} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            $result[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $result;
    }

    /**
     * Copy problemset problems from one problem set to the new problemset
     */
    public static function copyProblemset(
        int $newProblemsetId,
        int $oldProblemsetId
    ): int {
        $sql = '
            INSERT INTO
                Problemset_Problems (problemset_id, problem_id, commit, version, points, `order`)
            SELECT
                ?, problem_id, commit, version, points, `order`
            FROM
                Problemset_Problems
            WHERE
                Problemset_Problems.problemset_id = ?;
        ';
        $params = [$newProblemsetId, $oldProblemsetId];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
      * Update problemset order.
      */
    final public static function updateProblemsOrder(
        int $problemsetId,
        int $problemId,
        int $order
    ): int {
        $sql = '
            UPDATE
                `Problemset_Problems`
            SET
                `order` = ?
            WHERE
                `problemset_id` = ? AND `problem_id` = ?;
        ';
        $params = [
            $order,
            $problemsetId,
            $problemId,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /*
     * Get max points posible for contest
     */
    final public static function getMaxPointsByProblemset(int $problemsetId): float {
        // Build SQL statement
        $sql = '
            SELECT
                IFNULL(SUM(points), 0.0) as max_points
            FROM
                Problemset_Problems
            WHERE
                problemset_id = ?;
        ';

        /** @var float */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * Update the version of the problem across all problemsets to the current
     * version.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem         the problem.
     * @param \OmegaUp\DAO\VO\Users    $user            the user that is making the change.
     * @param string   $updatePublished the way to update the problemset runs.
     */
    final public static function updateVersionToCurrent(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Users $user,
        string $updatePublished
    ): void {
        $now = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        if ($updatePublished === \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS) {
            $sql = '
                UPDATE
                    Problemset_Problems pp
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = pp.problemset_id
                INNER JOIN
                    ACLs acl
                ON
                    acl.acl_id = p.acl_id
                INNER JOIN
                    Contests c
                ON
                    c.contest_id = p.contest_id
                SET
                    pp.commit = ?, pp.version = ?
                WHERE
                    c.finish_time >= ? AND
                    pp.problem_id = ? AND
                    acl.owner_id = ?;
            ';
            \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [
                $problem->commit,
                $problem->current_version,
                $now,
                $problem->problem_id,
                $user->user_id,
            ]);

            $sql = '
                UPDATE
                    Problemset_Problems pp
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = pp.problemset_id
                INNER JOIN
                    ACLs acl
                ON
                    acl.acl_id = p.acl_id
                INNER JOIN
                    Assignments a
                ON
                    a.assignment_id = p.assignment_id
                SET
                    pp.commit = ?, pp.version = ?
                WHERE
                    a.finish_time >= ? AND
                    pp.problem_id = ? AND
                    acl.owner_id = ?;
            ';
            \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [
                $problem->commit,
                $problem->current_version,
                $now,
                $problem->problem_id,
                $user->user_id,
            ]);
        } elseif ($updatePublished === \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS) {
            $problemsets = [];

            $sql = '
                SELECT
                    p.problemset_id, p.acl_id
                FROM
                    Problemset_Problems pp
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = pp.problemset_id
                INNER JOIN
                    Contests c
                ON
                    c.contest_id = p.contest_id
                WHERE
                    c.finish_time >= ? AND
                    pp.problem_id = ?;
            ';
            /** @var list<array{acl_id: int, problemset_id: int}> */
            $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [
                $now,
                $problem->problem_id,
            ]);
            foreach ($rs as $row) {
                array_push($problemsets, new \OmegaUp\DAO\VO\Problemsets($row));
            }

            $sql = '
                SELECT
                    p.problemset_id, p.acl_id
                FROM
                    Problemset_Problems pp
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = pp.problemset_id
                INNER JOIN
                    Assignments a
                ON
                    a.assignment_id = p.assignment_id
                WHERE
                    a.finish_time >= ? AND
                    pp.problem_id = ?;
            ';
            /** @var list<array{acl_id: int, problemset_id: int}> */
            $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [
                $now,
                $problem->problem_id,
            ]);
            foreach ($rs as $row) {
                array_push($problemsets, new \OmegaUp\DAO\VO\Problemsets($row));
            }

            $identity = \OmegaUp\DAO\Identities::getByPK(
                intval($user->main_identity_id)
            );
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $problemsets = array_filter(
                $problemsets,
                fn (\OmegaUp\DAO\VO\Problemsets $problemset) => \OmegaUp\Authorization::isAdmin(
                    $identity,
                    $problemset
                )
            );

            if (!empty($problemsets)) {
                $problemsetIds = array_map(
                    fn (\OmegaUp\DAO\VO\Problemsets $p) => intval(
                        $p->problemset_id
                    ),
                    $problemsets
                );
                $problemsetPlaceholders = implode(
                    ', ',
                    array_fill(
                        0,
                        count(
                            $problemsetIds
                        ),
                        '?'
                    )
                );

                $sql = "
                    UPDATE
                        Problemset_Problems pp
                    SET
                        pp.commit = ?, pp.version = ?
                    WHERE
                        pp.problem_id = ? AND
                        pp.problemset_id IN ($problemsetPlaceholders);
                ";
                \OmegaUp\MySQLConnection::getInstance()->Execute($sql, array_merge([
                    $problem->commit,
                    $problem->current_version,
                    $problem->problem_id,
                    ], $problemsetIds));
            }
        }

        $sql = '
            UPDATE
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.submission_id = s.submission_id
            INNER JOIN
                Problemset_Problems pp
            ON
                pp.problemset_id = s.problemset_id AND
                pp.problem_id = s.problem_id AND
                pp.version = r.version
            SET
                s.current_run_id = r.run_id
            WHERE
                pp.version = ? AND
                pp.problem_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$problem->current_version, $problem->problem_id]
        );
    }

    public static function updateProblemsetProblemSubmissions(
        \OmegaUp\DAO\VO\ProblemsetProblems $problemsetProblem
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
                s.problemset_id = ? AND
                s.problem_id = ?
            ORDER BY
                s.submission_id;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [
            $problemsetProblem->version,
            $problemsetProblem->commit,
            $problemsetProblem->problemset_id,
            $problemsetProblem->problem_id,
        ]);

        $sql = '
            UPDATE
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.submission_id = s.submission_id
            INNER JOIN
                Problemset_Problems pp
            ON
                pp.problemset_id = s.problemset_id AND
                pp.problem_id = s.problem_id AND
                pp.version = r.version
            SET
                s.current_run_id = r.run_id
            WHERE
                pp.problemset_id = ? AND
                pp.problem_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [
            $problemsetProblem->problemset_id,
            $problemsetProblem->problem_id,
        ]);
    }

    /**
     * It removes all the problems belong to problemset and return the number of
     * affected rows
     */
    public static function removeProblemsFromProblemset(int $problemsetId): int {
        $sql = 'DELETE FROM `Problemset_Problems` WHERE problemset_id = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problemsetId]);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
