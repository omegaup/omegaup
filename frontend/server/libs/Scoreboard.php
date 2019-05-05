<?php

/**
 * ScoreboardParams
 *
 * @author joemmanuel
 */
class ScoreboardParams implements ArrayAccess {
    private $params;

    public function __construct(array $params) {
        ScoreboardParams::validateParameter('alias', $params, true /*is_required*/);
        ScoreboardParams::validateParameter('title', $params, true /*is_required*/);
        ScoreboardParams::validateParameter('problemset_id', $params, true /*is_required*/);
        ScoreboardParams::validateParameter('start_time', $params, true /*is_required*/);
        ScoreboardParams::validateParameter('finish_time', $params, true /*is_required*/);
        ScoreboardParams::validateParameter('acl_id', $params, true /*is_required*/);
        ScoreboardParams::validateParameter('group_id', $params, false /*is_required*/, null);
        ScoreboardParams::validateParameter('penalty', $params, false /*is_required*/, 0);
        ScoreboardParams::validateParameter('virtual', $params, false /*is_required */, false);
        ScoreboardParams::validateParameter('penalty_calc_policy', $params, false /*is_required*/, 'sum');
        ScoreboardParams::validateParameter('show_scoreboard_after', $params, false /*is_required*/, 1);
        ScoreboardParams::validateParameter('scoreboard_pct', $params, false /*is_required*/, 100);
        ScoreboardParams::validateParameter('admin', $params, false /*is_required*/, false);
        ScoreboardParams::validateParameter('auth_token', $params, false /*is_required*/, null);
        ScoreboardParams::validateParameter('only_ac', $params, false /*is_required*/, false);
        ScoreboardParams::validateParameter('show_all_runs', $params, false /*is_required*/, true);

        // Convert any string dates into timestamps.
        foreach (['start_time', 'finish_time'] as $timeParam) {
            if (is_string($params[$timeParam])) {
                $params[$timeParam] = strtotime($params[$timeParam]);
            }
        }

        $this->params = $params;
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    public static function fromContest(Contests $contest) {
        return new ScoreboardParams([
            'alias' => $contest->alias,
            'title' => $contest->title,
            'problemset_id' => $contest->problemset_id,
            'start_time' => $contest->start_time,
            'finish_time' => $contest->finish_time,
            'acl_id' => $contest->acl_id,
            'penalty' => $contest->penalty,
            'virtual' => ContestsDAO::isVirtual($contest),
            'penalty_calc_policy' => $contest->penalty_calc_policy,
            'show_scoreboard_after' => $contest->show_scoreboard_after,
            'scoreboard_pct' => $contest->scoreboard
        ]);
    }

    public static function fromAssignment(Assignments $assignment, $groupId, $showAllRuns) {
        return new ScoreboardParams([
            'alias' => $assignment->alias,
            'title' => $assignment->name,
            'problemset_id' => $assignment->problemset_id,
            'start_time' => $assignment->start_time,
            'finish_time' => $assignment->finish_time,
            'acl_id' => $assignment->acl_id,
            'group_id' => $groupId,
            'show_all_runs' => $showAllRuns,
        ]);
    }

    /**
     * Checks if array contains a key defined by $parameter
     * @param  string  $parameter
     * @param  array   $array
     * @param  boolean $required
     * @param    $default
     * @return boolean
     * @throws InvalidParameterException
     */
    private static function validateParameter($parameter, array& $array, $required = true, $default = null) {
        if (!isset($array[$parameter])) {
            if ($required) {
                throw new InvalidParameterException('parameterEmpty', $parameter);
            }

            $array[$parameter] = $default;
        }

        return true;
    }
}

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

    private $params;
    public $log;
    private static $isTestRun = false;
    private static $isLastRunFromCache = false;

    public function __construct(ScoreboardParams $params) {
        $this->params = $params;
        $this->log = Logger::getLogger('Scoreboard');
        Scoreboard::setIsLastRunFromCacheForTesting(false);
    }

