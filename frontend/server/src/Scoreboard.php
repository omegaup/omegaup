<?php

namespace OmegaUp;

/**
 *  Scoreboard
 *
 * @author alanboy
 * @author pablo.aguilar
 * @author lhchavez
 * @author joemmanuel
 */
class Scoreboard {
    // Column to return total score per user
    const TOTAL_COLUMN = 'total';

    /** @var \OmegaUp\ScoreboardParams */
    private $params;

    /** @var \Logger */
    public $log;

    /** @var bool */
    private static $isTestRun = false;

    /** @var bool */
    private static $isLastRunFromCache = false;

    public function __construct(\OmegaUp\ScoreboardParams $params) {
        $this->params = $params;
        $this->log = \Logger::getLogger('Scoreboard');
        \OmegaUp\Scoreboard::setIsLastRunFromCacheForTesting(false);
    }

    /**
     * Generate Scoreboard snapshot
     *
     * @return array{status: string, problems: array{order: int, alias: string}[], ranking: array{problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], username: string, name: string, country: null|string, is_invited: bool, total: array{points: float, penalty: float}}[], start_time: int, finish_time: int, title: string, time: int}
     */
    public function generate(
        bool $withRunDetails = false,
        bool $sortByName = false,
        ?string $filterUsersBy = null
    ): array {
        $cache = null;
        // A few scoreboard options are not cacheable.
        if (
            !$sortByName &&
            is_null($filterUsersBy) &&
            !$this->params->only_ac
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
            /** @var null|array{status: string, problems: array{order: int, alias: string}[], ranking: array{problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], username: string, name: string, country: null|string, is_invited: bool, total: array{points: float, penalty: float}}[], start_time: int, finish_time: int, title: string, time: int} */
            $result = $cache->get();
            if (!is_null($result)) {
                \OmegaUp\Scoreboard::setIsLastRunFromCacheForTesting(true);
                return $result;
            }
        }

        // Get all distinct contestants participating in the given contest
        $rawContestIdentities = \OmegaUp\DAO\Runs::getAllRelevantIdentities(
            $this->params->problemset_id,
            $this->params->acl_id,
            true /* show all runs */,
            $filterUsersBy,
            $this->params->group_id,
            !$this->params->virtual /* Treat admin as contestant in virtual contest*/
        );

        // Get all problems given problemset
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $this->params->problemset_id
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        $rawProblemsetProblems =
            \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems($problemset);

        $contestRuns = \OmegaUp\DAO\Runs::getProblemsetRuns(
            $problemset,
            $this->params->only_ac
        );

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

        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitUnixTimestamp(
            $this->params
        );

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
            $sortByName,
            $withRunDetails,
            $this->params->auth_token
        );

        if (!is_null($cache)) {
            $timeout = max(
                0,
                $this->params->finish_time - \OmegaUp\Time::get()
            );
            $cache->set($result, $timeout);
        }

