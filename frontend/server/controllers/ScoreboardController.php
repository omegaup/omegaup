<?php

/**
 * ScoreboardController
 *
 */
class ScoreboardController extends Controller {
    /**
     * Returns a list of contests
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRefresh(\OmegaUp\Request $r) {
        // This is not supposed to be called by end-users, but by the
        // Grader service. Regular sessions cannot be used since they
        // expire, so use a pre-shared secret to authenticate that
        // grants admin-level privileges just for this call.
        if ($r['token'] !== OMEGAUP_GRADER_SECRET) {
            throw new ForbiddenAccessException();
        }

        $contest = ContestsDAO::getByAlias($r['alias']);
        if (is_null($contest)) {
            $course = CoursesDAO::getByAlias($r['course_alias']);
            if (is_null($course)) {
                throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
            }
            $assignment = AssignmentsDAO::getByAliasAndCourse($r['alias'], $course->course_id);
            if (is_null($assignment)) {
                throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
            }
            Scoreboard::refreshScoreboardCache(ScoreboardParams::fromAssignment($assignment, $course->group_id, true));
        } else {
            Scoreboard::refreshScoreboardCache(ScoreboardParams::fromContest($contest));
        }

        return [
            'status' => 'ok'
        ];
    }
}
