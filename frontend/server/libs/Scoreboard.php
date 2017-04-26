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
        ScoreboardParams::validateParameter('penalty_calc_policy', $params, false /*is_required*/, 'sum');
        ScoreboardParams::validateParameter('show_scoreboard_after', $params, false /*is_required*/, 1);
        ScoreboardParams::validateParameter('scoreboard_pct', $params, false /*is_required*/, 100);
        ScoreboardParams::validateParameter('show_all_runs', $params, false /*is_required*/, false);
        ScoreboardParams::validateParameter('auth_token', $params, false /*is_required*/, null);
        ScoreboardParams::validateParameter('only_ac', $params, false /*is_required*/, false);

        $params['start_time'] = strtotime($params['start_time']);
        $params['finish_time'] = strtotime($params['finish_time']);

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
                'penalty_calc_policy' => $contest->penalty_calc_policy,
                'show_scoreboard_after' => $contest->show_scoreboard_after,
                'scoreboard_pct' => $contest->scoreboard]);
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

    public function __construct(ScoreboardParams $params) {
        $this->params = $params;
        $this->log = Logger::getLogger('Scoreboard');
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

        $can_use_contestant_cache = !$this->params['show_all_runs'] &&
            !$sortByName &&
            is_null($filterUsersBy) &&
            !$this->params['only_ac'];

        $can_use_admin_cache = $this->params['show_all_runs'] &&
            !$sortByName &&
            is_null($filterUsersBy) &&
            !$this->params['only_ac'];

        // If cache is turned on and we're not looking for admin-only runs
        if ($can_use_contestant_cache) {
            $result = $contestantScoreboardCache->get();
        } elseif ($can_use_admin_cache) {
            $result = $adminScoreboardCache->get();
        }

        if (is_null($result)) {
            try {
                // Get all distinct contestants participating in the given contest
                $raw_contest_users = RunsDAO::getAllRelevantUsers(
                    $this->params['problemset_id'],
                    $this->params['acl_id'],
                    true /* show all runs */,
                    $filterUsersBy,
                    $this->params['group_id']
                );

                // Get all problems given problemset
                $problemset = ProblemsetsDAO::getByPK($this->params['problemset_id']);
                $raw_problemset_problems =
                    ProblemsetProblemsDAO::getRelevantProblems($problemset);

                $contest_runs = RunsDAO::getProblemsetRuns(
                    $problemset,
                    $this->params['only_ac']
                );
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            $problem_mapping = [];

            $order = 0;
            foreach ($raw_problemset_problems as $problem) {
                $problem_mapping[$problem->problem_id] = [
                    'order' => $order++,
                    'alias' => $problem->alias
                ];
            }

            $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($this->params);

            $result = Scoreboard::getScoreboardFromRuns(
                $contest_runs,
                $raw_contest_users,
                $problem_mapping,
                $this->params['penalty'],
                $this->params['penalty_calc_policy'],
                $scoreboardLimit,
                $this->params['title'],
                $this->params['start_time'],
                $this->params['finish_time'],
                $this->params['show_all_runs'],
                $sortByName,
                $withRunDetails,
                $this->params['auth_token']
            );
            $result['from_cache'] = false;

            $timeout = max(0, $this->params['finish_time'] - time());
            if ($can_use_contestant_cache) {
                $contestantScoreboardCache->set($result, $timeout);
            } elseif ($can_use_admin_cache) {
                $adminScoreboardCache->set($result, $timeout);
            }
        } else {
            $result['from_cache'] = true;
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

        $can_use_contestant_cache = !$this->params['show_all_runs'];
        $can_use_admin_cache = $this->params['show_all_runs'];

        // If cache is turned on and we're not looking for admin-only runs
        if ($can_use_contestant_cache) {
            $result = $contestantEventsCache->get();
        } elseif ($can_use_admin_cache) {
            $result = $adminEventsCache->get();
        }

        if (is_null($result)) {
            try {
                // Get all distinct contestants participating in the given contest
                $raw_contest_users = RunsDAO::getAllRelevantUsers(
                    $this->params['problemset_id'],
                    $this->params['acl_id'],
                    $this->params['show_all_runs']
                );

                // Get all problems given problemset
                $problemset = ProblemsetsDAO::getByPK($this->params['problemset_id']);
                $raw_problemset_problems =
                    ProblemsetProblemsDAO::getRelevantProblems($problemset);

                $contest_runs = RunsDAO::getProblemsetRuns($problemset);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            $problem_mapping = [];

            $order = 0;
            foreach ($raw_problemset_problems as $problem) {
                $problem_mapping[$problem->problem_id] = [
                    'order' => $order++,
                    'alias' => $problem->alias
                ];
            }

            $result = Scoreboard::calculateEvents(
                $this->params,
                $contest_runs,
                $raw_contest_users,
                $problem_mapping
            );

            $timeout = max(0, $this->params['finish_time'] - time());
            if ($can_use_contestant_cache) {
                $contestantEventsCache->set($result, $timeout);
            } elseif ($can_use_admin_cache) {
                $adminEventsCache->set($result, $timeout);
            }
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
            $contest_runs = RunsDAO::getProblemsetRuns($problemset);

            // Get all distinct contestants participating in the contest
            $raw_contest_users = RunsDAO::getAllRelevantUsers(
                $params['problemset_id'],
                $params['acl_id'],
                true /* show all runs */,
                null
            );

            // Get all problems given problemset
            $raw_problemset_problems =
                ProblemsetProblemsDAO::getRelevantProblems($problemset);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $problem_mapping = [];

        $order = 0;
        foreach ($raw_problemset_problems as $problem) {
            $problem_mapping[$problem->problem_id] = [
                'order' => $order++,
                'alias' => $problem->alias
            ];
        }

        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($params);

        // Cache scoreboard until the contest ends (or forever if it has already ended).
        // Contestant cache
        $timeout = max(0, $params['finish_time'] - time());
        $contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $params['problemset_id']);
        $contestantScoreboard = Scoreboard::getScoreboardFromRuns(
            $contest_runs,
            $raw_contest_users,
            $problem_mapping,
            $params['penalty'],
            $params['penalty_calc_policy'],
            $scoreboardLimit,
            $params['title'],
            $params['start_time'],
            $params['finish_time'],
            $params['show_all_runs'],
            false  /* sortByName */
        );

        $contestantEventCache = new Cache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $params['problemset_id']);
        $contestantEventCache->set(Scoreboard::calculateEvents(
            $params,
            $contest_runs,
            $raw_contest_users,
            $problem_mapping
        ), $timeout);

        // Admin cache
        $params['show_all_runs'] = true; // Temporarily set for admin cache
        $contestantScoreboardCache->set($contestantScoreboard, $timeout);
        $adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $params['problemset_id']);
        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($params);
        $adminScoreboard = Scoreboard::getScoreboardFromRuns(
            $contest_runs,
            $raw_contest_users,
            $problem_mapping,
            $params['penalty'],
            $params['penalty_calc_policy'],
            $scoreboardLimit,
            $params['title'],
            $params['start_time'],
            $params['finish_time'],
            $params['show_all_runs'],
            false /* sortByName */
        );
        $adminScoreboardCache->set($adminScoreboard, $timeout);
        $params['show_all_runs'] = false;

        $adminEventCache = new Cache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $params['problemset_id']);
        $adminEventCache->set(Scoreboard::calculateEvents(
            $params,
            $contest_runs,
            $raw_contest_users,
            $problem_mapping
        ), $timeout);

        // Try to broadcast the updated scoreboards:
        $log = Logger::getLogger('Scoreboard');
        try {
            $grader = new Grader();
            $log->debug('Sending updated scoreboards');
            $grader->broadcast(
                $params['alias'],
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
            $grader->broadcast(
                $params['alias'],
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
        if ($params['show_all_runs'] || ((time() >= $params['finish_time']) && $params['show_scoreboard_after'])) {
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

    private static function getTotalScore($scores, $contest_penalty_calc_policy) {
        $totalPoints = 0;
        $totalPenalty = 0;
        // Get final scores
        foreach ($scores as $score) {
            $totalPoints += $score['points'];
            if ($contest_penalty_calc_policy == 'sum') {
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
        $runs,
        $raw_contest_users,
        $problem_mapping,
        $contest_penalty,
        $contest_penalty_calc_policy,
        $scoreboard_time_limit,
        $contest_title,
        $contest_start_time,
        $contest_finish_time,
        $showAllRuns,
        $sortByName,
        $withRunDetails = false,
        $auth_token = null
    ) {
        $test_only = [];
        $no_runs = [];
        $users_info = [];
        $problems = [];

        foreach ($problem_mapping as $problem) {
            array_push($problems, $problem);
        }

        // Calculate score for each contestant x problem
        foreach ($raw_contest_users as $contestant) {
            $user_problems = [];

            $test_only[$contestant->user_id] = true;
            $no_runs[$contestant->user_id] = true;
            foreach ($problem_mapping as $id => $problem) {
                array_push($user_problems, [
                    'points' => 0,
                    'percent' => 0,
                    'penalty' => 0,
                    'runs' => 0
                ]);
            }

            // Add the problems' information
            $users_info[$contestant->user_id] = [
                'problems' => $user_problems,
                'username' => $contestant->username,
                'name' => $contestant->name ?
                    $contestant->name :
                    $contestant->username,
                'total' => null,
                'country' => $contestant->country_id
            ];
        }

        foreach ($runs as $run) {
            $user_id = $run->user_id;
            $problem_id = $run->problem_id;
            $contest_score = $run->contest_score;
            $score = $run->score;
            $is_test = $run->test != 0;

            $problem =
                &$users_info[$user_id]['problems'][$problem_mapping[$problem_id]['order']];

            if (!array_key_exists($user_id, $test_only)) {
                //
                // Hay un usuario en la lista de Runs,
                // que no fue regresado por RunsDAO::getAllRelevantUsers()
                //
                continue;
            }

            $test_only[$user_id] &= $is_test;
            $no_runs[$user_id] = false;
            if (!$showAllRuns) {
                if ($is_test) {
                    continue;
                }
                if (!is_null($scoreboard_time_limit) &&
                    strtotime($run->time) >= $scoreboard_time_limit) {
                    $problem['runs']++;
                    $problem['pending'] = true;
                    continue;
                }
            }

            $totalPenalty = $run->penalty +     $problem['runs'] * $contest_penalty;
            $rounded_score = round($contest_score, 2);
            if ($problem['points'] < $rounded_score ||
                $problem['points'] == $rounded_score && $problem['penalty'] > $totalPenalty) {
                $problem['points'] = $rounded_score;
                $problem['percent'] = round($score * 100, 2);
                $problem['penalty'] = $totalPenalty;

                if ($withRunDetails === true) {
                    $runDetails = [];

                    $runDetailsRequest = new Request([
                        'run_alias' => $run->guid,
                        'auth_token' => $auth_token,
                    ]);
                    $runDetails = RunController::apiDetails($runDetailsRequest);
                    unset($runDetails['source']);
                    $problem['run_details'] = $runDetails;
                }
            }
            $problem['runs']++;
        }

        $result = [];
        foreach ($raw_contest_users as $contestant) {
            $user_id = $contestant->user_id;

            // Add contestant results to scoreboard data
            if (!$showAllRuns && $test_only[$user_id] && !$no_runs[$user_id]) {
                continue;
            }
            $info = $users_info[$user_id];
            if ($info == null) {
                continue;
            }
            $info[self::TOTAL_COLUMN] = Scoreboard::getTotalScore($info['problems'], $contest_penalty_calc_policy);
            array_push($result, $info);
        }

        Scoreboard::sortScoreboard($result, $sortByName);
        usort($problems, ['Scoreboard', 'compareOrder']);

        return [
            'status' => 'ok',
            'problems' => $problems,
            'ranking' => $result,
            'start_time' => $contest_start_time,
            'finish_time' => $contest_finish_time,
            'title' => $contest_title,
            'time' => time() * 1000
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

            // Set the place for the current user
            $userData['place'] = $place;
        }
    }

    private static function calculateEvents(
        ScoreboardParams $params,
        $contest_runs,
        $raw_contest_users,
        $problem_mapping
    ) {
        $contest_users = [];

        foreach ($raw_contest_users as $user) {
            $contest_users[$user->user_id] = $user;
        }

        $result = [];
        $user_problems_score = [];
        $contestStart = $params['start_time'];
        $scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($params);

        // Calculate score for each contestant x problem x run
        foreach ($contest_runs as $run) {
            if (!$params['show_all_runs'] && $run->test != 0) {
                continue;
            }

            $log = Logger::getLogger('Scoreboard');
            $run_delay = strtotime($run->time);
            $log->debug(">>      run_delay : $run_delay");
            $log->debug(">>scoreboardLimit : $scoreboardLimit");
            $log->debug('');

            if (!is_null($scoreboardLimit) && $run_delay >= $scoreboardLimit) {
                continue;
            }

            $user_id = $run->user_id;
            $problem_id = $run->problem_id;
            $contest_score = $run->contest_score;

            if (!isset($user_problems_score[$user_id])) {
                $user_problems_score[$user_id] = [
                    $problem_id => ['points' => 0, 'penalty' => 0]
                ];
            } elseif (!isset($user_problems_score[$user_id][$problem_id])) {
                $user_problems_score[$user_id][$problem_id] =
                    ['points' => 0, 'penalty' => 0];
            }

            $problem_data = &$user_problems_score[$user_id][$problem_id];

            if ($problem_data['points'] >= $contest_score) {
                continue;
            }

            $problem_data['points'] = round((float) $contest_score, 2);
            $problem_data['penalty'] = 0;

            $user = &$contest_users[$user_id];

            if ($user == null) {
                continue;
            }

            $data = [
                'name' => $user->name ? $user->name : $user->username,
                'username' => $user->username,
                'delta' => max(0, ($run_delay - $contestStart) / 60),
                'problem' => [
                    'alias' => $problem_mapping[$problem_id]['alias'],
                    'points' => round($contest_score, 2),
                    'penalty' => 0
                ],
                'total' => [
                    'points' => 0,
                    'penalty' => 0
                ],
                'country' => $user->country_id
            ];

            foreach ($user_problems_score[$user_id] as $problem) {
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
}
