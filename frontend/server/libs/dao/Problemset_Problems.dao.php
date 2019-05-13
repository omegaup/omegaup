<?php

include('base/Problemset_Problems.dao.base.php');
include('base/Problemset_Problems.vo.base.php');
/** ProblemsetProblems Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetProblems }.
  * @access public
  *
  */
class ProblemsetProblemsDAO extends ProblemsetProblemsDAOBase {
    final public static function getProblems($problemset_id) {
        // Build SQL statement
        $sql = '
            SELECT
                p.problem_id, p.title, p.alias, p.languages, pp.points,
                pp.order, pp.version
            FROM
                Problems p
            INNER JOIN
                Problemset_Problems pp ON pp.problem_id = p.problem_id
            WHERE
                pp.problemset_id = ?
            ORDER BY
                pp.`order`, `pp`.`problem_id` ASC;
        ';
        $val = [$problemset_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    /*
     * Get number of problems in problemset.
     */
    final public static function countProblemsetProblems(Problemsets $problemset) {
        // Build SQL statement
        $sql = 'SELECT COUNT(pp.problem_id) ' .
               'FROM Problemset_Problems pp ' .
               'WHERE pp.problemset_id = ?';
        $val = [$problemset->problemset_id];
        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
     * Get problemset problems including problemset alias, points, and order
     */
    final public static function getProblemsetProblems(Problemsets $problemset) {
        // Build SQL statement
        $sql = 'SELECT p.problem_id, p.alias, pp.points, pp.order, pp.version ' .
               'FROM Problems p ' .
               'INNER JOIN Problemset_Problems pp ON pp.problem_id = p.problem_id ' .
               'WHERE pp.problemset_id = ? ' .
               'ORDER BY pp.`order`, `pp`.`problem_id` ASC;';
        $val = [$problemset->problemset_id];
        global $conn;
        return $conn->GetAll($sql, $val);
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

        global $conn;
        $rs = $conn->Execute($sql, [$problemset_id]);

        $problemsetProblems = [];
        foreach ($rs as $row) {
            array_push($problemsetProblems, new ProblemsetProblems($row));
        }
        return $problemsetProblems;
    }

    /*
     *
     * Get relevant problems including problemset alias
     */
    final public static function getRelevantProblems(Problemsets $problemset) {
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
        global $conn;
        $result = [];
        foreach ($conn->Execute($sql, $val) as $row) {
            $result[] = new Problems($row);
        }
        return $result;
    }

    /**
     * Copy problemset problems from one problem set to the new problemset
     * @param Number, Number
     * @return void
     */
    public static function copyProblemset($new_problemset, $old_problemset) {
        $sql = '
            INSERT INTO
                Problemset_Problems (problemset_id, problem_id, version, points, `order`)
            SELECT
                ?, problem_id, version, points, `order`
            FROM
                Problemset_Problems
            WHERE
                Problemset_Problems.problemset_id = ?;
        ';
        global $conn;
        $params = [$new_problemset, $old_problemset];
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
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
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    final public static function getProblemsByProblemset($problemset_id) {
        $sql = 'SELECT
                    p.title,
                    p.alias,
                    p.visits,
                    p.submissions,
                    p.accepted,
                    p.difficulty,
                    p.order,
                    p.languages,
                    pp.points,
                    pp.version
                FROM
                    Problems p
                INNER JOIN
                    Problemset_Problems pp
                ON
                    p.problem_id = pp.problem_id
                WHERE
                    pp.problemset_id = ?
                ORDER BY
                    pp.order, pp.problem_id ASC;';

        global $conn;
        $rs = $conn->Execute($sql, [$problemset_id]);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, $row);
        }
        return $problems;
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

        global $conn;
        return $conn->GetOne($sql, [$problemset_id]);
    }

    /**
     * Update the version of the problem across all problemsets to the current
     * version.
     *
     * @param Problems $problem         the problem.
     * @param Users    $user            the user that is making the change.
     * @param string   $updatePublished the way to update the problemset runs.
     */
    final public static function updateVersionToCurrent(
        Problems $problem,
        Users $user,
        string $updatePublished
    ) : void {
        global $conn;
        $now = Time::get();

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
            $conn->Execute($sql, [
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
            $conn->Execute($sql, [
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
            $rs = $conn->Execute($sql, [
                $now,
                $problem->problem_id,
            ]);
            foreach ($rs as $row) {
                array_push($problemsets, new Problemsets($row));
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
            $rs = $conn->Execute($sql, [
                $now,
                $problem->problem_id,
            ]);
            foreach ($rs as $row) {
                array_push($problemsets, new Problemsets($row));
            }

            $problemsets = array_filter($problemsets, function (Problemsets $p) use ($user) {
                return Authorization::isAdmin($user->main_identity_id, $p);
            });

            if (!empty($problemsets)) {
                $problemsetIds = array_map(function (Problemsets $p) {
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
                $conn->Execute($sql, array_merge([
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
        $conn->Execute($sql, [$problem->current_version, $problem->problem_id]);
    }
}
