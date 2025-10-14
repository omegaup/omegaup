<?php

namespace OmegaUp\Controllers;

/**
 * @psalm-type ProblemQualityPayload=array{canNominateProblem: bool, dismissed: bool, dismissedBeforeAc: bool, language?: string, nominated: bool, nominatedBeforeAc: bool, problemAlias: string, solved: bool, tried: bool}
 * @psalm-type ProblemsetProblem=array{accepted: int, accepts_submissions: bool, alias: string, commit: string, difficulty: float, has_submissions: bool, input_limit: int, is_extra_problem: bool, languages: string, letter?: string, order: int, points: float, problem_id?: int, quality_payload?: ProblemQualityPayload, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, max_points: float, name: string, opened: bool, order: int, problemCount: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 * @psalm-type ArenaProblemset=array{admin?: bool, admission_mode?: string, alias?: string, courseAssignments?: list<CourseAssignment>, director?: string, feedback?: string, finish_time: \OmegaUp\Timestamp|null, name?: string, opened?: bool, original_contest_alias?: string, original_problemset_id?: int, problems?: list<ProblemsetProblem>, problemset_id?: int, requests_user_information?: string, show_penalty?: bool, start_time?: \OmegaUp\Timestamp, submission_deadline?: \OmegaUp\Timestamp, submissions_gap?: int, title?: string}
 * @psalm-type Problemset=array{admin?: bool, admission_mode?: string, alias?: string, archived?: bool, assignment_type?: null|string, contest_alias?: null|string, courseAssignments?: list<CourseAssignment>, description: null|string, director?: string, feedback?: string, finish_time?: \OmegaUp\Timestamp|null, has_submissions?: bool, languages?: list<string>, name?: string, needs_basic_information?: bool, opened?: bool, original_contest_alias?: null|string, original_problemset_id?: int|null, score_mode?: string, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problems?: list<ProblemsetProblem>, problemset_id: int|null, requests_user_information?: string, rerun_id?: int, scoreboard?: int, scoreboard_url?: string, scoreboard_url_admin?: string, show_penalty?: bool, show_scoreboard_after?: bool, start_time?: \OmegaUp\Timestamp, submission_deadline?: \OmegaUp\Timestamp|null, submissions_gap?: int, title?: string, users?: list<array{access_time: \OmegaUp\Timestamp|null, country: null|string, email: null|string, user_id: int|null, username: string}>, window_length?: int|null}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type ScoreboardRankingProblemDetailsGroup=array{cases: list<array{meta: RunMetadata}>}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<CaseResult>, details: array{groups: list<ScoreboardRankingProblemDetailsGroup>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 * @psalm-type ScoreboardEvent=array{classname: string, country: string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}
 */
class Problemset extends \OmegaUp\Controllers\Controller {
    public static function validateAddProblemToProblemset(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity,
        ?int $problemsetId = null
    ): void {
        if (
            $problem->visibility == \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED ||
            $problem->visibility == \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'problemIsBanned'
            );
        }
        $canEditProblemset = !is_null(
            $problemsetId
        ) && \OmegaUp\Authorization::canEditProblemset(
            $identity,
            $problemsetId
        );

