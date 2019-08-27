<?php

require_once('libs/dao/Problemsets.dao.php');
require_once('libs/dao/Problemset_Problems.dao.php');

class ProblemsetController extends Controller {
    public static function validateAddProblemToProblemset(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        if ($problem->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED ||
            $problem->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemIsBanned');
        }
        if (!ProblemsDAO::isVisible($problem)
            && !\OmegaUp\Authorization::isProblemAdmin($identity, $problem)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemIsPrivate');
        }
    }

    public static function addProblem(
        $problemset_id,
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit,
        string $currentVersion,
        \OmegaUp\DAO\VO\Identities $identity,
        int $points,
        int $order_in_contest = 1,
        bool $validateVisibility = true
    ) {
        if ($validateVisibility) {
            ProblemsetController::validateAddProblemToProblemset(
                $problem,
                $identity
            );
        }

        self::updateProblemsetProblem(new \OmegaUp\DAO\VO\ProblemsetProblems([
            'problemset_id' => $problemset_id,
            'problem_id' => $problem->problem_id,
            'commit' => $commit,
            'version' => $currentVersion,
            'points' => $points,
            'order' => $order_in_contest,
        ]));
    }

    /**
     * When problem is already in the problemset, it must recalculate
     * the contest_score for all the problemset and problem runs
     */
    private static function updateProblemsetProblem(
        \OmegaUp\DAO\VO\ProblemsetProblems $updatedProblemsetProblem
    ) {
        $oldProblemsetProblem = ProblemsetProblemsDAOBase::getByPK(
            $updatedProblemsetProblem->problemset_id,
            $updatedProblemsetProblem->problem_id
        );
        if (is_null($oldProblemsetProblem)) {
            ProblemsetProblemsDAOBase::create($updatedProblemsetProblem);
            return;
        }
        ProblemsetProblemsDAOBase::update($updatedProblemsetProblem);
        if ($oldProblemsetProblem->points == $updatedProblemsetProblem->points &&
            $oldProblemsetProblem->version == $updatedProblemsetProblem->version
        ) {
            return;
        }
        if ($oldProblemsetProblem->version != $updatedProblemsetProblem->version) {
            ProblemsetProblemsDAO::updateProblemsetProblemSubmissions(
                $updatedProblemsetProblem
            );
        }
        RunsDAO::recalculateScore(
            $updatedProblemsetProblem->problemset_id,
            $updatedProblemsetProblem->problem_id,
            $updatedProblemsetProblem->points,
            $oldProblemsetProblem->points
        );
    }

    /**
     * @param $r
     * @return Array
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        $r = self::wrapRequest($r);

        if ($r['problemset']['type'] == 'Contest') {
            return ContestController::apiDetails(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            return CourseController::apiAssignmentDetails(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'course' => $r['problemset']['course'],
                    'assignment' => $r['problemset']['assignment'],
                ])
            );
        } elseif ($r['problemset']['type'] == 'Interview') {
            return InterviewController::apiDetails(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'interview_alias' => $r['problemset']['interview_alias'],
                ])
            );
        }
        throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
    }

    /**
     * @param $r
     * @return Array
     */
    public static function apiScoreboard(\OmegaUp\Request $r) {
        $r = self::wrapRequest($r);

        if ($r['problemset']['type'] == 'Contest') {
            return ContestController::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'token' => $r['token'],
                    'contest_alias' => $r['problemset']['contest_alias']
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            return CourseController::apiAssignmentScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'token' => $r['token'],
                    'course' => $r['problemset']['course'],
                    'assignment' => $r['problemset']['assignment'],
                ])
            );
        }
        // There is no scoreboard for interviews yet
        return [];
    }

    /**
     * Returns the Scoreboard events
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiScoreboardEvents(\OmegaUp\Request $r) {
        $r = self::wrapRequest($r);

        if ($r['problemset']['type'] == 'Contest') {
            return ContestController::apiScoreboardEvents(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $r['problemset']['contest_alias'],
                ])
            );
        } elseif ($r['problemset']['type'] == 'Assignment') {
            return CourseController::apiAssignmentScoreboardEvents(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'course' => $r['problemset']['course'],
                    'assignment' => $r['problemset']['assignment'],
                    'token' => $r['token'],
                ])
            );
        }
        // Not implemented in interviews yet
        return ['events' => []];
    }

    /**
     * @param \OmegaUp\Request $r
     * $r['tokens'][0] = invalid filter
     * $r['tokens'][1] = Type of filter (all-events, user, contest, problemset, problem)
     * $r['tokens'][2] = Id of entity ($tokens[2])
     * $r['tokens'][3] = Token given by the filter
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function wrapRequest(\OmegaUp\Request $r) {
        $r->ensureInt('problemset_id');

        $r['problemset'] = ProblemsetsDAO::getWithTypeByPK($r['problemset_id']);
        if (is_null($r['problemset'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }
        if ($r['problemset']['type'] == 'Contest') {
            $request = new \OmegaUp\Request([
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
            $response = ContestController::validateDetails($request);
            $request['problemset'] = $r['problemset'];
            $request['contest_alias'] = $response['contest_alias'];
            $request['contest_admin'] = $response['contest_admin'];
            return $request;
        }
        return $r;
    }

    /**
     * Downloads all the runs of the problemset.
     *
     * @param $problemsetId integer The problemset ID.
     * @param $zip ZipStream The object that represents the .zip file.
     */
    public static function downloadRuns(int $problemsetId, ZipStream $zip): void {
        $runs = RunsDAO::getByProblemset($problemsetId);

        $table = ['guid,user,problem,verdict,points'];
        foreach ($runs as $run) {
            $zip->add_file(
                "runs/{$run['guid']}.{$run['language']}",
                SubmissionController::getSource($run['guid'])
            );
            $table[] = "{$run['guid']},{$run['username']},{$run['alias']},{$run['verdict']},{$run['contest_score']}";
        }
        $zip->add_file('summary.csv', implode("\n", $table));
    }
}
