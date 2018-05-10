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
}