        if (!$canEditProblemset) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotAddProb'
            );
        }

        $problemsetProblem = \OmegaUp\DAO\Base\ProblemsetProblems::getByPK(
            $problemsetId,
            $problem->problem_id
        );
        if (!is_null($problemsetProblem)) {
            // Invited admin should update a problem in problemset
            return;
        }

        // Only problem admins are allowed to add their own private problems in problemsets.
        if (
            !\OmegaUp\DAO\Problems::isVisible($problem) &&
            !\OmegaUp\Authorization::isProblemAdmin($identity, $problem)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowedToAddPrivateProblem'
            );
        }
    }

    public static function addProblem(
        int $problemsetId,
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit,
        string $currentVersion,
        \OmegaUp\DAO\VO\Identities $identity,
        float $points,
        int $order_in_contest = 1,
        ?\OmegaUp\DAO\VO\ProblemsetProblems $oldproblemsetProblem = null,
        bool $shouldValidateVisibility = true,
        bool $isExtraProblem = false
    ): void {
        if ($shouldValidateVisibility) {
            \OmegaUp\Controllers\Problemset::validateAddProblemToProblemset(
                $problem,
                $identity,
                $problemsetId
            );
        }

        self::updateProblemsetProblem(new \OmegaUp\DAO\VO\ProblemsetProblems([
            'problemset_id' => $problemsetId,
            'problem_id' => $problem->problem_id,
            'commit' => $commit,
            'version' => $currentVersion,
            'points' => $points,
            'order' => $order_in_contest,
            'is_extra_problem' => $isExtraProblem,
        ]), $oldproblemsetProblem);
    }

    /**
     * When problem is already in the problemset, it must recalculate
     * the contest_score for all the problemset and problem runs
     */
    private static function updateProblemsetProblem(
        \OmegaUp\DAO\VO\ProblemsetProblems $updatedProblemsetProblem,
        ?\OmegaUp\DAO\VO\ProblemsetProblems $oldProblemsetProblem = null,
    ): void {
        if (is_null($oldProblemsetProblem)) {
            \OmegaUp\DAO\Base\ProblemsetProblems::create(
                $updatedProblemsetProblem
            );
            return;
        }
        \OmegaUp\DAO\Base\ProblemsetProblems::update($updatedProblemsetProblem);
        if (
            $oldProblemsetProblem->points == $updatedProblemsetProblem->points &&
            $oldProblemsetProblem->version == $updatedProblemsetProblem->version
        ) {
            return;
        }
        if ($oldProblemsetProblem->version != $updatedProblemsetProblem->version) {
            \OmegaUp\DAO\ProblemsetProblems::updateProblemsetProblemSubmissions(
                $updatedProblemsetProblem
            );
        }
        \OmegaUp\DAO\Runs::recalculateScore(
            intval($updatedProblemsetProblem->problemset_id),
            intval($updatedProblemsetProblem->problem_id),
            $updatedProblemsetProblem->points,
            $oldProblemsetProblem->points
        );
    }

    /**
     * @return Problemset
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $course
     * @omegaup-request-param int $problemset_id
     * @omegaup-request-param null|string $token
     * @omegaup-request-param mixed $tokens
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        [
            'problemset' => $problemset,
            'request' => $r,
        ] = self::wrapRequest($r);

        if ($problemset['type'] == 'Contest') {
            return \OmegaUp\Controllers\Contest::apiDetails(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $problemset['contest_alias']
                ])
            );
        } elseif ($problemset['type'] == 'Assignment') {
            return \OmegaUp\Controllers\Course::apiAssignmentDetails(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'course' => $problemset['course'],
                    'assignment' => $problemset['assignment'],
                ])
            );
        }
        throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
    }

    /**
     * @return Scoreboard
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $course
     * @omegaup-request-param int $problemset_id
     * @omegaup-request-param mixed $token
     * @omegaup-request-param mixed $tokens
     */
    public static function apiScoreboard(\OmegaUp\Request $r): array {
        [
            'problemset' => $problemset,
            'request' => $r,
        ] = self::wrapRequest($r);

        if ($problemset['type'] === 'Contest') {
            return \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'token' => $r['token'],
                    'contest_alias' => $problemset['contest_alias']
                ])
            );
        }
        if ($problemset['type'] === 'Assignment') {
            return \OmegaUp\Controllers\Course::apiAssignmentScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'token' => $r['token'],
                    'course' => $problemset['course'],
                    'assignment' => $problemset['assignment'],
                ])
            );
        }
        return [
            'problems' => [],
            'ranking' => [],
            'start_time' => new \OmegaUp\Timestamp(0),
            'finish_time' => new \OmegaUp\Timestamp(0),
            'time' => new \OmegaUp\Timestamp(0),
            'title' => '',
        ];
    }

    /**
     * Returns the Scoreboard events
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{events: list<ScoreboardEvent>}
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $course
     * @omegaup-request-param int $problemset_id
     * @omegaup-request-param mixed $token
     * @omegaup-request-param mixed $tokens
     */
    public static function apiScoreboardEvents(\OmegaUp\Request $r): array {
        [
            'problemset' => $problemset,
            'request' => $r,
        ] = self::wrapRequest($r);

        if ($problemset['type'] == 'Contest') {
            return \OmegaUp\Controllers\Contest::apiScoreboardEvents(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $problemset['contest_alias'],
                ])
            );
        } elseif ($problemset['type'] == 'Assignment') {
            return \OmegaUp\Controllers\Course::apiAssignmentScoreboardEvents(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'course' => $problemset['course'],
                    'assignment' => $problemset['assignment'],
                    'token' => $r['token'],
                ])
            );
        }
        return ['events' => []];
    }

    /**
     * @param \OmegaUp\Request $r $r['tokens'][0] = invalid filter $r['tokens'][1] = Type of filter (all-events, user, contest, problemset, problem) $r['tokens'][2] = Id of entity ($tokens[2]) $r['tokens'][3] = Token given by the filter
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{problemset: array{assignment: null|string, contest_alias: null|string, course: null|string, type: string}, request: \OmegaUp\Request}
     *
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param int $problemset_id
     * @omegaup-request-param null|string $token
     * @omegaup-request-param mixed $tokens
     */
    public static function wrapRequest(\OmegaUp\Request $r): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }
        $problemsetId = $r->ensureInt('problemset_id');

        $problemset = \OmegaUp\DAO\Problemsets::getWithTypeByPK($problemsetId);
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        if ($problemset['type'] == 'Contest') {
            if (is_null($problemset['contest_alias'])) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }
            $token = $r->ensureOptionalString('token');
            $request = new \OmegaUp\Request([
                'token' => $token,
                'problemset_id' => $problemsetId,
                'contest_alias' => $problemset['contest_alias'],
            ]);
            if (isset($r['auth_token']) && is_string($r['auth_token'])) {
                $request['auth_token'] = $r['auth_token'];
            }
            /** @psalm-suppress MixedArgument $r['tokens'] is definitely an array here. */
            if (
                isset($r['tokens']) &&
                is_array($r['tokens']) &&
                count($r['tokens']) >= 4
            ) {
                /** @psalm-suppress MixedArrayAccess $r['tokens'] is definitely an array here. */
                $token = strval($r['tokens'][3]);
                $request['token'] = $token;
            }
            $response = \OmegaUp\Controllers\Contest::validateDetails(
                $problemset['contest_alias'],
                $r->identity,
                $token
            );
            $request['contest_alias'] = $response['contest_alias'];
            $request['contest_admin'] = $response['contest_admin'];
            return [
                'problemset' => $problemset,
                'request' => $request,
            ];
        }
        return [
            'problemset' => $problemset,
            'request' => $r,
        ];
    }

    /**
     * Downloads all the runs of the problemset.
     *
     * @param int $problemsetId The problemset ID.
     * @param \ZipStream $zip The object that represents the .zip file.
     */
    public static function downloadRuns(
        int $problemsetId,
        \ZipStream $zip
    ): void {
        $runs = \OmegaUp\DAO\Runs::getByProblemset($problemsetId);

        $table = ['guid,user,problem,verdict,points'];
        foreach ($runs as $run) {
            $zip->add_file(
                "runs/{$run['guid']}.{$run['language']}",
                \OmegaUp\Controllers\Submission::getSource($run['guid'])
            );
            $table[] = "{$run['guid']},{$run['username']},{$run['alias']},{$run['verdict']},{$run['contest_score']}";
        }
        $zip->add_file('summary.csv', implode("\n", $table));
    }
}
