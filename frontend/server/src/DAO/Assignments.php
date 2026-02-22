<?php

namespace OmegaUp\DAO;

/**
 * Assignments Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Assignments}.
 *
 * @access public
 * @package docs
 *
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, max_points: float, name: string, opened: bool, order: int, problemCount: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 */
class Assignments extends \OmegaUp\DAO\Base\Assignments {
    public static function getProblemset(
        int $courseId,
        string $assignmentAlias
    ): ?\OmegaUp\DAO\VO\Problemsets {
        $sql = 'SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problemsets::FIELD_NAMES,
            'p'
        ) . '
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets p
                ON
                    a.problemset_id = p.problemset_id
                WHERE
                    a.course_id = ?
                    AND a.alias = ?';
        $params = [$courseId, $assignmentAlias];

        /** @var array{access_mode: string, acl_id: int, assignment_id: int|null, contest_id: int|null, interview_id: int|null, languages: null|string, needs_basic_information: bool, problemset_id: int, requests_user_information: string, scoreboard_url: string, scoreboard_url_admin: string, type: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Problemsets($rs);
    }

    /**
     * Returns each problem with the statistics of the runs submmited by the students
     *
     * @return list<array{assignment_alias: string, average: float, avg_runs: float, completed_score_percentage: float, high_score_percentage: float, low_score_percentage: float, max_points: float, maximum: float, minimum: float, problem_alias: string, variance: float}>
     */
    public static function getAssignmentsProblemsStatistics(
        int $courseId,
        int $groupId
    ): array {
        $sql = '
        SELECT
            bpr.assignment_alias,
            bpr.problem_alias,
            IFNULL(VARIANCE(bpr.max_user_score_for_problem), 0) AS variance,
            IFNULL(AVG(bpr.max_user_score_for_problem), 0) AS average,
            IFNULL(AVG(
                CASE WHEN bpr.max_user_percent_for_problem >= 1 THEN 1 ELSE 0 END
            ) * 100, 0) AS completed_score_percentage,
            IFNULL(AVG(
                CASE WHEN bpr.max_user_percent_for_problem > 0.6 THEN 1 ELSE 0 END
            ) * 100, 0) AS high_score_percentage,
            IFNULL(AVG(
                CASE WHEN bpr.max_user_percent_for_problem = 0 THEN 1 ELSE 0 END
            ) * 100, 0) AS low_score_percentage,
            IFNULL(MIN(bpr.max_user_score_for_problem), 0) as minimum,
            IFNULL(MAX(bpr.max_user_score_for_problem), 0) as maximum,
            bpr.max_points,
            IFNULL(AVG(bpr.run_count), 0) AS avg_runs
        FROM (
            SELECT
                pr.assignment_id,
                pr.assignment_alias,
                pr.problem_alias,
                pr.problem_id,
                pr.order,
                pr.max_points,
                IFNULL(MAX(`r`.`contest_score`), 0) AS max_user_score_for_problem,
                IFNULL(MAX(`r`.`score`), 0) AS max_user_percent_for_problem,
                IFNULL(COUNT(`r`.`submission_id`), 0) AS run_count
            FROM
                `Groups_Identities` AS `gi`
            CROSS JOIN
                (
                SELECT
                    `a`.`assignment_id`,
                    `a`.`alias` AS assignment_alias,
                    `a`.`problemset_id`,
                    `p`.`problem_id`,
                    `p`.`alias` AS problem_alias,
                    `psp`.`points` as max_points,
                    `psp`.`order`
                FROM
                    `Assignments` AS `a`
                INNER JOIN
                    `Problemset_Problems` AS `psp` ON `psp`.`problemset_id` = `a`.`problemset_id`
                INNER JOIN
                    `Problems` AS `p` ON `p`.`problem_id` = `psp`.`problem_id`
                WHERE
                    `a`.`course_id` = ?
                    AND `p`.`languages` <> ""
                GROUP BY
                    `a`.`assignment_id`, `p`.`problem_id`
                ) AS pr
            LEFT JOIN
                `Submissions` AS `s`
            ON
                `s`.`problem_id` = `pr`.`problem_id`
                AND `s`.`identity_id` = `gi`.`identity_id`
                AND `s`.`problemset_id` = `pr`.`problemset_id`
            LEFT JOIN
                `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
            WHERE
                `gi`.`group_id` = ?
            GROUP BY
                `gi`.`identity_id`, `pr`.`assignment_id`, `pr`.`problem_id`
        ) AS bpr
        GROUP BY
            bpr.problem_alias, bpr.assignment_alias
        ORDER BY
            bpr.assignment_id, bpr.order, bpr.problem_id;
        ';

        /** @var list<array{assignment_alias: string, average: float, avg_runs: float, completed_score_percentage: float, high_score_percentage: float, low_score_percentage: float, max_points: float, maximum: float, minimum: float, problem_alias: string, variance: float}> */
        $results = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [ $courseId, $groupId ]
        );
        return $results;
    }

    /**
     * Returns each problem with the count of each verdict
     *
     * @return list<array{assignment_alias: string, problem_alias: string, problem_id: int, runs: int, verdict: null|string}>
     */
    public static function getAssignmentVerdictDistribution(
        int $courseId,
        int $groupId
    ): array {
        $sql = '
        SELECT
            pr.assignment_alias,
            pr.problem_alias,
            pr.problem_id,
            `r`.`verdict` AS verdict,
            COUNT(*) AS runs
        FROM
            `Groups_Identities` AS `gi`
        CROSS JOIN
            (
            SELECT
                `a`.`assignment_id`,
                `a`.`alias` AS assignment_alias,
                `a`.`problemset_id`,
                `p`.`problem_id`,
                `p`.`alias` AS problem_alias,
                `psp`.`order`
            FROM
                `Assignments` AS `a`
            INNER JOIN
                `Problemset_Problems` AS `psp` ON `psp`.`problemset_id` = `a`.`problemset_id`
            INNER JOIN
                `Problems` AS `p` ON `p`.`problem_id` = `psp`.`problem_id`
            WHERE
                `a`.`course_id` = ?
                AND `p`.`languages` <> ""
            GROUP BY
                `a`.`assignment_id`, `p`.`problem_id`
            ) AS pr
        LEFT JOIN
            `Submissions` AS `s`
        ON
            `s`.`problem_id` = `pr`.`problem_id`
            AND `s`.`identity_id` = `gi`.`identity_id`
            AND `s`.`problemset_id` = `pr`.`problemset_id`
        LEFT JOIN
            `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
        WHERE
            `gi`.`group_id` = ? AND `r`.`status` = "ready" AND `s`.`type` = "normal"
        GROUP BY
            `gi`.`identity_id`, `pr`.`assignment_id`, `pr`.`problem_id`, verdict
        ORDER BY
            pr.order, pr.problem_id, `pr`.`assignment_id`, verdict;
        ';

        /** @var list<array{assignment_alias: string, problem_alias: string, problem_id: int, runs: int, verdict: null|string}> */
        $results = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [ $courseId, $groupId ]
        );
        return $results;
    }

