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
               (time() > strtotime($container->finish_time));
    }

    public static function insideSubmissionWindow($container, $user_id) {
        if (isset($container->finish_time)) {
            if (time() > strtotime($container->finish_time) ||
                time() < strtotime($container->start_time)) {
                return false;
            }
        }

        if (!isset($container->window_length)) {
            return true;
        }

        $problemset_user = ProblemsetUsersDAO::getByPK(
            $user_id,
            $container->problemset_id
        );
        $first_access_time = $problemset_user->access_time;

        return time() <= strtotime($first_access_time) + $container->window_length * 60;
    }
}
