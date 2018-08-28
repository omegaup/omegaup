<?php

require_once('libs/dao/Problemsets.dao.php');
require_once('libs/dao/Problemset_Problems.dao.php');

class ProblemsetController extends Controller {
    public static function validateAddProblemToProblemset(
        $problemset_id,
        Problems $problem,
        $current_identity_id
    ) {
        if ($problem->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED ||
            $problem->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED) {
            throw new ForbiddenAccessException('problemIsBanned');
        }
        if (!ProblemsDAO::isVisible($problem)
            && !Authorization::isProblemAdmin($current_identity_id, $problem)
        ) {
            throw new ForbiddenAccessException('problemIsPrivate');
        }
    }

    public static function addProblem(
        $problemset_id,
        Problems $problem,
        $current_identity_id,
        $points,
        $order_in_contest = 1
    ) {
        ProblemsetController::validateAddProblemToProblemset(
            $problemset_id,
            $problem,
            $current_identity_id
        );

        try {
            self::updateProblemsetProblem(new ProblemsetProblems([
                'problemset_id' => $problemset_id,
                'problem_id' => $problem->problem_id,
                'points' => $points,
                'order' => $order_in_contest,
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * When problem is already in the problemset, it must recalculate
     * the contest_score for all the problemset and problem runs
     */
    public static function updateProblemsetProblem(ProblemsetProblems $updatedProblemsetProblem) {
        $problem = ProblemsetProblemsDAOBase::getByPK(
            $updatedProblemsetProblem->problemset_id,
            $updatedProblemsetProblem->problem_id
        );
        ProblemsetProblemsDAOBase::save($updatedProblemsetProblem);
        if (is_null($problem) || $problem->points == $updatedProblemsetProblem->points) {
            return;
        }
        RunsDAO::recalculateScore(
            $updatedProblemsetProblem->problemset_id,
            $updatedProblemsetProblem->problem_id,
            $updatedProblemsetProblem->points,
            $problem->points
        );
    }

    /**
     * @param $r
     * @return Array
     */
    public static function apiDetails(Request $r) {
        Validators::isStringNonEmpty($r['problemset_id'], 'problemset_id');
        $r = self::wrapRequest($r);

        if ($r['problemset']['type'] == 'Contest') {
            return ContestController::apiDetails(
                new Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            return CourseController::apiAssignmentDetails(
                new Request([
                    'auth_token' => $r['auth_token'],
                    'course' => $r['problemset']['course'],
                    'assignment' => $r['problemset']['assignment'],
                ])
            );
        } elseif ($r['problemset']['type'] == 'Interview') {
            return InterviewController::apiDetails(
                new Request([
                    'auth_token' => $r['auth_token'],
                    'interview_alias' => $r['problemset']['interview_alias'],
                ])
            );
        }
        throw new NotFoundException('problemsetNotFound');
    }

    /**
     * @param $r
     * @return Array
     */
    public static function apiScoreboard(Request $r) {
        Validators::isStringNonEmpty($r['problemset_id'], 'problemset_id');
        $r = self::wrapRequest($r);

        if ($r['problemset']['type'] == 'Contest') {
            return ContestController::apiScoreboard(
                new Request([
                    'auth_token' => $r['auth_token'],
                    'token' => $r['token'],
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            return CourseController::apiAssignmentScoreboard(
                new Request([
                    'auth_token' => $r['auth_token'],
                    'course_alias' => $r['problemset']['course'],
                    'assignment_alias' => $r['problemset']['assignment'],
                ])
            );
        }
        // There in no scoreboard for interviews yet
        return [];
    }

    /**
     * Returns the Scoreboard events
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function apiScoreboardEvents(Request $r) {
        Validators::isStringNonEmpty($r['problemset_id'], 'problemset_id');
        $r = self::wrapRequest($r);

        if ($r['problemset']['type'] != 'Contest') {
            // Not implemented in courses nor interviews yet
            return ['events' => []];
        }
        return ContestController::apiScoreboardEvents(
            new Request([
                'auth_token' => $r['auth_token'],
                'contest_alias' => $r['problemset']['contest_alias'],
            ])
        );
    }

    /**
     * @param Request $r
     * $r['tokens'][0] = invalid filter
     * $r['tokens'][1] = Type of filter (all-events, user, contest, problemset, problem)
     * $r['tokens'][2] = Id of entity ($tokens[2])
     * $r['tokens'][3] = Token given by the filter
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function wrapRequest(Request $r) {
        Validators::isStringNonEmpty($r['problemset_id'], 'problemset_id');

        try {
            $r['problemset'] = ProblemsetsDAO::getWithTypeByPK($r['problemset_id']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problemset'])) {
            throw new NotFoundException('problemsetNotFound');
        }
        if ($r['problemset']['type'] == 'Contest') {
            $request = new Request([
                'token' => $r['token'],
                'problemset_id' => $r['problemset_id'],
                'contest_alias' => $r['problemset']['contest_alias'],
            ]);
            if (isset($r['auth_token'])) {
                $request['auth_token'] = $r['auth_token'];
            }
            if (isset($r['tokens']) && count($r['tokens']) >= 4) {
                $request['token'] = $r['tokens'][3];
            }
            ContestController::validateDetails($request);
            $request['problemset'] = $r['problemset'];
            return $request;
        }
        return $r;
    }
}
