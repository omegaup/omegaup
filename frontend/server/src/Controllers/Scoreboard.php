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
     * @return array{status: string}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param null|string $course_alias
     * @omegaup-request-param mixed $token
     */
    public static function apiRefresh(\OmegaUp\Request $r) {
        // This is not supposed to be called by end-users, but by the
        // Grader service. Regular sessions cannot be used since they
        // expire, so use a pre-shared secret to authenticate that
        // grants admin-level privileges just for this call.
        if ($r['token'] !== OMEGAUP_GRADER_SECRET) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (!is_null($r['course_alias'])) {
            $courseAlias = $r->ensureString(
                'course_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            );
            $assignmentAlias = $r->ensureString(
                'alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            );
            $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
            if (
                is_null($course) ||
                is_null($course->group_id) ||
                is_null($course->course_id)
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseNotFound'
                );
            }
            $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
                $assignmentAlias,
                intval($course->course_id)
            );
            if (is_null($assignment)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'assignmentNotFound'
                );
            }
            \OmegaUp\Scoreboard::refreshScoreboardCache(
                \OmegaUp\ScoreboardParams::fromAssignment(
                    $assignment,
                    $course->group_id,
                    true
                )
            );

            return ['status' => 'ok'];
        }
        $contestAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }
        \OmegaUp\Scoreboard::refreshScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $contest
            )
        );
        return ['status' => 'ok'];
    }
}
