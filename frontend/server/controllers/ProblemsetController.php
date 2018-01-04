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
            self::saveProblem(new ProblemsetProblems([
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
    public static function saveProblem(ProblemsetProblems $Problemset_Problems) {
        $problem = ProblemsetProblemsDAOBase::getByPK(
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id
        );
        if (!is_null($problem)) {
            if ($problem->points != $Problemset_Problems->points) {
                self::recalculateScore($Problemset_Problems, $problem->points);
            }
        }
        ProblemsetProblemsDAOBase::save($Problemset_Problems);
    }

    /**
     * Recalculate the contest_score of all problemset and problem Runs
     */
    private static function recalculateScore(ProblemsetProblems $Problemset_Problems, $original_points) {
        $runs = RunsDAO::GetAllRuns(
            $Problemset_Problems->problemset_id,
            null,
            null,
            $Problemset_Problems->problem_id,
            null,
            null,
            null,
            null
        );
        foreach ($runs as $run) {
            $exsistingRun = RunsDAOBase::getByPK($run['run_id']);
            $exsistingRun->contest_score = round((
                (int)$run['contest_score'] / (int)$original_points) * (int)$Problemset_Problems->points);
            RunsDAOBase::save($exsistingRun);
        }
    }
}
