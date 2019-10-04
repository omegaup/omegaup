<?php

namespace OmegaUp\DAO;

/**
 * Courses Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Courses}.
 * @access public
 */
class Courses extends \OmegaUp\DAO\Base\Courses {
    public static function findByName($name) {
        $sql = "SELECT DISTINCT c.*
                FROM Courses c
                WHERE c.name
                LIKE CONCAT('%', ?, '%') LIMIT 10";

        $resultRows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$name]);
        $finalResult = [];

        foreach ($resultRows as $row) {
            array_push($finalResult, new \OmegaUp\DAO\VO\Courses($row));
        }

        return $finalResult;
    }

    /**
      * Given a course alias, get all of its assignments. Hides any assignments
      * that have not started, if not an admin.
      **/
    public static function getAllAssignments($alias, $isAdmin) {
        // Non-admins should not be able to see assignments that have not
        // started.
        $timeCondition = $isAdmin ? '' : 'AND a.start_time <= CURRENT_TIMESTAMP';
        $sql = "
            SELECT
                a.*,
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
            WHERE
                c.alias = ? $timeCondition
            ORDER BY
                start_time;";

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$alias]);

        $ar = [];
        foreach ($rs as $row) {
            unset($row['acl_id']);
            unset($row['assignment_id']);
            unset($row['problemset_id']);
            unset($row['course_id']);
            $row['start_time'] =  \OmegaUp\DAO\DAO::fromMySQLTimestamp($row['start_time']);
            $row['finish_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($row['finish_time']);
            array_push($ar, $row);
        }

        return $ar;
    }

    public static function getCoursesForStudent($identity_id) {
        $sql = 'SELECT c.*
                FROM Courses c
                INNER JOIN (
                    SELECT g.group_id
                    FROM Groups_Identities gi
                    INNER JOIN Groups g ON g.group_id = gi.group_id
                    WHERE gi.identity_id = ?
                ) gg
                ON c.group_id = gg.group_id;
               ';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$identity_id]);
        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new \OmegaUp\DAO\VO\Courses($row));
        }
        return $courses;
    }

    /**
     * Returns a list of students within a course
     * @param  int $course_id
     * @param  int $group_id
     * @return Array Students data
     */
    public static function getStudentsInCourseWithProgressPerAssignment($course_id, $group_id) {
        $sql = 'SELECT i.username, i.name, pr.alias as assignment_alias, pr.assignment_score
                FROM Groups g
                INNER JOIN Groups_Identities gi
                    ON g.group_id = ? AND g.group_id = gi.group_id
                INNER JOIN Identities i
                    ON i.identity_id = gi.identity_id
                LEFT JOIN (
                    SELECT bpr.alias, bpr.identity_id, sum(best_score_of_problem) as assignment_score
                    FROM (
                        SELECT a.alias, a.assignment_id, psp.problem_id, s.identity_id, max(r.contest_score) as best_score_of_problem
                        FROM Assignments a
                        INNER JOIN Problemsets ps
                            ON a.problemset_id = ps.problemset_id
                        INNER JOIN Problemset_Problems psp
                            ON psp.problemset_id = ps.problemset_id
                        INNER JOIN Submissions s
                            ON s.problem_id = psp.problem_id
                            AND s.problemset_id = a.problemset_id
                        INNER JOIN Runs r
                            ON r.run_id = s.current_run_id
                        WHERE a.course_id = ?
                        GROUP BY a.assignment_id, psp.problem_id, s.identity_id
                    ) bpr
                    GROUP BY bpr.assignment_id, bpr.identity_id
                ) pr
                ON pr.identity_id = i.identity_id';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$group_id, $course_id]);
        $progress = [];
        foreach ($rs as $row) {
            $username = $row['username'];
            if (!isset($progress[$username])) {
                $progress[$username] = [
                    'name' => $row['name'],
                    'progress' => [],
                    'username' => $username,
                ];
            }

            if (!is_null($row['assignment_score'])) {
                $progress[$username]['progress'][$row['assignment_alias']] = $row['assignment_score'];
            }
        }
        usort($progress, function ($a, $b) {
            return strcasecmp(
                !empty($a['name']) ? $a['name'] : $a['username'],
                !empty($b['name']) ? $b['name'] : $b['username']
            );
        });
        return $progress;
    }

    /**
     * Returns the score per assignment of a user, as well as the maximum score attainable
     * @param  int $course_id
     * @param  int $user_id
     * @return Array Students data
     */
    public static function getAssignmentsProgress($course_id, $identity_id) {
        $sql = 'SELECT a.alias as assignment, IFNULL(pr.total_score, 0) as score, a.max_points as max_score
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
                where a.course_id = ?';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$course_id, $identity_id, $course_id]);

        $progress = [];
        foreach ($rs as $row) {
            $assignment = $row['assignment'];
            $progress[$assignment] = [
                'score' => floatval($row['score']),
                'max_score' => floatval($row['max_score']),
            ];
        }

        return $progress;
    }

    /**
     * Returns all courses that an identity can manage.
     */
    final public static function getAllCoursesAdminedByIdentity(
        $identity_id,
        $page = 1,
        $pageSize = 1000
    ) {
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
            $identity_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identity_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identity_id,
            (int)$offset,
            (int)$pageSize,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new \OmegaUp\DAO\VO\Courses($row));
        }
        return $courses;
    }

    /**
     * Returns all courses owned by a user.
     */
    final public static function getAllCoursesOwnedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
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
            $user_id,
            (int)$offset,
            (int)$pageSize,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $courses = [];
        foreach ($rs as $row) {
            array_push($courses, new \OmegaUp\DAO\VO\Courses($row));
        }
        return $courses;
    }

    final public static function getByAlias(string $alias) : ?\OmegaUp\DAO\VO\Courses {
        $sql = 'SELECT * FROM Courses WHERE (alias = ?) LIMIT 1;';

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$alias]);
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Courses($row);
    }

    final public static function getAssignmentByAlias(\OmegaUp\DAO\VO\Courses $course, string $assignmentAlias) {
        $sql = 'SELECT * FROM Assignments WHERE (alias = ? AND course_id = ?) LIMIT 1;';
        $params = [$assignmentAlias, $course->course_id];

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    final public static function updateAssignmentMaxPoints(\OmegaUp\DAO\VO\Courses $course, string $assignment_alias) {
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

    final public static function getSharingInformation($identity_id, \OmegaUp\DAO\VO\Courses $course, \OmegaUp\DAO\VO\Groups $group) {
        if ($course->group_id != $group->group_id) {
            return true;
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
            $identity_id,
            $group->group_id,
        ];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return $row;
    }

    public static function countCourses(int $startTimestamp, int $endTimestamp) : int {
        $sql = '
            SELECT
                COUNT(c.course_id)
            FROM
                Courses c
            WHERE
                c.start_time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?);
';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$startTimestamp, $endTimestamp]);
    }

    public static function countAttemptedIdentities(
        string $courseAlias,
        int $startTimestamp,
        int $endTimestamp
    ) : int {
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
    ) : int {
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