        return $result;
    }

    /**
     * Returns Scoreboard events
     *
     * @return array{country: null|string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: string, username: string, problem: array{alias: string, points: float, penalty: float}}[]
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
            /** @var array{country: null|string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: string, username: string, problem: array{alias: string, points: float, penalty: float}}[] */
            $result = $contestantEventsCache->get();
        } elseif ($canUseAdminCache) {
            /** @var array{country: null|string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: string, username: string, problem: array{alias: string, points: float, penalty: float}}[] */
            $result = $adminEventsCache->get();
        }

        if (!is_null($result)) {
            \OmegaUp\Scoreboard::setIsLastRunFromCacheForTesting(true);
            return $result;
        }

        // Get all distinct contestants participating in the given contest
        $rawContestIdentities = \OmegaUp\DAO\Runs::getAllRelevantIdentities(
            $this->params->problemset_id,
            $this->params->acl_id,
            $this->params->admin,
            null,
            null,
            !$this->params->virtual /* Treat admin as contestant */
        );

        // Get all problems given problemset
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $this->params->problemset_id
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        $rawProblemsetProblems =
            \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems($problemset);

        $contestRuns = \OmegaUp\DAO\Runs::getProblemsetRuns($problemset);

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

        $timeout = max(0, $this->params->finish_time - \OmegaUp\Time::get());
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
        $log = \Logger::getLogger('Scoreboard');
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
        $problemset = \OmegaUp\DAO\Problemsets::getByPK($params->problemset_id);
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        $contestRuns = \OmegaUp\DAO\Runs::getProblemsetRuns($problemset);

        // Get all distinct contestants participating in the contest
        $rawContestIdentities = \OmegaUp\DAO\Runs::getAllRelevantIdentities(
            $params->problemset_id,
            $params->acl_id,
            true /* show all runs */,
            null,
            null,
            !$params->virtual /* Treat admin as contestant in virtual contest */
        );

        // Get all problems given problemset
        $rawProblemsetProblems =
            \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems($problemset);

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

        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitUnixTimestamp(
            $params
        );

        // Cache scoreboard until the contest ends (or forever if it has already ended).
        // Contestant cache
        $timeout = max(0, $params->finish_time - \OmegaUp\Time::get());
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
            false  /* sortByName */
        );

        $contestantEventCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX,
            strval($params->problemset_id)
        );
        $contestantEventCache->set(\OmegaUp\Scoreboard::calculateEvents(
            $params,
            $contestRuns,
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
        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitUnixTimestamp(
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
            false /* sortByName */
        );
        $adminScoreboardCache->set($adminScoreboard, $timeout);
        $params->admin = false;

        $adminEventCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX,
            strval($params->problemset_id)
        );
        $adminEventCache->set(\OmegaUp\Scoreboard::calculateEvents(
            $params,
            $contestRuns,
            $rawContestIdentities,
            $problemMapping
        ), $timeout);

        // Try to broadcast the updated scoreboards:
        $log = \Logger::getLogger('Scoreboard');
        try {
            $log->debug('Sending updated scoreboards');
            \OmegaUp\Grader::getInstance()->broadcast(
                $params->alias,
                intval($problemset->problemset_id),
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
                intval($problemset->problemset_id),
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
            $log->error('Error broadcasting scoreboard', $e);
        }
    }

    /**
     * getScoreboardTimeLimitUnixTimestamp
     * Returns the max timestamp to consider for the given problemset
     */
    private static function getScoreboardTimeLimitUnixTimestamp(
        \OmegaUp\ScoreboardParams $params
    ): ?int {
        if (
            $params->admin
            || ((\OmegaUp\Time::get() >= $params->finish_time)
            && $params->show_scoreboard_after)
        ) {
            // Show full scoreboard to admin users
            // or if the contest finished and the creator wants to show it at the end
            return null;
        }

        $start = $params->start_time;
        $finish = $params->finish_time;

        $percentage = floatval($params->scoreboard_pct) / 100.0;

        $limit = $start + intval(($finish - $start) * $percentage);

        return $limit;
    }

    /**
     * @param array{alias: string, points: float, penalty: float, percent: float, runs: int}[] $scores
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
     * @param array{score: float, penalty: int, contest_score: float, problem_id: int, identity_id: int, type: string, time: int, submit_delay: int, guid: string}[] $contestRuns
     * @param array{identity_id: int, username: string, name: string, country_id: null|string, is_invited: bool}[] $rawContestIdentities
     * @param array<int, array{order: int, alias: string}> $problemMapping
     * @param int $contestPenalty
     * @param string $contestPenaltyCalcPolicy
     * @param null|int $scoreboardTimeLimit
     * @param string $contestTitle
     * @param int $contestStartTime
     * @param int $contestFinishTime
     * @param bool $showAllRuns
     * @param bool $sortByName
     * @param bool $withRunDetails
     * @param null|string $authToken
     * @return array{status: string, problems: array{order: int, alias: string}[], ranking: array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}}[], start_time: int, finish_time: int, title: string, time: int}
     */
    private static function getScoreboardFromRuns(
        array $contestRuns,
        array $rawContestIdentities,
        array $problemMapping,
        int $contestPenalty,
        string $contestPenaltyCalcPolicy,
        ?int $scoreboardTimeLimit,
        string $contestTitle,
        int $contestStartTime,
        int $contestFinishTime,
        bool $showAllRuns,
        bool $sortByName,
        bool $withRunDetails = false,
        ?string $authToken = null
    ): array {
        /** @val array<int, bool> */
        $testOnly = [];
        /** @val array<int, bool> */
        $noRuns = [];
        /** @val array<int, array{problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], username: string, name: string, country: null|string, is_invited: bool, total: array{points: float, penalty: float}}> */
        $identitiesInfo = [];
        /** @val array{order: int, alias: string}[] */
        $problems = [];

        foreach ($problemMapping as $problem) {
            array_push($problems, $problem);
        }

        // Calculate score for each contestant x problem
        foreach ($rawContestIdentities as $contestant) {
            /** @var array{alias: string, points: float, penalty: float, percent: float, runs: int}[] */
            $identityProblems = [];

            $testOnly[$contestant['identity_id']] = true;
            $noRuns[$contestant['identity_id']] = true;
            foreach ($problemMapping as $id => $problem) {
                array_push($identityProblems, [
                    'alias' => $problem['alias'],
                    'points' => 0.0,
                    'percent' => 0.0,
                    'penalty' => 0.0,
                    'runs' => 0
                ]);
            }

            // Add the problems' information
            $identitiesInfo[$contestant['identity_id']] = [
                'problems' => $identityProblems,
                'username' => $contestant['username'],
                'name' => $contestant['name'] ?
                    $contestant['name'] :
                    $contestant['username'],
                'country' => $contestant['country_id'],
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

            $testOnly[$identityId] &= $isTest;
            $noRuns[$identityId] = false;
            if (!$showAllRuns) {
                if ($isTest) {
                    continue;
                }
                if (
                    !is_null($scoreboardTimeLimit)
                    && $run['time'] >= $scoreboardTimeLimit
                ) {
                    $problem['runs']++;
                    $problem['pending'] = true;
                    continue;
                }
            }

            $totalPenalty = $run['penalty'] + $problem['runs'] * $contestPenalty;
            $roundedScore = round($contestScore, 2);
            if (
                $problem['points'] < $roundedScore ||
                $problem['points'] == $roundedScore &&
                $problem['penalty'] > $totalPenalty
            ) {
                $problem['points'] = $roundedScore;
                $problem['percent'] = round($score * 100, 2);
                $problem['penalty'] = $totalPenalty;

                if ($withRunDetails === true) {
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
            $problem['runs']++;
        }

        /** @val array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}}[] */
        $result = [];
        foreach ($rawContestIdentities as $contestant) {
            $identityId = $contestant['identity_id'];

            // Add contestant results to scoreboard data
            if (!$showAllRuns && $testOnly[$identityId] && !$noRuns[$identityId]) {
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
            array_push($result, $info);
        }

        \OmegaUp\Scoreboard::sortScoreboard($result, $sortByName);

        usort(
            $problems,
            /**
             * @param array{order: int, alias: string} $a
             * @param array{order: int, alias: string} $b
             */
            function (array $a, array $b): int {
                return $a['order'] - $b['order'];
            }
        );

        return [
            'status' => 'ok',
            'problems' => $problems,
            'ranking' => $result,
            'start_time' => $contestStartTime,
            'finish_time' => $contestFinishTime,
            'title' => $contestTitle,
            'time' => \OmegaUp\Time::get() * 1000
        ];
    }

    /**
     * @param array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}}[] $scoreboard
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
                 * @param array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}} $a
                 * @param array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}} $b
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
                 * @param array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}} $a
                 * @param array{username: string, name: string, country: null|string, is_invited: bool, place?: int, problems: array{alias: string, points: float, penalty: float, percent: float, runs: int}[], total: array{points: float, penalty: float}} $b
                 */
                function (array $a, array $b): int {
                    return strcasecmp($a['username'], $b['username']);
                }
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
     * @param array{score: float, penalty: int, contest_score: float, problem_id: int, identity_id: int, type: string, time: int, submit_delay: int, guid: string}[] $contestRuns
     * @param array{identity_id: int, username: string, name: string, country_id: null|string, is_invited: bool}[] $rawContestIdentities
     * @param array<int, array{order: int, alias: string}> $problemMapping
     * @return array{country: null|string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: string, username: string, problem: array{alias: string, points: float, penalty: float}}[]
     */
    private static function calculateEvents(
        \OmegaUp\ScoreboardParams $params,
        array $contestRuns,
        array $rawContestIdentities,
        array $problemMapping
    ): array {
        /** @var array<int, array{identity_id: int, username: string, name: string, country_id: null|string, is_invited: bool}> */
        $contestIdentities = [];

        foreach ($rawContestIdentities as $identity) {
            $contestIdentities[$identity['identity_id']] = $identity;
        }

        /** @var array{country: null|string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: string, username: string, problem: array{alias: string, points: float, penalty: float}}[] */
        $result = [];
        /** @var array<int, array<int, array{points: int, penalty: int}>> */
        $identityProblemsScore = [];
        $contestStart = $params->start_time;
        $scoreboardTimeLimit = \OmegaUp\Scoreboard::getScoreboardTimeLimitUnixTimestamp(
            $params
        );

        // Calculate score for each contestant x problem x run
        foreach ($contestRuns as $run) {
            if (!$params->admin && $run['type'] != 'normal') {
                continue;
            }

            $log = \Logger::getLogger('Scoreboard');
            $runDelay = $run['time'];
            $log->debug(">>      run_delay : $runDelay");
            $log->debug(">>scoreboardLimit : $scoreboardTimeLimit");
            $log->debug('');

            if (
                !is_null($scoreboardTimeLimit) &&
                $runDelay >= $scoreboardTimeLimit
            ) {
                continue;
            }

            $identityId = $run['identity_id'];
            $problemId = $run['problem_id'];
            $contestScore = $run['contest_score'];

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

            if ($problemData['points'] >= $contestScore and $params->show_all_runs) {
                continue;
            }

            $problemData['points'] = max(
                $problemData['points'],
                round(
                    floatval(
                        $contestScore
                    ),
                    2
                )
            );
            $problemData['penalty'] = 0.0;

            if (!isset($contestIdentities[$identityId])) {
                continue;
            }

            $identity =& $contestIdentities[$identityId];

            $data = [
                'name' => $identity['name'] ?? $identity['username'],
                'username' => $identity['username'],
                'delta' => max(0.0, ($runDelay - $contestStart) / 60.0),
                'problem' => [
                    'alias' => $problemMapping[$problemId]['alias'],
                    'points' => round($contestScore, 2),
                    'penalty' => 0.0,
                ],
                'country' => $identity['country_id'],
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
            array_push($result, $data);
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
