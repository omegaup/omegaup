<?php

require_once('libs/dao/Problemsets.dao.php');
require_once('libs/dao/Problemset_Problems.dao.php');

class ProblemsetController extends Controller {
    public static function validateAddProblemToProblemset($problemset_id, Problems $problem, $current_user_id) {
        if ($problem->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED ||
            $problem->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED) {
            throw new ForbiddenAccessException('problemIsBanned');
        }
        if (!ProblemsDAO::isVisible($problem)
            && !Authorization::isProblemAdmin($current_user_id, $problem)
        ) {
            throw new ForbiddenAccessException('problemIsPrivate');
        }
    }

    public static function addProblem($problemset_id, Problems $problem, $current_user_id, $points, $order_in_contest = 1) {
        ProblemsetController::validateAddProblemToProblemset(
            $problemset_id,
            $problem,
            $current_user_id
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
        ProblemsetController::validateDetails($r);

        if ($r['problemset']['type'] == 'Contest') {
            $details = ContestController::apiDetails(
                new Request([
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            $details = CourseController::apiAssignmentDetails(
                new Request([
                    'course' => $r['problemset']['course'],
                    'assignment' => $r['problemset']['assignment'],
                ])
            );
        } elseif ($r['problemset']['type'] == 'Interview') {
            $details = InterviewController::apiDetails(
                new Request([
                    'interview_alias' => $r['problemset']['interview_alias'],
                ])
            );
        }

        return $details;
    }

    /**
     * @param $r
     * @return Array
     */
    public static function apiScoreboard(Request $r) {
        Validators::isStringNonEmpty($r['problemset_id'], 'problemset_id');
        ProblemsetController::validateDetails($r);

        if ($r['problemset']['type'] == 'Contest') {
            $scoreboard = ContestController::apiScoreboard(
                new Request([
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            $scoreboard = CourseController::apiAssignmentScoreboard(
                new Request([
                    'course_alias' => $r['problemset']['course'],
                    'assignment_alias' => $r['problemset']['assignment'],
                ])
            );
        } elseif ($r['problemset']['type'] == 'Interview') {
            // There in no scoreboard for interviews yet
            return [];
        }

        return $scoreboard;
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
        ProblemsetController::validateDetails($r);

        if ($r['problemset']['type'] != 'Contest') {
            // Not implemented in courses nor interviews yet
            return ['events' => []];
        } else {
            $scoreboardEvents = ContestController::apiScoreboardEvents(
                new Request([
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        }
        return $scoreboardEvents;
    }

    /**
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function validateDetails(Request $r) {
        Validators::isStringNonEmpty($r['problemset_id'], 'problemset_id');

        try {
            $r['problemset'] = ProblemsetsDAO::getTypeByPK($r['problemset_id']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problemset'])) {
            throw new NotFoundException('problemsetNotFound');
        }
        return $r;
    }
}