    /**
     * @return array<string, int>
     */
    public static function getAssignmentCountsForCourse(int $courseId): array {
        $sql = 'SELECT a.assignment_type, COUNT(*) AS count
                FROM Assignments a
                WHERE a.course_id = ?
                GROUP BY a.assignment_type;';
        /** @var list<array{assignment_type: string, count: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
        $counts = [];
        foreach ($rs as $row) {
            $counts[$row['assignment_type']] = intval($row['count']);
        }
        return $counts;
    }

    public static function getAssignmentForProblemset(?int $problemsetId): ?\OmegaUp\DAO\VO\Assignments {
        if ($problemsetId === null) {
            return null;
        }

        return self::getByProblemset($problemsetId);
    }

    final public static function getByProblemset(int $problemsetId): ?\OmegaUp\DAO\VO\Assignments {
        $sql = 'SELECT * FROM Assignments WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemsetId];

        /** @var array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, start_time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    final public static function getByAliasAndCourse(
        ?string $assignmentAlias,
        int $courseId
    ): ?\OmegaUp\DAO\VO\Assignments {
        $sql = 'SELECT
                ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Assignments::FIELD_NAMES,
            'Assignments'
        ) . '
                FROM
                    Assignments
                WHERE
                    course_id = ?
                AND
                    alias = ?
                LIMIT 1;';

        /** @var array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, start_time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$courseId, $assignmentAlias]
        );
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    /**
     * @return null|array{scoreboard_url: string, scoreboard_url_admin: string}
     */
    final public static function getByIdWithScoreboardUrls(int $assignmentId) {
        $sql = '
                SELECT
                   ps.scoreboard_url,
                   ps.scoreboard_url_admin
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets ps
                ON
                    ps.problemset_id = a.problemset_id
                WHERE
                    a.assignment_id = ? LIMIT 1;';
        /** @var null|array{scoreboard_url: string, scoreboard_url_admin: string} */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$assignmentId]
        );
    }

    /**
      * Update assignments order.
      */
    final public static function updateAssignmentsOrder(
        int $assignmentId,
        int $order
    ): int {
        $sql = 'UPDATE `Assignments` SET `order` = ? WHERE `assignment_id` = ?;';
        $params = [
            $order,
            $assignmentId,
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Get the course assignments sorted by order and start_time
     *
     * @return list<CourseAssignment>
     */
    final public static function getSortedCourseAssignments(
        int $courseId
    ): array {
        $sql = '
            SELECT
               `a`.`problemset_id`,
               `a`.`name`,
               `a`.`description`,
               `a`.`alias`,
               `a`.`assignment_type`,
               `a`.`start_time`,
               `a`.`finish_time`,
               `a`.`max_points`,
               `a`.`publish_time_delay`,
               `a`.`order`,
                COUNT(`psp`.`problem_id`) AS `problem_count`,
                COUNT(`s`.`submission_id`) AS `has_runs`,
               `ps`.`scoreboard_url`,
               `ps`.`scoreboard_url_admin`,
                false AS opened
            FROM
                `Assignments` `a`
            INNER JOIN
                `Problemsets` `ps`
            ON
                `ps`.`problemset_id` = `a`.`problemset_id`
            LEFT JOIN
                `Problemset_Problems` `psp`
            ON
                `psp`.`problemset_id` = `ps`.`problemset_id`
            LEFT JOIN
                `Submissions` `s`
            ON
                `ps`.`problemset_id` = `s`.`problemset_id`
            WHERE
                course_id = ?
            GROUP BY
                `a`.`assignment_id`
            ORDER BY
                `order` ASC,
                `start_time` ASC,
                `a`.`assignment_id` ASC;
        ';

        /** @var list<array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: int, max_points: float, name: string, opened: int, order: int, problem_count: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
        $assignments = [];
        foreach ($rs as $row) {
            $row['has_runs'] = $row['has_runs'] > 0;
            $row['problemCount'] = $row['problem_count'];
            $row['opened'] = boolval($row['opened']);
            unset($row['problem_count']);
            $assignments[] = $row;
        }
        return $assignments;
    }

    /**
     * Since Problemsets and Assignments tables are related to each other, it
     * is necessary to unlink the assignment in Problemsets table.
     */
    public static function unlinkProblemset(
        \OmegaUp\DAO\VO\Assignments $assignment,
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): void {
        $sql = '
            UPDATE
                `Problemsets`
            SET
                `assignment_id` = NULL
            WHERE
                `problemset_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$problemset->problemset_id]
        );
    }

    public static function getNextPositionOrder(int $courseId): int {
        $sql = '
            SELECT
                COUNT(a.assignment_id)
            FROM
                Assignments a
            WHERE
                a.course_id = ?;
            ';

        /** @var int|null */
        $numberOfAssignments = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$courseId]
        );
        return intval($numberOfAssignments) + 1;
    }
}
