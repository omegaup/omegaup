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
     * @return list<\OmegaUp\DAO\VO\Courses>
     */
    public static function getCoursesForStudent(int $identityId) {
        $sql = 'SELECT c.*
                FROM Courses c
                INNER JOIN (
                    SELECT g.group_id
                    FROM Groups_Identities gi
                    INNER JOIN `Groups_` AS g ON g.group_id = gi.group_id
                    WHERE gi.identity_id = ?
                ) gg
                ON c.group_id = gg.group_id;
               ';
        /** @var list<array{acl_id: int, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, name: string, needs_basic_information: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identityId]
        );
        $courses = [];
        foreach ($rs as $row) {
            $courses[] = new \OmegaUp\DAO\VO\Courses($row);
        }
        return $courses;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Courses>
     */
    public static function getPublicCourses() {
        $sql = '
                SELECT cc.*
                FROM Courses cc
                WHERE cc.admission_mode = ?;
               ';
        /** @var list<array{acl_id: int, admission_mode: string, alias: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, group_id: int, name: string, needs_basic_information: bool, requests_user_information: string, school_id: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC]
        );
        $courses = [];
        foreach ($rs as $row) {
            $courses[] = new \OmegaUp\DAO\VO\Courses($row);
        }
        return $courses;
    }

    //FIXME: Use type list<StudentProgress> instead
    /**
     * Returns a list of students within a course
     * @return list<array{name: string|null, progress: array<string, array<string, float>>, username: string}>
     */
    public static function getStudentsInCourseWithProgressPerAssignment(
        int $courseId,
        int $groupId
    ): array {
        $sql = 'SELECT
                    i.username,
                    i.name,
                    pr.alias as assignment_alias,
                    pr.problem_alias,
                    pr.best_score_of_problem as problem_score
                FROM
                    `Groups_` AS g
                INNER JOIN Groups_Identities gi
                    ON g.group_id = ? AND g.group_id = gi.group_id
                INNER JOIN Identities i
                    ON i.identity_id = gi.identity_id
                LEFT JOIN (
                    SELECT
                        bpr.alias,
                        bpr.identity_id,
                        bpr.problem_alias,
                        bpr.best_score_of_problem
                    FROM (
                        SELECT
                            a.alias,
                            a.assignment_id,
                            p.problem_id,
                            p.alias as problem_alias,
                            s.identity_id,
                            MAX(r.contest_score) as best_score_of_problem
                        FROM Assignments a
                        INNER JOIN Problemsets ps
                            ON a.problemset_id = ps.problemset_id
                        INNER JOIN Problemset_Problems psp
                            ON psp.problemset_id = ps.problemset_id
                        INNER JOIN Problems p
                            ON p.problem_id = psp.problem_id
                        INNER JOIN Submissions s
                            ON s.problem_id = p.problem_id
                            AND s.problemset_id = a.problemset_id
                        INNER JOIN Runs r
                            ON r.run_id = s.current_run_id
                        WHERE a.course_id = ?
                        GROUP BY a.assignment_id, p.problem_id, s.identity_id
                        ORDER BY p.alias
                    ) bpr
                    GROUP BY bpr.assignment_id, bpr.problem_id, bpr.identity_id
                ) pr
                ON pr.identity_id = i.identity_id';

        /** @var list<array{assignment_alias: null|string, name: null|string, problem_alias: null|string, problem_score: float|null, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$groupId, $courseId]
        );

        $allProgress = [];
        foreach ($rs as $row) {
            $username = $row['username'];
            if (!isset($allProgress[$username])) {
                $allProgress[$username] = [
                    'name' => $row['name'],
                    'progress' => [],
                    'username' => $username,
                ];
            }

            $assignmentAlias = $row['assignment_alias'];
            $problemAlias = $row['problem_alias'];

            if (is_null($assignmentAlias) || is_null($problemAlias)) {
                continue;
            }

            if (!isset($allProgress[$username]['progress'][$assignmentAlias])) {
                $allProgress[$username]['progress'][$assignmentAlias] = [];
            }

            $allProgress[$username]['progress'][$assignmentAlias][$problemAlias] = floatval(
                $row['problem_score']
            );
        }

        usort(
            $allProgress,
            /**
             * @param array{name: string|null, progress: array<string, array<string, float>>, username: string} $a
             * @param array{name: string|null, progress: array<string, array<string, float>>, username: string} $b
             */
            function (array $a, array $b): int {
                return strcasecmp(
                    !empty($a['name']) ? $a['name'] : $a['username'],
                    !empty($b['name']) ? $b['name'] : $b['username']
                );
            }
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
     * @return array{share_user_information: bool, accept_teacher: bool}
     */
    final public static function getSharingInformation(
        int $identityId,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ): array {
        if ($course->group_id !== $group->group_id) {
            return ['share_user_information' => false, 'accept_teacher' => false];
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
            return ['share_user_information' => false, 'accept_teacher' => false];
        }

        return [
            'share_user_information' => boolval(
                $row['share_user_information']
            ),
            'accept_teacher' => boolval(
                $row['accept_teacher']
            ),
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
}