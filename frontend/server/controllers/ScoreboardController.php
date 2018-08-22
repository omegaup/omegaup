<?php

/**
 * ScoreboardController
 *
 */
class ScoreboardController extends Controller {
    /**
     * Returns a list of contests
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRefresh(Request $r) {
        // This is not supposed to be called by end-users, but by the
        // Grader service. Regular sessions cannot be used since they
        // expire, so use a pre-shared secret to authenticate that
        // grants admin-level privileges just for this call.
        if ($r['token'] !== OMEGAUP_GRADER_SECRET) {
            throw new ForbiddenAccessException();
        }

        $contest = ContestsDAO::getByAlias($r['alias']);
        if ($contest === null) {
            $course = CoursesDAO::getByAlias($r['course_alias']);
            if ($course === null) {
                throw new NotFoundException();
            }
            $assignment = AssignmentsDAO::getByAliasAndCourse($r['alias'], $course->course_id);
            if ($assignment === null) {
                throw new NotFoundException();
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
