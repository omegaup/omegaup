<?php

namespace OmegaUp\DAO;

/**
 * Courses Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Courses}.
 * @access public
 * @package docs
 *
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 * @psalm-type FilteredCourse=array{accept_teacher: bool|null, admission_mode: string, alias: string, assignments: list<CourseAssignment>, counts: array<string, int>, finish_time: \OmegaUp\Timestamp|null, is_open: bool, name: string, progress?: float, school_name: null|string, start_time: \OmegaUp\Timestamp}
 * @psalm-type StudentProgress=array{name: string|null, username: string, progress: list<array{assignment_alias: string, assignment_score: float, problems: list<string, float>}>}
 */
class Courses extends \OmegaUp\DAO\Base\Courses {
    /**
     * @return list<\OmegaUp\DAO\VO\Courses>
     */
    public static function findByName(string $name): array {
        $sql = "SELECT DISTINCT c.*
                FROM Courses c
                WHERE c.name
                LIKE CONCAT('%', ?, '%') LIMIT 10";

        /** @var list<array{acl_id: int, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, name: string, needs_basic_information: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}> */
        $resultRows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$name]
        );

        $finalResult = [];
        foreach ($resultRows as $row) {
            $finalResult[] = new \OmegaUp\DAO\VO\Courses($row);
        }
        return $finalResult;
    }

    /**
     * Given a course alias, get all of its assignments. Hides any assignments
     * that have not started, if not an admin.
     *
     * @return list<CourseAssignment>
     */
    public static function getAllAssignments(
        string $alias,
        bool $isAdmin
    ): array {
        // Non-admins should not be able to see assignments that have not
        // started.
        $timeCondition = $isAdmin ? '' : 'AND a.start_time <= CURRENT_TIMESTAMP';
        $sql = "
            SELECT
                a.*,
                COUNT(s.submission_id) AS has_runs,
                p.scoreboard_url,
                p.scoreboard_url_admin
            FROM
                Courses c
            INNER JOIN
                Assignments a
            ON
                a.course_id = c.course_id
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = a.problemset_id
            LEFT JOIN
                Submissions s
            ON
                p.problemset_id = s.problemset_id
            WHERE
                c.alias = ? $timeCondition
            GROUP BY
                a.assignment_id
            ORDER BY
                `order`, start_time;";

        /** @var list<array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: int, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$alias]);

        $ar = [];
        foreach ($rs as $row) {
            unset($row['acl_id']);
            unset($row['assignment_id']);
            unset($row['course_id']);
            $row['has_runs'] = $row['has_runs'] > 0;
            $ar[] = $row;
        }
        return $ar;
    }

    /**
     * @return list<FilteredCourse>
     */
    public static function getCoursesForStudent(int $identityId) {
        $sql = 'SELECT
                    admission_mode,
                    alias,
                    c.course_id,
                    finish_time,
                    c.name AS name,
                    s.name AS school_name,
                    start_time,
                    accept_teacher,
                    IFNULL(pr.progress, 0.0) AS progress,
                    pr.last_submission_time
                FROM Courses c
                INNER JOIN (
                    SELECT g.group_id, gi.accept_teacher
                    FROM Groups_Identities gi
                    INNER JOIN `Groups_` AS g ON g.group_id = gi.group_id
                    WHERE gi.identity_id = ?
                ) gg
                ON c.group_id = gg.group_id
                LEFT JOIN (
                    -- we want a score even if there are no submissions yet
                    SELECT
                        cbpr.course_id,
                        ROUND(SUM(cbpr.total_assignment_score) / SUM(cbpr.max_points) * 100, 2) AS progress,
                        MAX(cbpr.last_submission_time) AS last_submission_time
                    FROM (
                        -- aggregate all runs per assignment
                        SELECT
                            bpr.alias,
                            bpr.course_id,
                            bpr.assignment_id,
                            SUM(best_score_of_problem) AS total_assignment_score,
                            bpr.max_points,
                            MAX(bpr.last_submission_time) AS last_submission_time
                        FROM (
                            -- get all runs belonging to an identity and get the best score
                            SELECT
                                a.alias,
                                a.course_id,
                                a.assignment_id,
                                psp.problem_id,
                                s.identity_id,
                                MAX(r.contest_score) AS best_score_of_problem,
                                a.max_points,
                                MAX(r.time) AS last_submission_time
                            FROM Assignments a
                            INNER JOIN Problemset_Problems psp
                                ON a.problemset_id = psp.problemset_id
                            INNER JOIN Submissions s
                                ON s.problem_id = psp.problem_id
                                AND s.problemset_id = a.problemset_id
                            INNER JOIN Runs r
                                ON r.run_id = s.current_run_id
                            WHERE s.identity_id = ?
                            GROUP BY a.assignment_id, psp.problem_id, s.identity_id
                        ) bpr
                        GROUP BY bpr.assignment_id
                    ) cbpr
                    GROUP BY cbpr.course_id
                ) pr
                ON c.course_id = pr.course_id
                LEFT JOIN
                    Schools s
                ON c.school_id = s.school_id
                ORDER BY
                    pr.last_submission_time DESC;';
        /** @var list<array{accept_teacher: bool|null, admission_mode: string, alias: string, course_id: int, finish_time: \OmegaUp\Timestamp|null, last_submission_time: \OmegaUp\Timestamp|null, name: string, progress: float, school_name: null|string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId, $identityId]
        );

        $courses = [];
        foreach ($rs as $row) {
            $row['assignments'] = [];
            $row['is_open'] = !is_null($row['accept_teacher']);
            $row['counts'] = \OmegaUp\DAO\Assignments::getAssignmentCountsForCourse(
                $row['course_id']
            );
            unset($row['last_submission_time']);
            unset($row['course_id']);
            $courses[] = $row;
        }
        return $courses;
    }

    /**
     * @return list<FilteredCourse>
     */
    public static function getPublicCourses() {
        $sql = '
            SELECT
                course_id,
                admission_mode,
                alias,
                finish_time,
                c.name AS name,
                s.name AS school_name,
                start_time,
                0.0 AS progress,
                0 AS is_open,
                CAST(NULL AS UNSIGNED) AS accept_teacher
            FROM
                Courses c
            LEFT JOIN
                Schools s
            ON c.school_id = s.school_id
            WHERE c.admission_mode = ?;
           ';

        /** @var list<array{accept_teacher: int|null, admission_mode: string, alias: string, course_id: int, finish_time: \OmegaUp\Timestamp|null, is_open: int, name: string, progress: float, school_name: null|string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC]
        );
        $courses = [];
        foreach ($rs as $row) {
            $row['assignments'] = \OmegaUp\DAO\Courses::getAllAssignments(
                $row['alias'],
                /*$isAdmin=*/false
            );
            $row['counts'] = \OmegaUp\DAO\Assignments::getAssignmentCountsForCourse(
                $row['course_id']
            );
            $row['is_open'] = boolval($row['is_open']);
            $row['accept_teacher'] = !is_null($row['accept_teacher'])
              ? boolval($row['accept_teacher'])
              : null;
            unset($row['course_id']);
            $courses[] = $row;
        }
        return $courses;
    }

    /**
     * Returns the list of students in a course
     *
     * @return list<array{name: null|string, username: string}>
     */
    public static function getStudentsInCourse(
        int $courseId,
        int $groupId
    ): array {
        $sql = '
            SELECT
                i.username,
                i.name
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?';

        /** @var list<array{name: null|string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$groupId]
        );
    }

    /**
     * Returns a list of students within a course with their score and progress
     * by problem
     * @return list<array{name: null|string, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string}>
     */
    public static function getStudentsInCourseWithProgressPerAssignment(
        int $courseId,
        int $groupId
    ): array {
        $sql = 'SELECT
                    i.username,
                    i.name,
                    pr.assignment_alias,
                    pr.problem_alias,
                    problem_points,
                    MAX(r.contest_score) AS problem_score
                FROM
                    Groups_Identities AS gi
                CROSS JOIN
                    (
                        SELECT
                            a.assignment_id,
                            a.alias AS assignment_alias,
                            a.problemset_id,
                            p.problem_id,
                            p.alias AS problem_alias,
                            `psp`.`order`,
                            psp.points AS problem_points
                        FROM Assignments a
                        INNER JOIN Problemsets ps
                        ON a.problemset_id = ps.problemset_id
                        INNER JOIN Problemset_Problems psp
                        ON psp.problemset_id = ps.problemset_id
                        INNER JOIN Problems p
                        ON p.problem_id = psp.problem_id
                        WHERE a.course_id = ?
                        GROUP BY a.assignment_id, p.problem_id
                    ) AS pr
                INNER JOIN Identities i
                    ON i.identity_id = gi.identity_id
                LEFT JOIN Submissions s
                    ON s.problem_id = pr.problem_id
                    AND s.identity_id = i.identity_id
                    AND s.problemset_id = pr.problemset_id
                LEFT JOIN Runs r
                    ON r.run_id = s.current_run_id
                WHERE
                    gi.group_id = ?
                GROUP BY
                    i.identity_id, pr.assignment_id, pr.problem_id
                ORDER BY
                    `pr`.`order`;';

        /** @var list<array{assignment_alias: string, name: null|string, problem_alias: string, problem_points: float, problem_score: float|null, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId, $groupId]
        );

        $allProgress = [];
        foreach ($rs as $row) {
            $username = $row['username'];
            if (!isset($allProgress[$username])) {
                $allProgress[$username] = [
                    'name' => $row['name'],
                    'progress' => [],
                    'points' => [],
                    'score' => [],
                    'username' => $username,
                ];
            }

            $assignmentAlias = $row['assignment_alias'];
            $problemAlias = $row['problem_alias'];

            if (!isset($allProgress[$username]['progress'][$assignmentAlias])) {
                $allProgress[$username]['progress'][$assignmentAlias] = [];
            }

            $allProgress[$username]['progress'][$assignmentAlias][$problemAlias] = (
                $row['problem_points'] == 0
            ) ? 0.0 :
            floatval($row['problem_score']) / $row['problem_points'] * 100;

            if (!isset($allProgress[$username]['points'][$assignmentAlias])) {
                $allProgress[$username]['points'][$assignmentAlias] = [];
            }

            $allProgress[$username]['points'][$assignmentAlias][$problemAlias] = $row['problem_points'] ?: 0.0;

            if (!isset($allProgress[$username]['score'][$assignmentAlias])) {
                $allProgress[$username]['score'][$assignmentAlias] = [];
            }

            $allProgress[$username]['score'][$assignmentAlias][$problemAlias] = $row['problem_score'] ?: 0.0;
        }

        usort(
            $allProgress,
            /**
             * @param array{name: string|null, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string} $a
             * @param array{name: string|null, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string} $b
             */
            fn (array $a, array $b) => strcasecmp(
                !empty($a['name']) ? $a['name'] : $a['username'],
                !empty($b['name']) ? $b['name'] : $b['username']
            )
        );
        return $allProgress;
    }

    /**
     * Returns the score per assignment of a user, as well as the maximum score attainable
     *
     * @return array<string, array{score: float, max_score: float}>
     */
    public static function getAssignmentsProgress(
        int $courseId,
        int $identityId
    ) {
        $sql = '
            SELECT
                a.alias as assignment,
                IFNULL(pr.total_score, 0.0) as score,
                a.max_points as max_score
            FROM Assignments a
            LEFT JOIN ( -- we want a score even if there are no submissions yet
                -- aggregate all runs per assignment
                SELECT bpr.alias, bpr.assignment_id, sum(best_score_of_problem) as total_score
                FROM (
                    -- get all runs belonging to an identity and get the best score
                    SELECT a.alias, a.assignment_id, psp.problem_id, s.identity_id, max(r.contest_score) as best_score_of_problem
                    FROM Assignments a
                    INNER JOIN Problemset_Problems psp
                        ON a.problemset_id = psp.problemset_id
                    INNER JOIN Submissions s
                        ON s.problem_id = psp.problem_id
                        AND s.problemset_id = a.problemset_id
                    INNER JOIN Runs r
                        ON r.run_id = s.current_run_id
                    WHERE a.course_id = ? AND s.identity_id = ?
                    GROUP BY a.assignment_id, psp.problem_id, s.identity_id
                ) bpr
                GROUP BY bpr.assignment_id
            ) pr
            ON a.assignment_id = pr.assignment_id
            where a.course_id = ?;
        ';

        /** @var list<array{assignment: string, max_score: float, score: float}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId, $identityId, $courseId]
        );

        $progress = [];
        foreach ($rs as $row) {
            $progress[$row['assignment']] = [
                'score' => $row['score'],
                'max_score' => $row['max_score'],
            ];
        }
        return $progress;
    }

    /**
     * Returns all courses that an identity can manage.
     *
     * @return list<\OmegaUp\DAO\VO\Courses>
     */
    final public static function getAllCoursesAdminedByIdentity(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000
    ): array {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*
            FROM
                Courses AS c
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = c.acl_id
            LEFT JOIN
                Identities uri ON ur.user_id = uri.identity_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = c.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                ai.identity_id = ? OR
                (ur.role_id = ? AND uri.identity_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)
            GROUP BY
                c.course_id
            ORDER BY
                c.course_id DESC
            LIMIT
                ?, ?';
        $params = [
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            $offset,
            $pageSize,
        ];
        /** @var list<array{acl_id: int, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, name: string, needs_basic_information: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            $courses[] = new \OmegaUp\DAO\VO\Courses($row);
        }
        return $courses;
    }

    /**
     * Returns all courses owned by a user.
     * @return \OmegaUp\DAO\VO\Courses[]
     */
    final public static function getAllCoursesOwnedByUser(
        int $userId,
        int $page = 1,
        int $pageSize = 1000
    ): array {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                c.*
            FROM
                Courses AS c
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            WHERE
                a.owner_id = ?
            ORDER BY
                c.course_id DESC
            LIMIT
                ?, ?';
        $params = [
            $userId,
            $offset,
            $pageSize,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new \OmegaUp\DAO\VO\Courses($row));
        }
        return $courses;
    }

    final public static function getByAlias(
        string $alias
    ): ?\OmegaUp\DAO\VO\Courses {
        $sql = 'SELECT * FROM Courses WHERE (alias = ?) LIMIT 1;';

        /** @var array{acl_id: int, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, name: string, needs_basic_information: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Courses($row);
    }

    final public static function getAssignmentByAlias(
        \OmegaUp\DAO\VO\Courses $course,
        string $assignmentAlias
    ): ?\OmegaUp\DAO\VO\Assignments {
        $sql = 'SELECT * FROM Assignments WHERE (alias = ? AND course_id = ?) LIMIT 1;';
        $params = [$assignmentAlias, $course->course_id];

        /** @var array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, start_time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    final public static function updateAssignmentMaxPoints(
        \OmegaUp\DAO\VO\Courses $course,
        string $assignment_alias
    ): int {
        $sql = 'UPDATE Assignments a
                JOIN (
                    SELECT assignment_id, sum(psp.points) as max_points
                    FROM Assignments a
                    INNER JOIN Problemset_Problems psp
                        ON a.problemset_id = psp.problemset_id
                    GROUP BY a.assignment_id
                ) q
                ON a.assignment_id = q.assignment_id
                SET a.max_points = q.max_points
                WHERE alias = ? AND course_id = ?;';

        $params = [$assignment_alias, $course->course_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return array{share_user_information: bool, accept_teacher: bool|null}
     */
    final public static function getSharingInformation(
        int $identityId,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ): array {
        if ($course->group_id !== $group->group_id) {
            return ['share_user_information' => false, 'accept_teacher' => null];
        }
        $sql = '
            SELECT
                gi.share_user_information,
                accept_teacher
            FROM
                Groups_Identities AS gi
            LEFT JOIN
                PrivacyStatement_Consent_Log AS pcl
            ON
                gi.privacystatement_consent_id = pcl.privacystatement_consent_id
            WHERE
                gi.identity_id = ?
                AND gi.group_id = ?
            ';
        $params = [
            $identityId,
            $group->group_id,
        ];
        /** @var array{accept_teacher: bool|null, share_user_information: bool|null}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return ['share_user_information' => false, 'accept_teacher' => null];
        }

        return [
            'share_user_information' => boolval(
                $row['share_user_information']
            ),
            'accept_teacher' => $row['accept_teacher'],
        ];
    }

    public static function countCourses(
        int $startTimestamp,
        int $endTimestamp
    ): int {
        $sql = '
            SELECT
                COUNT(c.course_id)
            FROM
                Courses c
            WHERE
                c.start_time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?);
                ';

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$startTimestamp, $endTimestamp]
        );
    }

    public static function countAttemptedIdentities(
        string $courseAlias,
        int $startTimestamp,
        int $endTimestamp
    ): int {
        $sql = '
            SELECT
                COUNT(DISTINCT s.identity_id)
            FROM
                Courses c
            INNER JOIN
                Assignments a ON a.course_id = c.course_id
            INNER JOIN
                Submissions s ON s.problemset_id = a.problemset_id
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            WHERE
                c.alias = ?
                AND r.verdict = "AC"
                AND s.time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?);
';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$courseAlias, $startTimestamp, $endTimestamp]
        );
    }

    public static function countCompletedIdentities(
        string $courseAlias,
        float $completionRate,
        int $startTimestamp,
        int $endTimestamp
    ): int {
        $sql = '
            SELECT
                COUNT(DISTINCT ip.identity_id)
            FROM
                (
                    SELECT
                        s.identity_id,
                        COUNT(DISTINCT s.problem_id) AS problems_solved
                    FROM
                        (
                            SELECT
                                pp.problemset_id,
                                pp.problem_id
                            FROM
                                Courses c
                            INNER JOIN
                                Assignments a ON a.course_id = c.course_id
                            INNER JOIN
                                Problemset_Problems pp ON pp.problemset_id = a.problemset_id
                            WHERE
                                c.alias = ?
                        ) cp
                    LEFT JOIN
                        Submissions s ON s.problemset_id = cp.problemset_id
                        AND s.problem_id = cp.problem_id
                    LEFT JOIN
                        Runs r ON r.run_id = s.current_run_id
                    WHERE
                        r.verdict = "AC"
                        AND s.time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)
                    GROUP BY s.identity_id
                    HAVING
                        problems_solved >= (
                            SELECT
                                COUNT(DISTINCT pp.problem_id)
                            FROM
                                Courses c
                            INNER JOIN
                                Assignments a ON a.course_id = c.course_id
                            INNER JOIN
                                Problemset_Problems pp ON pp.problemset_id = a.problemset_id
                            INNER JOIN
                                Problems p ON p.problem_id = pp.problem_id
                            WHERE
                                c.alias = ? AND p.languages != ""
                        ) * ?
                ) AS ip;
';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$courseAlias, $startTimestamp, $endTimestamp, $courseAlias, $completionRate]
        );
    }

    /**
     * @return array{classname: string, username: string}|null
     */
    public static function getCreatorInformation(
        \OmegaUp\DAO\VO\Courses $course
    ): ?array {
        $sql = 'SELECT
                    i.username,
                    IFNULL(
                        (
                            SELECT urc.classname FROM
                                User_Rank_Cutoffs urc
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
                            LIMIT
                                1
                        ),
                        \'user-rank-unranked\'
                    ) AS classname
                FROM
                    Users u
                INNER JOIN
                    ACLs a
                ON
                    u.user_id = a.owner_id
                INNER JOIN
                    Identities i
                ON
                    u.main_identity_id = i.identity_id
                WHERE
                    a.acl_id = ?
                LIMIT
                    1;';
        /** @var array{classname: string, username: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$course->acl_id]
        );
    }

    /**
     * @return list<array{alias: null|string, classname: string, event_type: string, ip: int, time: \OmegaUp\Timestamp, username: string}>
     */
    public static function getActivityReport(\OmegaUp\DAO\VO\Courses $course) {
        $sql = '(
            SELECT
                i.username,
                NULL AS alias,
                pal.ip,
                pal.`time`,
                IFNULL(
                    (
                        SELECT `urc`.classname FROM
                            `User_Rank_Cutoffs` urc
                        WHERE
                            `urc`.score <= (
                                    SELECT
                                        `ur`.`score`
                                    FROM
                                        `User_Rank` `ur`
                                    WHERE
                                        `ur`.user_id = `i`.`user_id`
                                )
                        ORDER BY
                            `urc`.percentile ASC
                        LIMIT
                            1
                    ),
                    "user-rank-unranked"
                ) `classname`,
                "open" AS event_type
            FROM
                Problemset_Access_Log pal
            INNER JOIN
                Identities i
            ON
                i.identity_id = pal.identity_id
            INNER JOIN
                Assignments a
            ON
                a.problemset_id = pal.problemset_id
            WHERE
                a.course_id = ?
        ) UNION (
            SELECT
                i.username,
                p.alias,
                sl.ip,
                sl.`time`,
                IFNULL(
                    (
                        SELECT `urc`.classname FROM
                            `User_Rank_Cutoffs` urc
                        WHERE
                            `urc`.score <= (
                                    SELECT
                                        `ur`.`score`
                                    FROM
                                        `User_Rank` `ur`
                                    WHERE
                                        `ur`.user_id = `i`.`user_id`
                                )
                        ORDER BY
                            `urc`.percentile ASC
                        LIMIT
                            1
                    ),
                    "user-rank-unranked"
                ) `classname`,
                "submit" AS event_type
            FROM
                Submission_Log sl
            INNER JOIN
                Identities i
            ON
                i.identity_id = sl.identity_id
            INNER JOIN
                Submissions s
            ON
                s.submission_id = sl.submission_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = s.problem_id
            INNER JOIN
                Assignments a
            ON
                a.problemset_id = sl.problemset_id
            WHERE
                a.course_id = ?
        ) ORDER BY time;';
        /** @var list<array{alias: null|string, classname: string, event_type: string, ip: int, time: \OmegaUp\Timestamp, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$course->course_id, $course->course_id]
        );
    }
}
