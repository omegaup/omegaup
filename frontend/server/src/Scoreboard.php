<?php

namespace OmegaUp;

/**
 *  Scoreboard
 *
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type ScoreboardEvent=array{classname: string, country: string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type ScoreboardRankingProblemDetailsGroup=array{cases: list<array{meta: RunMetadata}>}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<CaseResult>, details: array{groups: list<ScoreboardRankingProblemDetailsGroup>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 */
class Scoreboard {
    // Column to return total score per user
    const TOTAL_COLUMN = 'total';

    /** @var \OmegaUp\ScoreboardParams */
    private $params;

    /** @var \Monolog\Logger */
    public $log;

    /** @var bool */
    private static $isTestRun = false;

    /** @var bool */
    private static $isLastRunFromCache = false;

    public function __construct(\OmegaUp\ScoreboardParams $params) {
        $this->params = $params;
        $this->log = \Monolog\Registry::omegaup()->withName('Scoreboard');
        \OmegaUp\Scoreboard::setIsLastRunFromCacheForTesting(false);
    }

    /**
     * Generate Scoreboard snapshot
     *
     * @return Scoreboard
     */
    public function generate(
        bool $withRunDetails = false,
        bool $sortByName = false,
        ?string $filterUsersBy = null
    ) {
        $cache = null;
        // A few scoreboard options are not cacheable.
        if (
            !$sortByName && is_null(
                $filterUsersBy
            ) && !$this->params->only_ac
        ) {
            if ($this->params->admin) {
                $cache = new \OmegaUp\Cache(
                    \OmegaUp\Cache::ADMIN_SCOREBOARD_PREFIX,
                    strval($this->params->problemset_id)
                );
            } else {
                $cache = new \OmegaUp\Cache(
                    \OmegaUp\Cache::CONTESTANT_SCOREBOARD_PREFIX,
                    strval($this->params->problemset_id)
                );
            }
            /** @var null|Scoreboard */
            $result = $cache->get();
            if (!is_null($result)) {
                \OmegaUp\Scoreboard::setIsLastRunFromCacheForTesting(true);
                return $result;
            }
        }

        // Ensure the problemset exists.
        $problemsetExists = \OmegaUp\DAO\Problemsets::existsByPK(
            $this->params->problemset_id
        );
        if (!$problemsetExists) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        // Get all distinct contestants participating in the given contest
        $rawContestIdentities = \OmegaUp\DAO\Runs::getAllRelevantIdentities(
            $this->params->problemset_id,
            $this->params->acl_id,
            showAllRuns: true,
            filterUsersBy: $filterUsersBy,
            groupId: $this->params->group_id,
            // Treat admin as contestant in virtual contest.
            excludeAdmin: !$this->params->virtual,
        );

        // Get all problems given problemset
        $rawProblemsetProblems =
            \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems(
                $this->params->problemset_id
            );

        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitTimestamp(
            $this->params
        );

        if ($this->params->score_mode === 'max_per_group') {
            // The way to calculate the score is different in this mode
            $contestRuns = \OmegaUp\DAO\RunsGroups::getProblemsetRunsGroups(
                $this->params->problemset_id,
                $scoreboardTimeLimit
            );
        } else {
            $contestRuns = \OmegaUp\DAO\Runs::getProblemsetRuns(
                $this->params->problemset_id,
                $this->params->only_ac
            );
        }

        /** @var array<int, array{order: int, alias: string}> */
        $problemMapping = [];

        $order = 0;
        /** @var \OmegaUp\DAO\VO\Problems $problem */
        foreach ($rawProblemsetProblems as $problem) {
            /** @var int $problem->problem_id */
            $problemMapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => strval($problem->alias),
            ];
        }

        $result = \OmegaUp\Scoreboard::getScoreboardFromRuns(
            $contestRuns,
            $rawContestIdentities,
            $problemMapping,
            $this->params->penalty,
            $this->params->penalty_calc_policy,
            $scoreboardTimeLimit,
            $this->params->title,
            $this->params->start_time,
            $this->params->finish_time,
            $this->params->admin,
            $this->params->scoreboard_pct,
            $this->params->score_mode,
            $this->params->show_scoreboard_after,
            $sortByName,
            $withRunDetails,
            $this->params->auth_token
        );

