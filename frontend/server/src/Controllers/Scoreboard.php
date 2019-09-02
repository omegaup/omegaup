<?php

 namespace OmegaUp\Controllers;

/**
 * ScoreboardController
 *
 */
class Scoreboard extends \OmegaUp\Controllers\Controller {
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
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $contest = \OmegaUp\DAO\Contests::getByAlias($r['alias']);
        if (is_null($contest)) {
            $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
            if (is_null($course)) {
                throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
            }
            $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse($r['alias'], $course->course_id);
            if (is_null($assignment)) {
                throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
            }
            \OmegaUp\Scoreboard::refreshScoreboardCache(\OmegaUp\ScoreboardParams::fromAssignment($assignment, $course->group_id, true));
        } else {
            \OmegaUp\Scoreboard::refreshScoreboardCache(\OmegaUp\ScoreboardParams::fromContest($contest));
        }

        return [
            'status' => 'ok'
        ];
    }
}
