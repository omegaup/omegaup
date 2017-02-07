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
class ProblemsetsDAO extends ProblemsetsDAOBase
{
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

        $course = CoursesDAO::getCourseForProblemset($problemset_id);
        if (!is_null($course)) {
            return $course;
        }

        $interview = InterviewsDAO::getInterviewForProblemset($problemset_id);
        if (!is_null($interview)) {
            return $interview;
        }

        return null;
    }
}
