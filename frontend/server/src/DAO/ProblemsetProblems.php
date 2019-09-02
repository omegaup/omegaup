<?php

namespace OmegaUp\DAO;

use \ProblemController;

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
    final public static function getProblemsAssignmentByCourseAlias(
        \OmegaUp\DAO\VO\Courses $course
    ) : array {
        // Build SQL statement
        $sql = '
            SELECT
                a.name, a.alias AS assignment_alias,a.description,
                UNIX_TIMESTAMP(a.start_time) AS start_time,
                UNIX_TIMESTAMP(a.finish_time) AS finish_time,
                a.assignment_type, p.alias AS problem_alias,
                a.publish_time_delay, p.problem_id
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
                a.`assignment_id`, pp.`order`, `pp`.`problem_id` ASC;
        ';
        $val = [$course->alias];

        $problemsAssignments = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);

        $result = [];

        foreach ($problemsAssignments as $assignment) {
            $assignmentAlias = $assignment['assignment_alias'];
            if (!isset($result[$assignmentAlias])) {
                $result[$assignmentAlias] = [
                    'name' => $assignment['name'],
                    'description' => $assignment['description'],
                    'start_time' => $assignment['start_time'],
                    'finish_time' => $assignment['finish_time'],
                    'assignment_alias' => $assignment['assignment_alias'],
                    'assignment_type' => $assignment['assignment_type'],
                    'publish_time_delay' => $assignment['publish_time_delay'],
                    'problems' => [],
                ];
            }
            array_push($result[$assignmentAlias]['problems'], [
                'problem_alias' => $assignment['problem_alias'],
                'problem_id' => $assignment['problem_id'],
            ]);
        }

        return $result;
    }

    /*
     * Get number of problems in problemset.
     */
    final public static function countProblemsetProblems(\OmegaUp\DAO\VO\Problemsets $problemset) {
        // Build SQL statement
        $sql = 'SELECT COUNT(pp.problem_id) ' .
               'FROM Problemset_Problems pp ' .
               'WHERE pp.problemset_id = ?';
        $val = [$problemset->problemset_id];
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /*
     * Get problemset problems including problemset alias, points, and order
     */
    final public static function getProblemsByProblemset(
        int $problemsetId
    ) : array {
        // Build SQL statement
        $sql = 'SELECT
                    p.title,
                    p.problem_id,
                    p.alias,
                    p.visibility,
                    p.visits,
                    p.submissions,
                    p.accepted,
                    p.difficulty,
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

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemsetId]);
    }

    /*
     * Get problemset problems including problemset alias, points, and order
     */
    final public static function getByProblemset($problemset_id) {
        // Build SQL statement
        $sql = 'SELECT
                    *
                FROM
                    Problemset_Problems
                WHERE
                    problemset_id = ?
                ORDER BY
                    `order`, `problem_id` ASC;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemset_id]);

        $problemsetProblems = [];
        foreach ($rs as $row) {
            array_push($problemsetProblems, new \OmegaUp\DAO\VO\ProblemsetProblems($row));
        }
        return $problemsetProblems;
    }

    /*
     * Get relevant problems including problemset alias
     * @return \OmegaUp\DAO\VO\Problems[]
     */
    final public static function getRelevantProblems(
        \OmegaUp\DAO\VO\Problemsets $problemset
    ) : array {
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
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val) as $row) {
            $result[] = new \OmegaUp\DAO\VO\Problems($row);
        }
        return $result;
    }

    /**
     * Copy problemset problems from one problem set to the new problemset
     * @param Number, Number
     * @return void
     */
    public static function copyProblemset($newProblemsetId, $oldProblemsetId) {
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
      *
      * @param $problemsetId
      * @param $problemId
      * @param $order
      * @return Affected Rows
      */
    final public static function updateProblemsOrder($problemsetId, $problemId, $order) {
        $sql = 'UPDATE `Problemset_Problems` SET `order` = ? WHERE `problemset_id` = ? AND `problem_id` = ?;';
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
    final public static function getMaxPointsByProblemset($problemset_id) {
        // Build SQL statement
        $sql = 'SELECT
                    SUM(points) as max_points
                FROM
                    Problemset_Problems
                WHERE
                    problemset_id = ?;';

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$problemset_id]);
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
    ) : void {
        $now = \OmegaUp\Time::get();

        if ($updatePublished == ProblemController::UPDATE_PUBLISHED_OWNED_PROBLEMSETS) {
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
                    UNIX_TIMESTAMP(c.finish_time) >= ? AND
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
                    UNIX_TIMESTAMP(a.finish_time) >= ? AND
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
        } elseif ($updatePublished == ProblemController::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS) {
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
                    UNIX_TIMESTAMP(c.finish_time) >= ? AND
                    pp.problem_id = ?;
            ';
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
                    UNIX_TIMESTAMP(a.finish_time) >= ? AND
                    pp.problem_id = ?;
            ';
            $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [
                $now,
                $problem->problem_id,
            ]);
            foreach ($rs as $row) {
                array_push($problemsets, new \OmegaUp\DAO\VO\Problemsets($row));
            }

            $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
            $problemsets = array_filter(
                $problemsets,
                function (\OmegaUp\DAO\VO\Problemsets $problemset) use ($identity) {
                    return \OmegaUp\Authorization::isAdmin($identity, $problemset);
                }
            );

            if (!empty($problemsets)) {
                $problemsetIds = array_map(function (\OmegaUp\DAO\VO\Problemsets $p) {
                    return (int)$p->problemset_id;
                }, $problemsets);
                $problemsetPlaceholders = implode(', ', array_fill(0, count($problemsetIds), '?'));

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
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problem->current_version, $problem->problem_id]);
    }

    public static function updateProblemsetProblemSubmissions(
        \OmegaUp\DAO\VO\ProblemsetProblems $problemsetProblem
    ) : void {
        $sql = '
            INSERT IGNORE INTO
                Runs (
                    submission_id, version, verdict
                )
            SELECT
                s.submission_id, ?, "JE"
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
}
