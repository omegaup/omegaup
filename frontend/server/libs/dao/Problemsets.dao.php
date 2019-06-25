<?php

include('base/Problemsets.dao.base.php');
include('base/Problemsets.vo.base.php');
/** Problemsets Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Problemsets }.
  * @access public
  *
  */
class ProblemsetsDAO extends ProblemsetsDAOBase {
    public static function getProblemsetContainer($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }

        // Whenever I see a problemset I say it's used by a contest
        // and 99% of the time I'm right!
        $contest = ContestsDAO::getContestForProblemset($problemset_id);
        if (!is_null($contest)) {
            return $contest;
        }

        $assignment = AssignmentsDAO::getAssignmentForProblemset($problemset_id);
        if (!is_null($assignment)) {
            return $assignment;
        }

        $interview = InterviewsDAO::getInterviewForProblemset($problemset_id);
        if (!is_null($interview)) {
            return $interview;
        }

        return null;
    }

    /**
     *  Check if a submission is before the deadline.
     *  No one, including admins, can submit after the deadline.
     */
    public static function isLateSubmission($container) {
        return isset($container->finish_time) &&
               (Time::get() > strtotime($container->finish_time));
    }

    public static function insideSubmissionWindow($container, $identity_id) {
        if (isset($container->finish_time)) {
            if (Time::get() > strtotime($container->finish_time) ||
                Time::get() < strtotime($container->start_time)) {
                return false;
            }
        }

        if (!isset($container->window_length)) {
            return true;
        }

        $problemset_identity = ProblemsetIdentitiesDAO::getByPK(
            $identity_id,
            $container->problemset_id
        );
        $first_access_time = $problemset_identity->access_time;

        return Time::get() <= strtotime($first_access_time) + $container->window_length * 60;
    }

    public static function getWithTypeByPK($problemset_id) {
        $sql = 'SELECT
                    type,
                    c.alias AS contest_alias,
                    a.alias AS assignment,
                    cu.alias AS course,
                    i.alias AS interview_alias
                FROM
                    Problemsets p
                LEFT JOIN
                    Assignments a
                ON
                    p.problemset_id = a.problemset_id
                LEFT JOIN
                    Courses cu
                ON
                    a.course_id = cu.course_id
                LEFT JOIN
                    Contests c
                ON
                    p.problemset_id = c.problemset_id
                LEFT JOIN
                    Interviews i
                ON
                    p.problemset_id = i.problemset_id
                WHERE
                    p.problemset_id = ?
                LIMIT
                    1;';
        $params = [$problemset_id];

        global $conn;
        $problemset = $conn->GetRow($sql, $params);
        if (empty($problemset)) {
            return null;
        }

        return $problemset;
    }

    /**
     * validates identity has made submissions in a problemset, and whether this
     * identity is not the main identity of a user when it has been
     * associated with a user account berfore
     *
     */
    public static function shouldShowMessage(
        int $identityId,
        Object $problemset
    ) : bool {
        // It means user is in a Course, then we need to get all the problemsets
        if (!isset($problemset->problemset_id)) {
            $problemsets = '';
            $assignments = CoursesDAO::getAllAssignments($problemset->alias, false);
            foreach ($assignments as $assignment) {
                $problemsets .= $assignment['problemset_id'] . ',';
            }
            $problemsets = substr($problemsets, 0, -1);
        } else {
            $problemsets = $problemset->problemset_id;
        }
        $sql = "SELECT
                    u.main_identity_id,
                    i.identity_id,
                    i.user_id,
                    (SELECT
                        COUNT(*)
                    FROM
                        Submissions
                    WHERE
                        problemset_id IN ($problemsets)
                        AND identity_id = i.identity_id
                    ) AS totalRunsInProblemset
                FROM
                    Identities i
                LEFT JOIN
                    Users u
                ON
                    u.user_id = i.user_id
                WHERE
                    i.identity_id = ?
                LIMIT
                    1;";

        global $conn;
        $row = $conn->GetRow($sql, [$identityId]);

        if (empty($row)) {
            return false;
        }

        return !is_null($row['user_id']) && $row['totalRunsInProblemset'] == 0
                 && $row['identity_id'] != $row['main_identity_id'];
    }
}