        if (!is_null($cache)) {
            $timeout =  is_null($this->params->finish_time) ?
                0 :
                max(
                    0,
                    $this->params->finish_time->time - \OmegaUp\Time::get()
                );
            $cache->set($result, $timeout);
        }

        return $result;
    }

    /**
     * Returns Scoreboard events
     *
     * @return list<ScoreboardEvent>
     */
    public function events(): array {
        $result = null;

        $contestantEventsCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX,
            strval($this->params->problemset_id)
        );
        $adminEventsCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX,
            strval($this->params->problemset_id)
        );

        $canUseContestantCache = !$this->params->admin;
        $canUseAdminCache = $this->params->admin;

        // If cache is turned on and we're not looking for admin-only runs
        if ($canUseContestantCache) {
            /** @var list<ScoreboardEvent>|null */
            $result = $contestantEventsCache->get();
        } elseif ($canUseAdminCache) {
            /** @var list<ScoreboardEvent>|null */
            $result = $adminEventsCache->get();
        }

        if (!is_null($result) && !$this->params->admin) {
            \OmegaUp\Scoreboard::setIsLastRunFromCacheForTesting(true);
            return $result;
        }

        // Ensure the problemset exists.
        $problemsetExists = \OmegaUp\DAO\Problemsets::existsByPK(
            $this->params->problemset_id
        );
        if (!$problemsetExists) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        // Get all distinct contestants participating in the given contest
        $rawContestIdentities = \OmegaUp\DAO\Runs::getAllRelevantIdentities(
            $this->params->problemset_id,
            $this->params->acl_id,
            showAllRuns: $this->params->admin,
            filterUsersBy: null,
            groupId: null,
            // Treat admin as contestant.
            excludeAdmin: !$this->params->virtual,
        );

        // Get all problems given problemset
        $rawProblemsetProblems =
            \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems(
                $this->params->problemset_id
            );

        $contestRuns = \OmegaUp\DAO\Runs::getProblemsetRuns(
            $this->params->problemset_id
        );

        $problemMapping = [];

        $order = 0;
        /** @var \OmegaUp\DAO\VO\Problems $problem */
        foreach ($rawProblemsetProblems as $problem) {
            /** @var int $problem->problem_id */
            $problemMapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => strval($problem->alias),
            ];
        }

        $result = \OmegaUp\Scoreboard::calculateEvents(
            $this->params,
            $contestRuns,
            $rawContestIdentities,
            $problemMapping
        );

        $timeout =  is_null($this->params->finish_time) ?
            0 :
            max(
                0,
                $this->params->finish_time->time - \OmegaUp\Time::get()
            );
        if ($canUseContestantCache) {
            $contestantEventsCache->set($result, $timeout);
        } elseif ($canUseAdminCache) {
            $adminEventsCache->set($result, $timeout);
        }

        return $result;
    }

    /**
     * New runs trigger a scoreboard update asynchronously, only invalidate
     * scoreboard when contest details have changed.
     *
     * @param \OmegaUp\ScoreboardParams $params
     */
    public static function invalidateScoreboardCache(\OmegaUp\ScoreboardParams $params): void {
        $log = \Monolog\Registry::omegaup()->withName('Scoreboard');
        $log->info('Invalidating scoreboard cache.');

        // Invalidar cache del contestant
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_PREFIX,
            strval($params->problemset_id)
        );
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX,
            strval($params->problemset_id)
        );

        // Invalidar cache del admin
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_PREFIX,
            strval($params->problemset_id)
        );
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX,
            strval($params->problemset_id)
        );
    }

    /**
     * Force refresh of Scoreboard caches
     */
    public static function refreshScoreboardCache(\OmegaUp\ScoreboardParams $params): void {
        $problemsetExists = \OmegaUp\DAO\Problemsets::existsByPK(
            $params->problemset_id
        );
        if (!$problemsetExists) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        // Get all distinct contestants participating in the contest
        $rawContestIdentities = \OmegaUp\DAO\Runs::getAllRelevantIdentities(
            $params->problemset_id,
            $params->acl_id,
            showAllRuns: true,
            filterUsersBy: null,
            groupId: null,
            // Treat admin as contestant in virtual contest.
            excludeAdmin: !$params->virtual,
        );

        // Get all problems given problemset
        $rawProblemsetProblems =
            \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems(
                $params->problemset_id
            );

        $problemMapping = [];

        $order = 0;
        /** @var \OmegaUp\DAO\VO\Problems $problem */
        foreach ($rawProblemsetProblems as $problem) {
            /** @var int $problem->problem_id */
            $problemMapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => strval($problem->alias),
            ];
        }

        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitTimestamp(
            $params
        );

        $contestRunsForEvents = \OmegaUp\DAO\Runs::getProblemsetRuns(
            $params->problemset_id
        );
        if ($params->score_mode === 'max_per_group') {
            // The way to calculate the score is different in this mode
            $contestRuns = \OmegaUp\DAO\RunsGroups::getProblemsetRunsGroups(
                $params->problemset_id,
                $scoreboardTimeLimit
            );
        } else {
            $contestRuns = $contestRunsForEvents;
        }

        // Cache scoreboard until the contest ends (or forever if it has already ended).
        // Contestant cache
        $timeout =  is_null($params->finish_time) ?
            0 :
            max(
                0,
                $params->finish_time->time - \OmegaUp\Time::get()
            );

        $contestantScoreboardCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_PREFIX,
            strval($params->problemset_id)
        );
        $contestantScoreboard = \OmegaUp\Scoreboard::getScoreboardFromRuns(
            $contestRuns,
            $rawContestIdentities,
            $problemMapping,
            $params->penalty,
            $params->penalty_calc_policy,
            $scoreboardTimeLimit,
            $params->title,
            $params->start_time,
            $params->finish_time,
            $params->admin,
            $params->scoreboard_pct,
            $params->score_mode,
            $params->show_scoreboard_after,
            sortByName: false,
        );

        $contestantEventCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX,
            strval($params->problemset_id)
        );
        $contestantEventCache->set(\OmegaUp\Scoreboard::calculateEvents(
            $params,
            $contestRunsForEvents,
            $rawContestIdentities,
            $problemMapping
        ), $timeout);

        // Admin cache
        $params->admin = true; // Temporarily set for admin cache
        $contestantScoreboardCache->set($contestantScoreboard, $timeout);
        $adminScoreboardCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_PREFIX,
            strval($params->problemset_id)
        );
        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitTimestamp(
            $params
        );
        $adminScoreboard = \OmegaUp\Scoreboard::getScoreboardFromRuns(
            $contestRuns,
            $rawContestIdentities,
            $problemMapping,
            $params->penalty,
            $params->penalty_calc_policy,
            $scoreboardTimeLimit,
            $params->title,
            $params->start_time,
            $params->finish_time,
            $params->admin,
            $params->scoreboard_pct,
            $params->score_mode,
            $params->show_scoreboard_after,
            sortByName: false,
        );
        $adminScoreboardCache->set($adminScoreboard, $timeout);
        $params->admin = false;

        $adminEventCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX,
            strval($params->problemset_id)
        );
        $adminEventCache->set(\OmegaUp\Scoreboard::calculateEvents(
            $params,
            $contestRunsForEvents,
            $rawContestIdentities,
            $problemMapping
        ), $timeout);

        // Try to broadcast the updated scoreboards:
        $log = \Monolog\Registry::omegaup()->withName('Scoreboard');
        try {
            $log->debug('Sending updated scoreboards');
            \OmegaUp\Grader::getInstance()->broadcast(
                $params->alias,
                intval($params->problemset_id),
                null,
                json_encode([
                    'message' => '/scoreboard/update/',
                    'scoreboard_type' => 'contestant',
                    'scoreboard' => $contestantScoreboard
                ]),
                true,  // public
                null,  // username
                -1,  // user_id
                true  // user_only
            );
            \OmegaUp\Grader::getInstance()->broadcast(
                $params->alias,
                intval($params->problemset_id),
                null,
                json_encode([
                    'message' => '/scoreboard/update/',
                    'scoreboard_type' => 'admin',
                    'scoreboard' => $adminScoreboard
                ]),
                false,  // public
                null,  // username
                -1,  // user_id
                false  // user_only
            );
        } catch (\Exception $e) {
            $log->error('Error broadcasting scoreboard', ['exception' => $e]);
        }
    }

    /**
     * getScoreboardTimeLimitTimestamp
     * Returns the max timestamp to consider for the given problemset
     */
    private static function getScoreboardTimeLimitTimestamp(
        \OmegaUp\ScoreboardParams $params
    ): ?\OmegaUp\Timestamp {
        if (
            $params->admin
            || is_null($params->finish_time)
            || (
                (\OmegaUp\Time::get() >= $params->finish_time->time)
                && $params->show_scoreboard_after
            )
        ) {
            // Show full scoreboard to admin users
            // or if the contest finished and the creator wants to show it at the end
            return null;
        }

        $start = $params->start_time->time;
        $finish = $params->finish_time->time;

        $percentage = floatval($params->scoreboard_pct) / 100.0;

        return new \OmegaUp\Timestamp(
            $start + intval(($finish - $start) * $percentage)
        );
    }

    /**
     * @param list<array{alias: string, points: float, penalty: float, percent: float, runs: int}> $scores
     * @param string $contestPenaltyCalcPolicy
     * @return array{points: float, penalty: float}
     */
    private static function getTotalScore(
        $scores,
        string $contestPenaltyCalcPolicy
    ): array {
        $totalPoints = 0.0;
        $totalPenalty = 0.0;
        // Get final scores
        foreach ($scores as $score) {
            $totalPoints += $score['points'];
            if ($contestPenaltyCalcPolicy == 'sum') {
                $totalPenalty += $score['penalty'];
            } else {
                $totalPenalty = max($totalPenalty, $score['penalty']);
            }
        }

        return [
            'points' => $totalPoints,
            'penalty' => $totalPenalty,
        ];
    }

    /**
     * @param list<array{contest_score: float, guid?: string, identity_id: int, penalty: float|int, problem_id: int, score: float, submit_delay?: int, submission_count?: int, time?: \OmegaUp\Timestamp, type: string}> $contestRuns
     * @param list<array{identity_id: int, username: string, name: string|null, country_id: null|string, is_invited: bool, classname: string}> $rawContestIdentities
     * @param array<int, array{order: int, alias: string}> $problemMapping
     * @param int $contestPenalty
     * @param string $contestPenaltyCalcPolicy
     * @param string $contestTitle
     * @param bool $showAllRuns
     * @param bool $sortByName
     * @param bool $withRunDetails
     * @param null|string $authToken
     *
     * @return Scoreboard
     */
    private static function getScoreboardFromRuns(
        array $contestRuns,
        array $rawContestIdentities,
        array $problemMapping,
        int $contestPenalty,
        string $contestPenaltyCalcPolicy,
        ?\OmegaUp\Timestamp $scoreboardTimeLimit,
        string $contestTitle,
        \OmegaUp\Timestamp $contestStartTime,
        ?\OmegaUp\Timestamp $contestFinishTime,
        bool $showAllRuns,
        int $scoreboardPct,
        string $scoreMode,
        bool $showScoreboardAfter,
        bool $sortByName,
        bool $withRunDetails = false,
        ?string $authToken = null
    ): array {
        /** @val array<int, bool> */
        $testOnly = [];
        /** @val array<int, bool> */
        $noRuns = [];
        /** @val array<int, array{problems: list<array{alias: string, points: float, penalty: float, percent: float, runs: int}>, username: string, name: string|null, country: string, is_invited: bool, total: array{points: float, penalty: float}}> */
        $identitiesInfo = [];

        $problems = [];
        foreach ($problemMapping as $problem) {
            $problems[] = $problem;
        }

        // Calculate score for each contestant x problem
        foreach ($rawContestIdentities as $contestant) {
            /** @var list<array{alias: string, points: float, penalty: float, percent: float, runs: int}> */
            $identityProblems = [];

            $testOnly[$contestant['identity_id']] = true;
            $noRuns[$contestant['identity_id']] = true;
            foreach ($problemMapping as $_id => $problem) {
                $identityProblems[] = [
                    'alias' => $problem['alias'],
                    'points' => 0.0,
                    'percent' => 0.0,
                    'penalty' => 0.0,
                    'runs' => 0
                ];
            }

            // Add the problems' information
            $identitiesInfo[$contestant['identity_id']] = [
                'problems' => $identityProblems,
                'username' => $contestant['username'],
                'name' => $contestant['name'] ?
                    $contestant['name'] :
                    $contestant['username'],
                'country' => $contestant['country_id'] ?? 'xx',
                'classname' => $contestant['classname'],
                'is_invited' => boolval($contestant['is_invited']),
                self::TOTAL_COLUMN => [
                    'points' => 0.0,
                    'penalty' => 0.0,
                ],
            ];
        }

        foreach ($contestRuns as $run) {
            $identityId = $run['identity_id'];
            $problemId = $run['problem_id'];
            $contestScore = $run['contest_score'];
            $score = $run['score'];
            $isTest = $run['type'] == 'test';

            $problem =&
                $identitiesInfo[$identityId]['problems'][$problemMapping[$problemId]['order']];

            if (!array_key_exists($identityId, $testOnly)) {
                // Hay un usuario en la lista de Runs,
                // que no fue regresado por \OmegaUp\DAO\Runs::getAllRelevantIdentities()
                continue;
            }

            if ($testOnly[$identityId]) {
                $testOnly[$identityId] = $isTest;
            }
            $noRuns[$identityId] = false;
            if (!$showAllRuns) {
                if ($isTest || ($scoreboardPct === 0 && !$showScoreboardAfter)) {
                    continue;
                }
                if (
                    !is_null($scoreboardTimeLimit)
                    && !empty($run['time'])
                    && $run['time']->time >= $scoreboardTimeLimit->time
                ) {
                    $problem['runs']++;
                    $problem['pending'] = true;
                    continue;
                }
            }

            $totalPenalty = $run['penalty'] + $problem['runs'] * $contestPenalty;
            $roundedScore = round(floatval($contestScore), 2);
            if (
                $problem['points'] < $roundedScore ||
                $problem['points'] == $roundedScore &&
                $problem['penalty'] > $totalPenalty
            ) {
                $problem['points'] = $roundedScore;
                $problem['percent'] = round($score * 100, 2);
                $problem['penalty'] = $totalPenalty;

                if ($withRunDetails === true && !empty($run['guid'])) {
                    $runDetails = [];

                    $runDetailsRequest = new \OmegaUp\Request([
                        'run_alias' => $run['guid'],
                        'auth_token' => $authToken,
                    ]);
                    $runDetails = \OmegaUp\Controllers\Run::apiDetails(
                        $runDetailsRequest
                    );
                    unset($runDetails['source']);
                    $problem['run_details'] = $runDetails;
                }
            }
            if ($scoreMode == 'max_per_group') {
                $problem['runs'] = $run['submission_count'] ?? 0;
            } else {
                $problem['runs']++;
            }
        }

        /** @var list<ScoreboardRankingEntry> */
        $ranking = [];
        foreach ($rawContestIdentities as $contestant) {
            $identityId = $contestant['identity_id'];

            // Add contestant results to scoreboard data
            if (
                !$showAllRuns &&
                boolval($testOnly[$identityId]) &&
                !$noRuns[$identityId]
            ) {
                continue;
            }
            if (!array_key_exists($identityId, $identitiesInfo)) {
                continue;
            }
            $info = $identitiesInfo[$identityId];
            $info[self::TOTAL_COLUMN] = \OmegaUp\Scoreboard::getTotalScore(
                $info['problems'],
                $contestPenaltyCalcPolicy
            );
            $ranking[] = $info;
        }

        \OmegaUp\Scoreboard::sortScoreboard($ranking, $sortByName);

        usort(
            $problems,
            /**
             * @param array{order: int, alias: string} $a
             * @param array{order: int, alias: string} $b
             */
            fn (array $a, array $b) => $a['order'] - $b['order']
        );

        return [
            'problems' => $problems,
            'ranking' => $ranking,
            'start_time' => $contestStartTime,
            'finish_time' => $contestFinishTime,
            'title' => $contestTitle,
            'time' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
        ];
    }

    /**
     * @param list<ScoreboardRankingEntry> $scoreboard
     * @param bool $sortByName
     */
    private static function sortScoreboard(
        array &$scoreboard,
        bool $sortByName = false
    ): void {
        if ($sortByName == false) {
            // Sort users by their total column
            usort(
                $scoreboard,
                /**
                 * @param ScoreboardRankingEntry $a
                 * @param ScoreboardRankingEntry $b
                 */
                function (array $a, array $b): int {
                    if ($a[self::TOTAL_COLUMN]['points'] != $b[self::TOTAL_COLUMN]['points']) {
                        return ($a[self::TOTAL_COLUMN]['points'] < $b[self::TOTAL_COLUMN]['points']) ? 1 : -1;
                    }
                    if ($a[self::TOTAL_COLUMN]['penalty'] != $b[self::TOTAL_COLUMN]['penalty']) {
                        return ($a[self::TOTAL_COLUMN]['penalty'] > $b[self::TOTAL_COLUMN]['penalty']) ? 1 : -1;
                    }
                    return strcasecmp($a['username'], $b['username']);
                }
            );
        } else {
            // Sort users by their name
            usort(
                $scoreboard,
                /**
                 * @param ScoreboardRankingEntry $a
                 * @param ScoreboardRankingEntry $b
                 */
                fn (array $a, array $b) => strcasecmp(
                    $a['username'],
                    $b['username']
                )
            );
        }

        // Append the place for each user
        $currentPoints = -1;
        $currentPenalty = -1;
        $place = 1;
        $draws = 1;
        foreach ($scoreboard as &$userData) {
            if ($currentPoints === -1) {
                $currentPoints = $userData['total']['points'];
                $currentPenalty = $userData['total']['penalty'];
            } else {
                // If not in draw
                if (
                    $userData['total']['points'] < $currentPoints ||
                    $userData['total']['penalty'] > $currentPenalty
                ) {
                    $currentPoints = $userData['total']['points'];
                    $currentPenalty = $userData['total']['penalty'];

                    $place += $draws;
                    $draws = 1;
                } elseif (
                    $userData['total']['points'] == $currentPoints &&
                           $userData['total']['penalty'] == $currentPenalty
                ) {
                    $draws++;
                }
            }

            if (!$sortByName) {
                // Set the place for the current user
                $userData['place'] = $place;
            }
        }
    }

    /**
     * @param \OmegaUp\ScoreboardParams $params
     * @param list<array{contest_score: float, guid: string, identity_id: int, penalty: int, problem_id: int, score: float, score_by_group: null|string, submit_delay: int, time: \OmegaUp\Timestamp, type: string}> $contestRuns
     * @param list<array{identity_id: int, username: string, classname: string, name: string|null, country_id: null|string, is_invited: bool}> $rawContestIdentities
     * @param array<int, array{order: int, alias: string}> $problemMapping
     * @return list<ScoreboardEvent>
     */
    private static function calculateEvents(
        \OmegaUp\ScoreboardParams $params,
        array $contestRuns,
        array $rawContestIdentities,
        array $problemMapping
    ): array {
        /** @var array<int, array{identity_id: int, username: string, classname: string, name: string|null, country_id: null|string, is_invited: bool}> */
        $contestIdentities = [];

        foreach ($rawContestIdentities as $identity) {
            $contestIdentities[$identity['identity_id']] = $identity;
        }

        /** @var list<ScoreboardEvent> */
        $result = [];
        /** @var array<int, array<int, array{points: int, penalty: int}>> */
        $identityProblemsScore = [];
        $identityProblemsScoreByGroup = [];
        $contestStart = $params->start_time;
        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitTimestamp(
            $params
        );

        // Calculate score for each contestant x problem x run
        foreach ($contestRuns as $run) {
            if (!$params->admin && $run['type'] != 'normal') {
                continue;
            }

            if (
                !is_null($scoreboardTimeLimit)
                && !empty($run['time'])
                && $run['time']->time >= $scoreboardTimeLimit->time
            ) {
                continue;
            }

            $identityId = $run['identity_id'];
            $problemId = $run['problem_id'];
            if ($params->score_mode === 'max_per_group') {
                if (!isset($run['score_by_group'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'score_by_group'
                    );
                }
                $contestScore = self::getMaxPerGroupScore(
                    $identityProblemsScoreByGroup,
                    $run['score_by_group'],
                    $identityId,
                    $problemId
                );
            } else {
                $contestScore = $run['contest_score'];
            }

            if (!isset($identityProblemsScore[$identityId])) {
                $identityProblemsScore[$identityId] = [
                    $problemId => [
                        'points' => 0.0,
                        'penalty' => 0.0,
                    ],
                ];
            } elseif (!isset($identityProblemsScore[$identityId][$problemId])) {
                $identityProblemsScore[$identityId][$problemId] = [
                    'points' => 0.0,
                    'penalty' => 0.0,
                ];
            }

            $problemData =& $identityProblemsScore[$identityId][$problemId];

            if ($problemData['points'] >= $contestScore && $params->show_all_runs) {
                continue;
            }

            $problemData['points'] = max(
                $problemData['points'],
                round(floatval($contestScore), 2)
            );
            $problemData['penalty'] = 0.0;

            if (!isset($contestIdentities[$identityId])) {
                continue;
            }

            $identity =& $contestIdentities[$identityId];

            $data = [
                'name' => $identity['name'] ?? $identity['username'],
                'username' => $identity['username'],
                'delta' => max(
                    0.0,
                    ($run['time']->time - $contestStart->time) / 60.0
                ),
                'problem' => [
                    'alias' => $problemMapping[$problemId]['alias'],
                    'points' => round(floatval($contestScore), 2),
                    'penalty' => 0.0,
                ],
                'country' => $identity['country_id'] ?? 'xx',
                'classname' => $identity['classname'],
                'is_invited' => boolval($identity['is_invited']),
                self::TOTAL_COLUMN => [
                    'points' => 0.0,
                    'penalty' => 0.0,
                ],
            ];

            foreach ($identityProblemsScore[$identityId] as $problem) {
                $data['total']['points'] += $problem['points'];
                if ($params->penalty_calc_policy == 'sum') {
                    $data['total']['penalty'] += $problem['penalty'];
                } else {
                    $data['total']['penalty'] = max(
                        $data['total']['penalty'],
                        $problem['penalty']
                    );
                }
            }

            // Add contestant results to scoreboard data
            $result[] = $data;
        }

        return $result;
    }

    /**
     * Set last run from cache for testing purposes
     */
    private function setIsLastRunFromCacheForTesting(bool $value): void {
        if (!\OmegaUp\Scoreboard::$isTestRun) {
            return;
        }
        \OmegaUp\Scoreboard::$isLastRunFromCache = $value;
    }

    /**
     * @param list<list<array<string, float>>>
     * Get the max score when a contest has subtasks updated to the given
     * $scoreByGroup.
     */
    private static function getMaxPerGroupScore(
        array &$identityProblemsScoreByGroup,
        ?string $scoreByGroup,
        int $identityId,
        int $problemId
    ): float {
        if (is_null($scoreByGroup)) {
            return 0.0;
        }
        $scoreByGroupArray = json_decode($scoreByGroup, associative: true);

        if (!is_array($scoreByGroupArray)) {
            throw new \RuntimeException(
                'json_decode failed with: ' . json_last_error() . "for : {$scoreByGroup}"
            );
        }

        $groupNames = array_keys($scoreByGroupArray);

        if (!isset($identityProblemsScoreByGroup[$identityId])) {
            $identityProblemsScoreByGroup[$identityId] = [
                $problemId => $scoreByGroupArray,
            ];
        } elseif (
            !isset(
                $identityProblemsScoreByGroup[$identityId][$problemId]
            )
        ) {
            $identityProblemsScoreByGroup[$identityId][$problemId] = $scoreByGroupArray;
        }

        foreach ($groupNames as $groupName) {
            $identityProblemsScoreByGroup[$identityId][$problemId][$groupName] = max(
                $scoreByGroupArray[$groupName],
                $identityProblemsScoreByGroup[$identityId][$problemId][$groupName]
            );
        }

        return array_sum(
            $identityProblemsScoreByGroup[$identityId][$problemId]
        );
    }

    /**
     * Get last run from Cache value
     */
    public static function getIsLastRunFromCacheForTesting(): bool {
        return \OmegaUp\Scoreboard::$isLastRunFromCache;
    }

    /**
     * Enable testing extras
     */
    public static function setIsTestRunForTesting(bool $value): void {
        \OmegaUp\Scoreboard::$isTestRun = $value;
    }
}