    /**
     * Generate Scoreboard snapshot
     * @param  boolean $withRunDetails
     * @param  boolean $sortByName
     * @param  string  $filterUsersBy
     * @return array
     */
    public function generate($withRunDetails = false, $sortByName = false, $filterUsersBy = null) {
        $result = null;

        $contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $this->params['problemset_id']);
        $adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $this->params['problemset_id']);

        $canUseContestantCache = !$this->params['admin'] &&
            !$sortByName &&
            is_null($filterUsersBy) &&
            !$this->params['only_ac'];

        $canUseAdminCache = $this->params['admin'] &&
            !$sortByName &&
            is_null($filterUsersBy) &&
            !$this->params['only_ac'];

        // If cache is turned on and we're not looking for admin-only runs
        if ($canUseContestantCache) {
            $result = $contestantScoreboardCache->get();
        } elseif ($canUseAdminCache) {
            $result = $adminScoreboardCache->get();
        }

        if (!is_null($result)) {
            Scoreboard::setIsLastRunFromCacheForTesting(true);
            return $result;
        }

        try {
            // Get all distinct contestants participating in the given contest
            $rawContestIdentities = RunsDAO::getAllRelevantIdentities(
                (int)$this->params['problemset_id'],
                (int)$this->params['acl_id'],
                true /* show all runs */,
                $filterUsersBy,
                empty($this->params['group_id']) ? null : (int)$this->params['group_id'],
                !$this->params['virtual'] /* Treat admin as contestant in virtual contest*/
            );

            // Get all problems given problemset
            $problemset = ProblemsetsDAO::getByPK($this->params['problemset_id']);
            $rawProblemsetProblems =
                ProblemsetProblemsDAO::getRelevantProblems($problemset);

            $contestRuns = RunsDAO::getProblemsetRuns(
                $problemset,
                $this->params['only_ac']
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $problemMapping = [];

        $order = 0;
        foreach ($rawProblemsetProblems as $problem) {
            $problemMapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => $problem->alias
            ];
        }

        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($this->params);

        $result = Scoreboard::getScoreboardFromRuns(
            $contestRuns,
            $rawContestIdentities,
            $problemMapping,
            $this->params['penalty'],
            $this->params['penalty_calc_policy'],
            $scoreboardLimit,
            $this->params['title'],
            $this->params['start_time'],
            $this->params['finish_time'],
            $this->params['admin'],
            $sortByName,
            $withRunDetails,
            $this->params['auth_token']
        );

        $timeout = max(0, $this->params['finish_time'] - Time::get());
        if ($canUseContestantCache) {
            $contestantScoreboardCache->set($result, $timeout);
        } elseif ($canUseAdminCache) {
            $adminScoreboardCache->set($result, $timeout);
        }

        return $result;
    }

    /**
     * Returns Scoreboard events
     *
     * @return array Scoreboard events
     */
    public function events() {
        $result = null;

        $contestantEventsCache = new Cache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $this->params['problemset_id']);
        $adminEventsCache = new Cache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $this->params['problemset_id']);

        $canUseContestantCache = !$this->params['admin'];
        $canUseAdminCache = $this->params['admin'];

        // If cache is turned on and we're not looking for admin-only runs
        if ($canUseContestantCache) {
            $result = $contestantEventsCache->get();
        } elseif ($canUseAdminCache) {
            $result = $adminEventsCache->get();
        }

        if (!is_null($result)) {
            Scoreboard::setIsLastRunFromCacheForTesting(true);
            return $result;
        }

        try {
            // Get all distinct contestants participating in the given contest
            $rawContestIdentities = RunsDAO::getAllRelevantIdentities(
                (int)$this->params['problemset_id'],
                (int)$this->params['acl_id'],
                $this->params['admin'],
                null,
                null,
                !$this->params['virtual'] /* Treat admin as contestant */
            );

            // Get all problems given problemset
            $problemset = ProblemsetsDAO::getByPK($this->params['problemset_id']);
            $rawProblemsetProblems =
                ProblemsetProblemsDAO::getRelevantProblems($problemset);

            $contestRuns = RunsDAO::getProblemsetRuns($problemset);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $problemMapping = [];

        $order = 0;
        foreach ($rawProblemsetProblems as $problem) {
            $problemMapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => $problem->alias
            ];
        }

        $result = Scoreboard::calculateEvents(
            $this->params,
            $contestRuns,
            $rawContestIdentities,
            $problemMapping
        );

        $timeout = max(0, $this->params['finish_time'] - Time::get());
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
     * @param ScoreboardParams $params
     */
    public static function invalidateScoreboardCache(ScoreboardParams $params) {
        $log = Logger::getLogger('Scoreboard');
        $log->info('Invalidating scoreboard cache.');

        // Invalidar cache del contestant
        Cache::deleteFromCache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $params['problemset_id']);
        Cache::deleteFromCache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $params['problemset_id']);

        // Invalidar cache del admin
        Cache::deleteFromCache(Cache::ADMIN_SCOREBOARD_PREFIX, $params['problemset_id']);
        Cache::deleteFromCache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $params['problemset_id']);
    }

    /**
     * Force refresh of Scoreboard caches
     *
     * @param  ScoreboardParams $params
     */
    public static function refreshScoreboardCache(ScoreboardParams $params) {
        try {
            $problemset = ProblemsetsDAO::getByPK($params['problemset_id']);
            $contestRuns = RunsDAO::getProblemsetRuns($problemset);

            // Get all distinct contestants participating in the contest
            $rawContestIdentities = RunsDAO::getAllRelevantIdentities(
                (int)$params['problemset_id'],
                (int)$params['acl_id'],
                true /* show all runs */,
                null,
                null,
                !$params['virtual'] /* Treat admin as contestant in virtual contest */
            );

            // Get all problems given problemset
            $rawProblemsetProblems =
                ProblemsetProblemsDAO::getRelevantProblems($problemset);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $problemMapping = [];

        $order = 0;
        foreach ($rawProblemsetProblems as $problem) {
            $problemMapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => $problem->alias
            ];
        }

        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($params);

        // Cache scoreboard until the contest ends (or forever if it has already ended).
        // Contestant cache
        $timeout = max(0, $params['finish_time'] - Time::get());
        $contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $params['problemset_id']);
        $contestantScoreboard = Scoreboard::getScoreboardFromRuns(
            $contestRuns,
            $rawContestIdentities,
            $problemMapping,
            $params['penalty'],
            $params['penalty_calc_policy'],
            $scoreboardLimit,
            $params['title'],
            $params['start_time'],
            $params['finish_time'],
            $params['admin'],
            false  /* sortByName */
        );

        $contestantEventCache = new Cache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $params['problemset_id']);
        $contestantEventCache->set(Scoreboard::calculateEvents(
            $params,
            $contestRuns,
            $rawContestIdentities,
            $problemMapping
        ), $timeout);

        // Admin cache
        $params['admin'] = true; // Temporarily set for admin cache
        $contestantScoreboardCache->set($contestantScoreboard, $timeout);
        $adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $params['problemset_id']);
        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($params);
        $adminScoreboard = Scoreboard::getScoreboardFromRuns(
            $contestRuns,
            $rawContestIdentities,
            $problemMapping,
            $params['penalty'],
            $params['penalty_calc_policy'],
            $scoreboardLimit,
            $params['title'],
            $params['start_time'],
            $params['finish_time'],
            $params['admin'],
            false /* sortByName */
        );
        $adminScoreboardCache->set($adminScoreboard, $timeout);
        $params['admin'] = false;

        $adminEventCache = new Cache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $params['problemset_id']);
        $adminEventCache->set(Scoreboard::calculateEvents(
            $params,
            $contestRuns,
            $rawContestIdentities,
            $problemMapping
        ), $timeout);

        // Try to broadcast the updated scoreboards:
        $log = Logger::getLogger('Scoreboard');
        try {
            $log->debug('Sending updated scoreboards');
            Grader::getInstance()->broadcast(
                $params['alias'],
                (int)$problemset->problemset_id,
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
            Grader::getInstance()->broadcast(
                $params['alias'],
                (int)$problemset->problemset_id,
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
        } catch (Exception $e) {
            $log->error('Error broadcasting scoreboard: ' . $e);
        }
    }

    /**
     * getScoreboardTimeLimitUnixTimestamp
     * Returns the max timestamp to consider for the given problemset
     *
     * @param  ScoreboardParams $params
     * @return int
     */
    private static function getScoreboardTimeLimitUnixTimestamp(
        ScoreboardParams $params
    ) {
        if ($params['admin'] || ((Time::get() >= $params['finish_time']) && $params['show_scoreboard_after'])) {
            // Show full scoreboard to admin users
            // or if the contest finished and the creator wants to show it at the end
            return null;
        }

        $start = $params['start_time'];
        $finish = $params['finish_time'];

        $percentage = (double)$params['scoreboard_pct'] / 100.0;

        $limit = $start + (int) (($finish - $start) * $percentage);

        return $limit;
    }

    private static function getTotalScore($scores, $contestPenaltyCalcPolicy) {
        $totalPoints = 0;
        $totalPenalty = 0;
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
            'penalty' => $totalPenalty
        ];
    }

    private static function getScoreboardFromRuns(
        $contestRuns,
        $rawContestIdentities,
        $problemMapping,
        $contestPenalty,
        $contestPenaltyCalcPolicy,
        $scoreboardTimeLimit,
        $contestTitle,
        $contestStartTime,
        $contestFinishTime,
        $showAllRuns,
        $sortByName,
        $withRunDetails = false,
        $authToken = null
    ) {
        $testOnly = [];
        $noRuns = [];
        $identitiesInfo = [];
        $problems = [];

        foreach ($problemMapping as $problem) {
            array_push($problems, $problem);
        }

        // Calculate score for each contestant x problem
        foreach ($rawContestIdentities as $contestant) {
            $identityProblems = [];

            $testOnly[$contestant['identity_id']] = true;
            $noRuns[$contestant['identity_id']] = true;
            foreach ($problemMapping as $id => $problem) {
                array_push($identityProblems, [
                    'points' => 0,
                    'percent' => 0,
                    'penalty' => 0,
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
                'total' => null,
                'country' => $contestant['country_id'],
                'is_invited' => $contestant['is_invited'],
            ];
        }

        foreach ($contestRuns as $run) {
            $identityId = $run['identity_id'];
            $problemId = $run['problem_id'];
            $contestScore = $run['contest_score'];
            $score = $run['score'];
            $isTest = $run['type'] == 'test';

            $problem =
                &$identitiesInfo[$identityId]['problems'][$problemMapping[$problemId]['order']];

            if (!array_key_exists($identityId, $testOnly)) {
                //
                // Hay un usuario en la lista de Runs,
                // que no fue regresado por RunsDAO::getAllRelevantIdentities()
                //
                continue;
            }

            $testOnly[$identityId] &= $isTest;
            $noRuns[$identityId] = false;
            if (!$showAllRuns) {
                if ($isTest) {
                    continue;
                }
                if (!is_null($scoreboardTimeLimit) &&
                    $run['time'] >= $scoreboardTimeLimit) {
                    $problem['runs']++;
                    $problem['pending'] = true;
                    continue;
                }
            }

            $totalPenalty = $run['penalty'] + $problem['runs'] * $contestPenalty;
            $roundedScore = round($contestScore, 2);
            if ($problem['points'] < $roundedScore ||
                $problem['points'] == $roundedScore && $problem['penalty'] > $totalPenalty) {
                $problem['points'] = $roundedScore;
                $problem['percent'] = round($score * 100, 2);
                $problem['penalty'] = $totalPenalty;

                if ($withRunDetails === true) {
                    $runDetails = [];

                    $runDetailsRequest = new Request([
                        'run_alias' => $run['guid'],
                        'auth_token' => $authToken,
                    ]);
                    $runDetails = RunController::apiDetails($runDetailsRequest);
                    unset($runDetails['source']);
                    $problem['run_details'] = $runDetails;
                }
            }
            $problem['runs']++;
        }

        $result = [];
        foreach ($rawContestIdentities as $contestant) {
            $identityId = $contestant['identity_id'];

            // Add contestant results to scoreboard data
            if (!$showAllRuns && $testOnly[$identityId] && !$noRuns[$identityId]) {
                continue;
            }
            $info = $identitiesInfo[$identityId];
            if ($info == null) {
                continue;
            }
            $info[self::TOTAL_COLUMN] = Scoreboard::getTotalScore($info['problems'], $contestPenaltyCalcPolicy);
            array_push($result, $info);
        }

        Scoreboard::sortScoreboard($result, $sortByName);
        usort($problems, ['Scoreboard', 'compareOrder']);

        return [
            'status' => 'ok',
            'problems' => $problems,
            'ranking' => $result,
            'start_time' => $contestStartTime,
            'finish_time' => $contestFinishTime,
            'title' => $contestTitle,
            'time' => Time::get() * 1000
        ];
    }

    private static function compareUserScores($a, $b) {
        if ($a[self::TOTAL_COLUMN]['points'] != $b[self::TOTAL_COLUMN]['points']) {
            return ($a[self::TOTAL_COLUMN]['points'] < $b[self::TOTAL_COLUMN]['points']) ? 1 : -1;
        }
        if ($a[self::TOTAL_COLUMN]['penalty'] != $b[self::TOTAL_COLUMN]['penalty']) {
            return ($a[self::TOTAL_COLUMN]['penalty'] > $b[self::TOTAL_COLUMN]['penalty']) ? 1 : -1;
        }
        return Scoreboard::compareUserNames($a, $b);
    }

    private static function compareUserNames($a, $b) {
        return strcasecmp($a['username'], $b['username']);
    }

    private static function compareOrder($a, $b) {
        return $a['order'] - $b['order'];
    }

    private static function sortScoreboard(&$scoreboard, $sortByName = false) {
        if ($sortByName == false) {
            // Sort users by their total column
            usort($scoreboard, ['Scoreboard', 'compareUserScores']);
        } else {
            // Sort users by their name
            usort($scoreboard, ['Scoreboard', 'compareUserNames']);
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
                if ($userData['total']['points'] < $currentPoints ||
                    $userData['total']['penalty'] > $currentPenalty) {
                    $currentPoints = $userData['total']['points'];
                    $currentPenalty = $userData['total']['penalty'];

                    $place += $draws;
                    $draws = 1;
                } elseif ($userData['total']['points'] == $currentPoints &&
                           $userData['total']['penalty'] == $currentPenalty) {
                    $draws++;
                }
            }

            if (!$sortByName) {
                // Set the place for the current user
                $userData['place'] = $place;
            }
        }
    }

    private static function calculateEvents(
        ScoreboardParams $params,
        $contestRuns,
        $rawContestIdentities,
        $problemMapping
    ) {
        $contestIdentities = [];

        foreach ($rawContestIdentities as $identity) {
            $contestIdentities[$identity['identity_id']] = $identity;
        }

        $result = [];
        $identityProblemsScore = [];
        $contestStart = $params['start_time'];
        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($params);

        // Calculate score for each contestant x problem x run
        foreach ($contestRuns as $run) {
            if (!$params['admin'] && $run['type'] != 'normal') {
                continue;
            }

            $log = Logger::getLogger('Scoreboard');
            $runDelay = $run['time'];
            $log->debug(">>      run_delay : $runDelay");
            $log->debug(">>scoreboardLimit : $scoreboardLimit");
            $log->debug('');

            if (!is_null($scoreboardLimit) && $runDelay >= $scoreboardLimit) {
                continue;
            }

            $identityId = $run['identity_id'];
            $problemId = $run['problem_id'];
            $contestScore = $run['contest_score'];

            if (!isset($identityProblemsScore[$identityId])) {
                $identityProblemsScore[$identityId] = [
                    $problemId => ['points' => 0, 'penalty' => 0]
                ];
            } elseif (!isset($identityProblemsScore[$identityId][$problemId])) {
                $identityProblemsScore[$identityId][$problemId] =
                    ['points' => 0, 'penalty' => 0];
            }

            $problemData = &$identityProblemsScore[$identityId][$problemId];

            if ($problemData['points'] >= $contestScore and $params['show_all_runs']) {
                continue;
            }

            $problemData['points'] = max($problemData['points'], round((float) $contestScore, 2));
            $problemData['penalty'] = 0;

            $identity = &$contestIdentities[$identityId];

            if ($identity == null) {
                continue;
            }

            $data = [
                'name' => $identity['name'] ? $identity['name'] : $identity['username'],
                'username' => $identity['username'],
                'delta' => max(0, ($runDelay - $contestStart) / 60),
                'problem' => [
                    'alias' => $problemMapping[$problemId]['alias'],
                    'points' => round($contestScore, 2),
                    'penalty' => 0
                ],
                'total' => [
                    'points' => 0,
                    'penalty' => 0
                ],
                'country' => $identity['country_id'],
                'is_invited' => $identity['is_invited'],
            ];

            foreach ($identityProblemsScore[$identityId] as $problem) {
                $data['total']['points'] += $problem['points'];
                if ($params['penalty_calc_policy'] == 'sum') {
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
     * @param bool $value
     */
    private function setIsLastRunFromCacheForTesting($value) {
        if (Scoreboard::$isTestRun) {
            Scoreboard::$isLastRunFromCache = $value;
        }
    }

    /**
     * Get last run from Cache valu
     * @return bool
     */
    public static function getIsLastRunFromCacheForTesting() {
        return Scoreboard::$isLastRunFromCache;
    }

    /**
     * Enable testing extras
     * @param bool $value
     */
    public static function setIsTestRunForTesting($value) {
        Scoreboard::$isTestRun = $value;
    }
}
