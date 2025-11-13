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
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, max_points: float, name: string, opened: bool, order: int, problemCount: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 * @psalm-type FilteredCourse=array{accept_teacher: bool|null, admission_mode: string, alias: string, assignments: list<CourseAssignment>, description: string, counts: array<string, int>, finish_time: \OmegaUp\Timestamp|null, is_open: bool, name: string, progress?: float, school_name: null|string, start_time: \OmegaUp\Timestamp}
 * @psalm-type CourseCardEnrolled=array{alias: string, name: string, progress: float, school_name: null|string}
 * @psalm-type CourseCardFinished=array{alias: string, name: string}
 * @psalm-type CourseCardPublic=array{alias: string, alreadyStarted: bool, lessonCount: int, level: null|string, name: string, school_name: null|string, studentCount: int}
 * @psalm-type AssignmentsProblemsPoints=array{alias: string, extraPoints: float, name: string, points: float, problems: list<array{alias: string, title: string, isExtraProblem: bool, order: int, points: float}>, order: int}
 * @psalm-type StudentProgressInCourse=array{assignments: array<string, array{problems: array<string, array{progress: float, score: float}>, progress: float, score: float}>, classname: string, country_id: null|string, courseProgress: float, courseScore: float, name: null|string, username: string}
 * @psalm-type Course=array{acl_id?: int, admission_mode: string, alias: string, archived: bool, course_id: int, description: string, finish_time?: \OmegaUp\Timestamp|null, group_id?: int, languages?: null|string, level?: null|string, minimum_progress_for_certificate?: int|null, name: string, needs_basic_information: bool, objective?: null|string, requests_user_information: string, school_id?: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}
 */
class Courses extends \OmegaUp\DAO\Base\Courses {
    /**
     * @return list<\OmegaUp\DAO\VO\Courses>
     */
    public static function findByName(string $name): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Courses::FIELD_NAMES,
            'c'
        );
        $sql = "SELECT DISTINCT
                {$fields}
                FROM Courses c
                WHERE c.name
                LIKE CONCAT('%', ?, '%') LIMIT 10";

        /** @var list<array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificates_status: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, languages: null|string, level: null|string, minimum_progress_for_certificate: int|null, name: string, needs_basic_information: bool, objective: null|string, recommended: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, teaching_assistant_enabled: bool}> */
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
        bool $isAdmin,
        ?\OmegaUp\DAO\VO\Identities $identity = null
    ): array {
        // Non-admins should not be able to see assignments that have not
        // started.
        $timeCondition = $isAdmin ? '' : 'AND a.start_time <= CURRENT_TIMESTAMP';

        $openedCondition = 'false AS opened';
        $args = [];
        if (!is_null($identity)) {
            $openedCondition = '
                EXISTS(
                    SELECT
                        1
                    FROM
                        `Problemset_Problem_Opened` AS ppo
                    WHERE
                        ppo.`problemset_id` = a.`problemset_id`
                        AND ppo.`identity_id` = ?
                ) AS opened
            ';
            $args[] = $identity->identity_id;
        }

        $fields =  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Assignments::FIELD_NAMES,
            'a'
        );

        $sql = "
            SELECT
                {$fields},
                EXISTS(SELECT * FROM Submissions s WHERE s.problemset_id = a.problemset_id) AS has_runs,
                COUNT(psp.problem_id) AS problem_count,
                ps.scoreboard_url,
                ps.scoreboard_url_admin,
                $openedCondition
            FROM
                Courses c
            INNER JOIN
                Assignments a
            ON
                a.course_id = c.course_id
            INNER JOIN
                Problemsets ps
            ON
                ps.problemset_id = a.problemset_id
            LEFT JOIN
                Problemset_Problems psp
            ON
                psp.problemset_id = ps.problemset_id
            WHERE
                c.alias = ? $timeCondition
            GROUP BY
                a.assignment_id, ps.problemset_id, psp.problemset_id
            ORDER BY
                a.`order`, a.start_time;";
        $args[] = $alias;

        /** @var list<array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: int, max_points: float, name: string, opened: int, order: int, problem_count: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $args
        );

        $ar = [];
        foreach ($rs as $row) {
            unset($row['acl_id']);
            unset($row['assignment_id']);
            unset($row['course_id']);
            $row['has_runs'] = $row['has_runs'] > 0;
            $row['problemCount'] = $row['problem_count'];
            $row['opened'] = boolval($row['opened']);
            unset($row['problem_count']);
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
                    c.description,
                    finish_time,
                    c.name AS name,
                    s.name AS school_name,
                    start_time,
                    accept_teacher,
                    IFNULL(pr.progress, 0.0) AS progress,
                    pr.last_submission_time
                FROM Courses c
                INNER JOIN (
                    SELECT gi.group_id, gi.accept_teacher
                    FROM Groups_Identities gi
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
                WHERE
                    c.archived = 0
                ORDER BY
                    pr.last_submission_time DESC;';
        /** @var list<array{accept_teacher: bool|null, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, last_submission_time: \OmegaUp\Timestamp|null, name: string, progress: float, school_name: null|string, start_time: \OmegaUp\Timestamp}> */
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
                description,
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
            WHERE
                c.admission_mode = ?
                AND c.archived = 0;';

        /** @var list<array{accept_teacher: int|null, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, is_open: int, name: string, progress: float, school_name: null|string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC]
        );
        $courses = [];
        foreach ($rs as $row) {
            $row['assignments'] = \OmegaUp\DAO\Courses::getAllAssignments(
                $row['alias'],
                isAdmin: false
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
     * @return list<CourseCardPublic>
     */
    public static function getPublicCoursesForTab(): array {
        $sql = 'SELECT
                    c.alias,
                    c.name,
                    c.level,
                    s.name AS school_name,
                    COALESCE(student_count.total, 0) AS studentCount,
                    COALESCE(lesson_count.total, 0) AS lessonCount,
                    0 AS alreadyStarted
                FROM
                    Courses c
                LEFT JOIN
                    Schools s ON c.school_id = s.school_id
                LEFT JOIN (
                    SELECT group_id, COUNT(*) AS total
                    FROM Groups_Identities
                    GROUP BY group_id
                ) student_count ON student_count.group_id = c.group_id
                LEFT JOIN (
                    SELECT course_id, assignment_type, COUNT(*) AS total
                    FROM Assignments
                    WHERE assignment_type = ?
                    GROUP BY course_id
                ) lesson_count ON lesson_count.course_id = c.course_id
                WHERE
                    c.admission_mode = ? AND
                    c.recommended = ? AND
                    c.archived = ?;';

        /** @var list<array{alias: string, alreadyStarted: int, lessonCount: int, level: null|string, name: string, school_name: null|string, studentCount: int}> */
        $rs =  \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                \OmegaUp\Controllers\Course::ASSIGNMENT_TYPE_LESSON,
                \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
                /** recommended= */ 1,
                /** archived= */ 0
            ]
        );

        return array_map(function ($row) {
            $row['alreadyStarted'] = boolval($row['alreadyStarted']);
            return $row;
        }, $rs);
    }

    /**
     * @return array{enrolled: list<CourseCardEnrolled>, finished: list<CourseCardFinished>}
     */
    public static function getEnrolledAndFinishedCoursesForTabs(
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $sql = 'SELECT
                    c.alias,
                    c.name,
                    s.name as school_name,
                    IFNULL(pr.progress, 0.0) AS progress
                FROM
                    Courses c
                LEFT JOIN
                    Schools s ON c.school_id = s.school_id
                LEFT JOIN (
                    -- we want a score even if there are no submissions yet
                    -- and that score should not be greater than 100%
                    SELECT
                        cbpr.course_id,
                        LEAST(100, ROUND(SUM(cbpr.total_assignment_score) / SUM(cbpr.max_points) * 100, 2)) AS progress
                    FROM (
                        -- Aggregate all problem scores of an assignment.
                        SELECT
                            a.alias,
                            a.course_id,
                            a.assignment_id,
                            IFNULL(
                                -- Get the best score of each problem.
                                (
                                    SELECT
                                        SUM(ps.contest_score)
                                    FROM (
                                        SELECT
                                            psp.problem_id,
                                            MAX(r.contest_score) AS contest_score
                                        FROM
                                            Problemset_Problems psp
                                        INNER JOIN
                                            Submissions s ON s.problem_id = psp.problem_id AND s.problemset_id = psp.problemset_id
                                        INNER JOIN
                                            Runs r ON r.run_id = s.current_run_id
                                        WHERE
                                            psp.problemset_id = a.problemset_id AND
                                            s.identity_id = ?
                                        GROUP BY
                                            psp.problem_id
                                    ) AS ps
                                ),
                                0.0
                            ) AS total_assignment_score,
                            a.max_points
                        FROM
                            Assignments a
                        WHERE
                            EXISTS (
                                SELECT
                                    *
                                FROM
                                    Problemset_Problems psp
                                INNER JOIN
                                    Submissions s ON s.problemset_id = psp.problemset_id AND s.problem_id = psp.problem_id
                                WHERE
                                    psp.problemset_id = a.problemset_id AND
                                    s.identity_id = ?
                            )
                        GROUP BY
                            a.assignment_id
                    ) cbpr
                    GROUP BY cbpr.course_id
                ) pr ON c.course_id = pr.course_id
                WHERE
                    c.archived = 0 AND
                    EXISTS (
                        SELECT
                            *
                        FROM
                            Groups_Identities gi
                        WHERE
                            gi.group_id = c.group_id AND
                            gi.identity_id = ?
                    )
                ORDER BY
                    c.name ASC, c.finish_time DESC;';

        $courses = [
            'enrolled' => [],
            'finished' => [],
        ];

        /** @var array{alias: string, name: string, progress: float, school_name: null|string} */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [
                    $identity->identity_id,
                    $identity->identity_id,
                    $identity->identity_id,
                ]
            ) as $row
        ) {
            // TODO: Define the exact percentage to consider the course as finished.
            if ($row['progress'] == 100) {
                unset($row['progress']);
                unset($row['school_name']);
                $courses['finished'][] = $row;
                continue;
            }
            $courses['enrolled'][] = $row;
        }

        return $courses;
    }

    /**
     * Returns the list of students in a course
     *
     * @return list<array{name: null|string, user_id: int|null, username: string}>
     */
    public static function getStudentsInCourse(
        int $courseId,
        int $groupId
    ): array {
        $sql = '
            SELECT
                i.username,
                i.name,
                i.user_id
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?';

        /** @var list<array{name: null|string, user_id: int|null, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$groupId]
        );
    }

    /**
     * Returns a list of students within a course with their score and progress
     * by problem
     * @return array{allProgress: list<array{classname: string, country_id: null|string, name: null|string, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string}>, problemTitles: array<string, string>}
     */
    public static function getStudentsInCourseWithProgressPerAssignment(
        int $courseId,
        int $groupId
    ): array {
        $sql = 'SELECT
                    i.username,
                    i.name,
                    i.country_id,
                    pr.assignment_alias,
                    pr.problem_alias,
                    pr.problem_title,
                    problem_points,
                    MAX(r.contest_score) AS problem_score,
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Groups_Identities AS gi
                CROSS JOIN
                    (
                        SELECT
                            a.assignment_id,
                            a.alias AS assignment_alias,
                            a.problemset_id,
                            p.problem_id,
                            p.title AS problem_title,
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
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
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

        /** @var list<array{assignment_alias: string, classname: string, country_id: null|string, name: null|string, problem_alias: string, problem_points: float, problem_score: float|null, problem_title: string, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId, $groupId]
        );

        $allProgress = [];
        $problemTitles = [];
        foreach ($rs as $row) {
            $username = $row['username'];
            if (!isset($allProgress[$username])) {
                $allProgress[$username] = [
                    'classname' => $row['classname'],
                    'country_id' => $row['country_id'],
                    'name' => $row['name'],
                    'progress' => [],
                    'points' => [],
                    'score' => [],
                    'username' => $username,
                ];
            }

            $assignmentAlias = $row['assignment_alias'];
            $problemAlias = $row['problem_alias'];

            if (!isset($problemTitles[$problemAlias])) {
                $problemTitles[$problemAlias] =  $row['problem_title'];
            }

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
             * @param array{classname: string, country_id: null|string, name: string|null, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string} $a
             * @param array{classname: string, country_id: null|string, name: string|null, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string} $b
             */
            fn (array $a, array $b) => strcasecmp(
                !empty($a['name']) ? $a['name'] : $a['username'],
                !empty($b['name']) ? $b['name'] : $b['username']
            )
        );
        return [
            'allProgress' => $allProgress,
            'problemTitles' => $problemTitles,
        ];
    }

    /**
     * Returns the list of assignments with their problems and points.
     *
     * @return array{assignmentsProblems: list<AssignmentsProblemsPoints>, studentsProgress: list<StudentProgressInCourse>, totalRows: int}
     */
    public static function getStudentsProgressPerAssignment(
        int $courseId,
        int $groupId,
        int $page,
        int $rowsPerPage
    ): array {
        $sqlAssignmentsProblems = '
            SELECT
                a.alias AS assignment_alias,
                a.name AS assignment_name,
                a.`order` AS assignment_order,
                p.title AS problem_title,
                p.alias AS problem_alias,
                psp.is_extra_problem,
                psp.points AS problem_points,
                psp.`order` AS problem_order
            FROM
                Assignments a
            INNER JOIN
                Problemset_Problems psp ON psp.problemset_id = a.problemset_id
            INNER JOIN
                Problems p ON p.problem_id = psp.problem_id
            WHERE
                a.course_id = ? AND a.assignment_type <> "lesson"
            ORDER BY
                a.`order`, psp.`order`';

        $coursePoints = 0.0;
        $assignmentsProblems = [];
        /** @var list<array{assignment_alias: string, assignment_name: string, assignment_order: int, is_extra_problem: bool, problem_alias: string, problem_order: int, problem_points: float, problem_title: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sqlAssignmentsProblems,
            [ $courseId ]
        );
        foreach ($rs as $row) {
            if (!isset($assignmentsProblems[$row['assignment_alias']])) {
                $assignmentsProblems[$row['assignment_alias']] = [
                    'alias' => $row['assignment_alias'],
                    'name' => $row['assignment_name'],
                    'points' => 0.0,
                    'extraPoints' => 0.0,
                    'problems' => [],
                    'order' => $row['assignment_order'],
                ];
            }

            if (!$row['is_extra_problem']) {
                $assignmentsProblems[$row['assignment_alias']]['points'] += $row['problem_points'];
                $coursePoints += $row['problem_points'];
            } else {
                $assignmentsProblems[$row['assignment_alias']]['extraPoints'] += $row['problem_points'];
            }

            $assignmentsProblems[$row['assignment_alias']]['problems'][$row['problem_alias']] = [
                'alias' => $row['problem_alias'],
                'title' => $row['problem_title'],
                'isExtraProblem' => $row['is_extra_problem'],
                'points' => $row['problem_points'],
                'order' => $row['problem_order'],
            ];
        }

        // Gets the total count of students in the course.
        $sqlCount = '
            SELECT
                COUNT(*)
            FROM
                Groups_Identities AS gi
            WHERE
                gi.group_id = ?';
        /** @var int */
        $totalStudents = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount,
            [ $groupId, ]
        ) ?? 0;

        // Gets the students in a course between a certain range
        $sqlUsers = '
            SELECT
                i.username,
                i.name,
                i.country_id,
                IFNULL(ur.classname, "user-rank-unranked") AS classname
            FROM
                Groups_Identities AS gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            WHERE
                gi.group_id = ?
            LIMIT ?, ?';

        /** @var list<array{classname: string, country_id: null|string, name: null|string, username: string}> */
        $courseUsers = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sqlUsers,
            [
                $groupId,
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
            ]
        );

        // Gets on each row:
        // - the students with their information;
        // - the problem they solved;
        // - the alias of the assignment of the problem;
        // - the max score the users got in the submission for the problem.
        $sqlStudentsProgress = '
            SELECT
                students.username,
                students.name,
                students.country_id,
                students.classname,
                a.alias AS assignment_alias,
                p.alias AS problem_alias,
                IFNULL(
                    MAX(r.contest_score),
                    0.0
                ) AS problem_score,
                psp.is_extra_problem
            FROM
                (
                    SELECT
                        i.identity_id,
                        i.username,
                        i.name,
                        i.country_id,
                        IFNULL(ur.classname, "user-rank-unranked") AS classname
                    FROM
                        Groups_Identities AS gi
                    INNER JOIN
                        Identities i ON i.identity_id = gi.identity_id
                    LEFT JOIN
                        User_Rank ur ON ur.user_id = i.user_id
                    WHERE
                        gi.group_id = ?
                    LIMIT ?, ?
                ) AS students
            INNER JOIN
                Submissions s ON s.identity_id = students.identity_id
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            INNER JOIN
                Problemset_Problems psp ON psp.problemset_id = s.problemset_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id AND p.problem_id = psp.problem_id
            INNER JOIN
                Assignments a ON a.problemset_id = s.problemset_id
            WHERE
                a.course_id = ? AND a.assignment_type <> "lesson"
            GROUP BY
                students.identity_id, a.assignment_id, p.problem_id
            HAVING
                MAX(r.contest_score) IS NOT NULL
            ORDER BY
                students.name, a.`order`, psp.`order`
        ';

        $studentsProgress = [];
        /** @var list<array{assignment_alias: string, classname: string, country_id: null|string, is_extra_problem: bool, name: null|string, problem_alias: string, problem_score: float, username: string}> */
        $rs  = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sqlStudentsProgress,
            [
                $groupId,
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
                $courseId,
            ]
        );
        foreach ($rs as $row) {
            $username = $row['username'];
            $assignmentAlias = $row['assignment_alias'];
            $problemAlias = $row['problem_alias'];
            $problemScore = $row['problem_score'];

            if (!isset($studentsProgress[$username])) {
                $studentsProgress[$username] = [
                    'username' => $username,
                    'name' => $row['name'],
                    'country_id' => $row['country_id'],
                    'classname' => $row['classname'],
                    'courseScore' => 0.0,
                    'courseProgress' => 0.0,
                    'assignments' => [],
                ];
            }

            // Course score considers every problem in the course, including the extra problems.
            $studentsProgress[$username]['courseScore'] += $problemScore;
            $studentsProgress[$username]['courseProgress'] += $coursePoints !== 0.0 ? $problemScore / $coursePoints * 100 : 0.0;
            // Ensure always to not surpass 100%
            $studentsProgress[$username]['courseProgress'] = min(
                100,
                $studentsProgress[$username]['courseProgress']
            );

            if (
                !isset(
                    $studentsProgress[$username]['assignments'][$assignmentAlias]
                )
            ) {
                $studentsProgress[$username]['assignments'][$assignmentAlias] = [
                    'score' => 0.0,
                    'progress' => 0.0,
                    'problems' => [],
                ];
            }

            // Assignment score considers the extra problems.
            $studentsProgress[$username]['assignments'][$assignmentAlias]['score'] += $problemScore;
            $studentsProgress[$username]['assignments'][$assignmentAlias]['progress'] += (
                $assignmentsProblems[$assignmentAlias]['points'] !== 0.0 ? (
                    $problemScore / $assignmentsProblems[$assignmentAlias]['points'] * 100
                 ) : 0.0
            );

            $studentsProgress[$username]['assignments'][$assignmentAlias]['problems'][$problemAlias] = [
                'score' => $problemScore,
                'progress' => $assignmentsProblems[$assignmentAlias]['problems'][$problemAlias]['points'] !== 0.0 ? $problemScore / $assignmentsProblems[$assignmentAlias]['problems'][$problemAlias]['points'] * 100 : 0.0,
            ];
        }

        foreach ($courseUsers as $user) {
            if (isset($studentsProgress[$user['username']])) {
                continue;
            }
            $studentsProgress[$user['username']] = [
                'username' => $user['username'],
                'name' => $user['name'],
                'country_id' => $user['country_id'],
                'classname' => $user['classname'],
                'courseScore' => 0.0,
                'courseProgress' => 0.0,
                'assignments' => [],
            ];
        }

        /** @var array<string, array{alias: string, extraPoints: float, name: string, points: float, problems: list<array{alias: string, title: string, isExtraProblem: bool, order: int, points: float}>, order: int}> */
        $assignmentsProblems = array_map(
            function (array $assignmentProblems) {
                usort(
                    $assignmentProblems['problems'],
                    /**
                     * @param array{alias: string, title: string, isExtraProblem: bool, order: int, points: float} $a
                     * @param array{alias: string, title: string, isExtraProblem: bool, order: int, points: float} $b
                     */
                    fn (array $a, array $b) => $a['order'] - $b['order']
                );
                return $assignmentProblems;
            },
            $assignmentsProblems
        );

        usort(
            $assignmentsProblems,
            /**
             * @param array{alias: string, extraPoints: float, name: string, points: float, problems: list<array{alias: string, title: string, isExtraProblem: bool, order: int, points: float}>, order: int} $a
             * @param array{alias: string, extraPoints: float, name: string, points: float, problems: list<array{alias: string, title: string, isExtraProblem: bool, order: int, points: float}>, order: int} $b
             */
            fn (array $a, array $b) => $a['order'] - $b['order']
        );

        usort(
            $studentsProgress,
            /**
             * @param array{assignments: array<string, array{problems: array<string, array{progress: float, score: float}>, progress: float, score: float}>, classname: string, country_id: null|string, courseProgress: float, courseScore: float, name: null|string, username: string} $a
             * @param array{assignments: array<string, array{problems: array<string, array{progress: float, score: float}>, progress: float, score: float}>, classname: string, country_id: null|string, courseProgress: float, courseScore: float, name: null|string, username: string} $b
             */
            fn (array $a, array $b) => strcasecmp(
                !is_null($a['name']) ? $a['name'] : $a['username'],
                !is_null($b['name']) ? $b['name'] : $b['username']
            )
        );

        return [
            'assignmentsProblems' => $assignmentsProblems,
            'studentsProgress' => $studentsProgress,
            'totalRows' => $totalStudents,
        ];
    }

    /**
     * Returns the score per assignment of a user, as well as the maximum score
     * attainable
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
            WHERE a.course_id = ?;
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
     * @return array{admin: list<FilteredCourse>, teachingAssistant: list<FilteredCourse>}
     */
    final public static function getAllCoursesAdminedByIdentity(
        int $identityId,
        int $page = 1,
        int $pageSize = 1000
    ): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Courses::FIELD_NAMES,
            'c'
        );

        $adminRole = \OmegaUp\Authorization::ADMIN_ROLE;
        $taRole = \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE;

        $sql = "SELECT
                {$fields},
                MAX(
                    CASE
                        WHEN ur.role_id = ? OR gr.role_id = ? THEN ?
                        WHEN ur.role_id = ? OR gr.role_id = ? THEN ?
                        ELSE 0
                    END
                ) AS highest_role
            FROM
                Courses AS c
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = c.acl_id
            LEFT JOIN
                Identities uri ON ur.user_id = uri.user_id AND uri.identity_id = ?
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = c.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id AND gi.identity_id = ?
            WHERE
                c.archived = 0 AND (
                    ai.identity_id = ? OR
                    (ur.role_id IN (?, ?) AND uri.identity_id IS NOT NULL) OR
                    (gr.role_id IN (?, ?) AND gi.identity_id IS NOT NULL)
                )
            GROUP BY
                c.course_id
            ORDER BY
                c.course_id DESC
            LIMIT
                ?, ?";

        $params = [
        $adminRole, $adminRole, $adminRole,
        $taRole, $taRole, $taRole,
        $identityId,
        $identityId,
        $identityId,
        $adminRole, $taRole,
        $adminRole, $taRole,
        max(0, $page - 1) * $pageSize,
        $pageSize,
        ];

        /** @var list<array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificates_status: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, languages: null|string, level: null|string, minimum_progress_for_certificate: int|null, name: string, needs_basic_information: bool, objective: null|string, recommended: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, highest_role: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = ['admin' => [], 'teachingAssistant' => []];

        foreach ($rs as $row) {
            $course = \OmegaUp\Controllers\Course::convertCourseToArray(
                new \OmegaUp\DAO\VO\Courses([
                    'acl_id' => $row['acl_id'],
                    'admission_mode' => $row['admission_mode'],
                    'alias' => $row['alias'],
                    'archived' => $row['archived'],
                    'course_id' => $row['course_id'],
                    'description' => $row['description'],
                    'finish_time' => $row['finish_time'],
                    'group_id' => $row['group_id'],
                    'languages' => $row['languages'],
                    'level' => $row['level'],
                    'minimum_progress_for_certificate' => $row['minimum_progress_for_certificate'],
                    'name' => $row['name'],
                    'needs_basic_information' => $row['needs_basic_information'],
                    'objective' => $row['objective'],
                    'requests_user_information' => $row['requests_user_information'],
                    'school_id' => $row['school_id'],
                    'show_scoreboard' => $row['show_scoreboard'],
                    'start_time' => $row['start_time'],
                ])
            );

            if ($row['highest_role'] === $taRole) {
                $courses['teachingAssistant'][] = $course;
            } else {
                $courses['admin'][] = $course;
            }
        }

        return $courses;
    }

    /**
     * Returns all archived courses that an identity can manage.
     *
     * @return list<FilteredCourse>
     */
    final public static function getArchivedCoursesAdminedByIdentity(
        int $identityId
    ): array {
        $sql = '
            SELECT
                c.course_id,
                c.admission_mode,
                c.alias,
                c.description,
                c.finish_time,
                c.name AS name,
                s.name AS school_name,
                c.start_time,
                0.0 AS progress,
                0 AS is_open,
                CAST(NULL AS UNSIGNED) AS accept_teacher
            FROM
                Courses AS c
            INNER JOIN
                ACLs AS a ON a.acl_id = c.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                Schools AS s ON s.school_id = c.school_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = c.acl_id
            LEFT JOIN
                Identities uri ON ur.user_id = uri.identity_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = c.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                c.archived = 1 AND (
                    ai.identity_id = ? OR
                    (ur.role_id = ? AND uri.identity_id = ?) OR
                    (gr.role_id = ? AND gi.identity_id = ?)
                )
            GROUP BY
                c.course_id
            ORDER BY
                c.course_id DESC;';

        $params = [
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
        ];
        /** @var list<array{accept_teacher: int|null, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, is_open: int, name: string, progress: float, school_name: null|string, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            $row['assignments'] = \OmegaUp\DAO\Courses::getAllAssignments(
                $row['alias'],
                isAdmin: true
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
     * Returns all courses owned by a user.
     * @return \OmegaUp\DAO\VO\Courses[]
     */
    final public static function getAllCoursesOwnedByUser(
        int $userId,
        int $page = 1,
        int $pageSize = 1000
    ): array {
        $sql = '
            SELECT
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Courses::FIELD_NAMES,
            'c'
        ) . '
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
            max(0, $page - 1) * $pageSize,
            $pageSize,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new \OmegaUp\DAO\VO\Courses($row));
        }
        return $courses;
    }

    /**
     * Returns the list of courses created by a certain identity
     * @return list<Course>
     */
    final public static function getCoursesCreatedByIdentity(
        int $identityId
    ): array {
        $sql = '
            SELECT
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Courses::FIELD_NAMES,
            'c'
        ) . '
            FROM
                Courses c
            INNER JOIN
                ACLs a ON a.acl_id = c.acl_id
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            WHERE
                u.main_identity_id = ?
                AND archived = false
            ORDER BY
                c.course_id DESC;';

        $params = [
            $identityId,
        ];

        /** @var list<array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificates_status: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, languages: null|string, level: null|string, minimum_progress_for_certificate: int|null, name: string, needs_basic_information: bool, objective: null|string, recommended: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, teaching_assistant_enabled: bool}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    final public static function getByProblemsetId(
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): ?\OmegaUp\DAO\VO\Courses {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Courses::FIELD_NAMES,
            'c'
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    Courses c
                INNER JOIN
                    Assignments a
                ON
                    c.course_id = a.course_id
                WHERE
                    a.problemset_id = ?
                LIMIT 1;";

        /** @var array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificates_status: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, languages: null|string, level: null|string, minimum_progress_for_certificate: int|null, name: string, needs_basic_information: bool, objective: null|string, recommended: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, teaching_assistant_enabled: bool}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemset->problemset_id]
        );
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Courses($row);
    }

    final public static function getByAlias(
        string $alias
    ): ?\OmegaUp\DAO\VO\Courses {
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Courses::FIELD_NAMES,
            'Courses'
        ) . ' FROM Courses WHERE (alias = ?) LIMIT 1;';

        /** @var array{acl_id: int, admission_mode: string, alias: string, archived: bool, certificates_status: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, languages: null|string, level: null|string, minimum_progress_for_certificate: int|null, name: string, needs_basic_information: bool, objective: null|string, recommended: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, teaching_assistant_enabled: bool}|null */
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

    final public static function updateLanguagesToAssignments(
        \OmegaUp\DAO\VO\Courses $course,
        string $languages
    ): int {
        $sql = 'UPDATE
                    Problemsets ps
                INNER JOIN
                    Assignments a
                ON
                    a.assignment_id = ps.assignment_id
                SET
                    ps.languages = ?
                WHERE
                    a.course_id = ?;';

        $params = [$languages, $course->course_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
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
            WHERE
                c.alias = ?
                AND s.verdict = "AC"
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
                    WHERE
                        s.verdict = "AC"
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
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Users u
                INNER JOIN
                    ACLs a ON u.user_id = a.owner_id
                INNER JOIN
                    Identities i ON u.main_identity_id = i.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
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
     * @return array{activity: list<array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int|null, name: null| string, time: \OmegaUp\Timestamp, username: string}>, totalRows: int}
     */
    public static function getActivityReport(
        \OmegaUp\DAO\VO\Courses $course,
        int $page,
        int $rowsPerPage
    ): array {
        $sql = 'WITH course_assignments AS (
                    SELECT assignment_id, problemset_id
                    FROM Assignments
                    WHERE course_id = ?
                )
                (
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
                course_assignments a
            ON
                a.problemset_id = pal.problemset_id
            INNER JOIN
                Identities i
            ON
                i.identity_id = pal.identity_id
            LEFT JOIN
                User_Rank ur
            ON
                ur.user_id = i.user_id
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
                course_assignments a ON a.problemset_id = sl.problemset_id
            INNER JOIN
                Identities i ON i.identity_id = sl.identity_id
            INNER JOIN
                Submissions s ON s.submission_id = sl.submission_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
        ) UNION ALL (
            SELECT
                i.username,
                c.alias,
                INET_ATON(ccl.ip) AS `ip`,
                ccl.`timestamp` AS `time`,
                IFNULL(ur.classname, "user-rank-unranked") AS classname,
                "clone" AS event_type,
                ccl.result AS clone_result,
                ccl.token_payload AS clone_token_payload,
                c.name
            FROM
                Course_Clone_Log ccl
            INNER JOIN
                Users u ON u.user_id = ccl.user_id
            INNER JOIN
                Identities i ON i.identity_id = u.main_identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            LEFT JOIN
                Courses c ON c.course_id = ccl.new_course_id
            WHERE
                ccl.course_id = ?
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
            [$course->course_id, $course->course_id]
        );

        /** @var list<array{alias: null|string, classname: string, clone_result: null|string, clone_token_payload: null|string, event_type: string, ip: int|null, name: null|string, time: \OmegaUp\Timestamp, username: string}> */
        $activity = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql . $sqlOrder . $sqlLimit,
            [
                $course->course_id,
                $course->course_id,
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
            ]
        );

        return [
            'activity' => $activity,
            'totalRows' => $totalRows,
        ];
    }
    /**
     * @return list<\OmegaUp\DAO\VO\Groups>
     */
    public static function getCourseTeachingAssistantGroups(
        \OmegaUp\DAO\VO\Courses $course
    ) {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Groups::FIELD_NAMES,
            'g'
        );
        $sql = "
            SELECT
                {$fields}
            FROM
                Group_Roles gr
            INNER JOIN
                `Groups_` AS g ON g.group_id = gr.group_id
            WHERE
                gr.role_id = ? AND gr.acl_id IN (?);";
        $params = [
            \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE,
            $course->acl_id,
        ];

        /** @var list<array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, name: string}> */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
        $groups = [];
        foreach ($row as $group) {
            $groups[] = new \OmegaUp\DAO\VO\Groups($group);
        }
        return $groups;
    }
}
