<?php

/*
 * Scoreboard
 *
 */

class Scoreboard {
	// Column to return total score per user
	const total_column = "total";

	// Contest's data
	private $contest_id;
	private $showAllRuns;
	private $auth_token;
	public $log;

	public function __construct($contest_id, $showAllRuns = false, $auth_token = null) {
		$this->contest_id = $contest_id;
		$this->showAllRuns = $showAllRuns;
		$this->auth_token = $auth_token;
		$this->log = Logger::getLogger("Scoreboard");
	}

	public function generate($withRunDetails = false, $sortByName = false, $filterUsersBy = NULL) {
		$result = null;

		$contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $this->contest_id);
		$adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $this->contest_id);

		$can_use_contestant_cache = !$this->showAllRuns &&
			!$sortByName &&
			is_null($filterUsersBy);

		$can_use_admin_cache = $this->showAllRuns &&
			!$sortByName &&
			is_null($filterUsersBy);

		// If cache is turned on and we're not looking for admin-only runs
		if ($can_use_contestant_cache) {
			$result = $contestantScoreboardCache->get();
		} else if ($can_use_admin_cache) {
			$result = $adminScoreboardCache->get();
		}

		if (is_null($result)) {
			try {
				$contest = ContestsDAO::getByPK($this->contest_id);

				// Get all distinct contestants participating in the contest given contest_id
				$raw_contest_users = RunsDAO::GetAllRelevantUsers(
					$this->contest_id,
					true /* show all runs */,
					$filterUsersBy
				);

				// Get all problems given contest_id
				$raw_contest_problems =
					ContestProblemsDAO::GetRelevantProblems($this->contest_id);

				$use_penalty = $contest->getPenaltyTimeStart() != 'none';

				$contest_runs = RunsDAO::GetContestRuns(
					$this->contest_id,
					$use_penalty ? 'submit_delay' : 'run_id'
				);
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}

			$problem_mapping = array();

			$order = 0;
			foreach ($raw_contest_problems as $problem) {
				$problem_mapping[$problem->problem_id] = array(
					'order' => $order++,
					'alias' => $problem->alias
				);
			}

			$scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($contest, $this->showAllRuns);

			$result = Scoreboard::getScoreboardFromRuns(
				$contest_runs,
				$raw_contest_users,
				$problem_mapping,
				$contest->getPenalty(),
				$scoreboardLimit,
				$contest,
				$this->showAllRuns,
				$sortByName
			);

			$timeout = max(0, strtotime($contest->getFinishTime()) - time());
			if ($can_use_contestant_cache) {
				$contestantScoreboardCache->set($result, $timeout);
			} else if ($can_use_admin_cache) {
				$adminScoreboardCache->set($result, $timeout);
			}
		}

		return $result;
	}

	public function events() {
		$result = null;

		$contestantEventsCache = new Cache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $this->contest_id);
		$adminEventsCache = new Cache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $this->contest_id);

		$can_use_contestant_cache = !$this->showAllRuns;
		$can_use_admin_cache = $this->showAllRuns;

		// If cache is turned on and we're not looking for admin-only runs
		if ($can_use_contestant_cache) {
			$result = $contestantEventsCache->get();
		} else if ($can_use_admin_cache) {
			$result = $adminEventsCache->get();
		}

		if (is_null($result)) {
			try {
				$contest = ContestsDAO::getByPK($this->contest_id);

				// Get all distinct contestants participating in the contest given contest_id
				$raw_contest_users = RunsDAO::GetAllRelevantUsers(
					$this->contest_id,
					$this->showAllRuns
				);

				// Get all problems given contest_id
				$raw_contest_problems =
					ContestProblemsDAO::GetRelevantProblems($this->contest_id);

				$use_penalty = $contest->getPenaltyTimeStart() != 'none';

				$contest_runs = RunsDAO::GetContestRuns(
					$this->contest_id,
					$use_penalty ? 'submit_delay' : 'run_id'
				);
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}

			$problem_mapping = array();

			$order = 0;
			foreach ($raw_contest_problems as $problem) {
				$problem_mapping[$problem->problem_id] = array(
					'order' => $order++,
					'alias' => $problem->alias
				);
			}

			$result = Scoreboard::calculateEvents($contest,
			                                      $contest_runs,
			                                      $use_penalty,
			                                      $raw_contest_users,
			                                      $problem_mapping,
																						$this->showAllRuns);

			$timeout = max(0, strtotime($contest->getFinishTime()) - time());
			if ($can_use_contestant_cache) {
				$contestantEventsCache->set($result, $timeout);
			} else if ($can_use_admin_cache) {
				$adminEventsCache->set($result, $timeout);
			}
		}

		return $result;
	}

	/**
	 * New runs trigger a scoreboard update asynchronously, only invalidate
	 * scoreboard when contest details have changed.
	 *
	 * @param int $contest_id
	 */
	public static function InvalidateScoreboardCache($contest_id) {
		$log = Logger::getLogger("Scoreboard");
		$log->info("Invalidating scoreboard cache.");

		// Invalidar cache del contestant
		Cache::deleteFromCache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $contest_id);
		Cache::deleteFromCache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $contest_id);

		// Invalidar cache del admin
		Cache::deleteFromCache(Cache::ADMIN_SCOREBOARD_PREFIX, $contest_id);
		Cache::deleteFromCache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $contest_id);
	}

	public static function RefreshScoreboardCache($contest_id) {
		try {
			$contest = ContestsDAO::getByPK($contest_id);

			$use_penalty = $contest->getPenaltyTimeStart() != 'none';

			$contest_runs = RunsDAO::GetContestRuns(
				$contest_id,
				$use_penalty ? 'submit_delay' : 'run_id'
			);

			// Get all distinct contestants participating in the contest given contest_id
			$raw_contest_users = RunsDAO::GetAllRelevantUsers(
				$contest_id,
				true /* show all runs */,
				NULL
			);

			// Get all problems given contest_id
			$raw_contest_problems =
				ContestProblemsDAO::GetRelevantProblems($contest_id);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$problem_mapping = array();

		$order = 0;
		foreach ($raw_contest_problems as $problem) {
			$problem_mapping[$problem->problem_id] = array(
				'order' => $order++,
				'alias' => $problem->alias
			);
		}

		$scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($contest);

		// Cache scoreboard until the contest ends (or forever if it has already ended).
		$timeout = max(0, strtotime($contest->getFinishTime()) - time());
		$contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $contest_id);

		$contestantScoreboard = Scoreboard::getScoreboardFromRuns(
			$contest_runs,
			$raw_contest_users,
			$problem_mapping,
			$contest->getPenalty(),
			$scoreboardLimit,
			$contest,
			false, /* showAllRuns */
			false  /* sortByName */
		);
		$contestantScoreboardCache->set($contestantScoreboard, $timeout);
		$adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $contest_id);
		$scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($contest, true);
		$adminScoreboard = Scoreboard::getScoreboardFromRuns(
			$contest_runs,
			$raw_contest_users,
			$problem_mapping,
			$contest->getPenalty(),
			$scoreboardLimit,
			$contest,
			true, /* showAllRuns */
			false /* sortByName */
		);
		$adminScoreboardCache->set($adminScoreboard, $timeout);

		$contestantEventCache = new Cache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $contest_id);
		$contestantEventCache->set(Scoreboard::calculateEvents(
			$contest,
			$contest_runs,
			$use_penalty,
			$raw_contest_users,
			$problem_mapping,
			false /* showAllRuns */
		), $timeout);

		$adminEventCache = new Cache(Cache::ADMIN_SCOREBOARD_EVENTS_PREFIX, $contest_id);
		$adminEventCache->set(Scoreboard::calculateEvents(
			$contest,
			$contest_runs,
			$use_penalty,
			$raw_contest_users,
			$problem_mapping,
			true /* showAllRuns */
		), $timeout);

		// Try to broadcast the updated scoreboards:
		$log = Logger::getLogger("Scoreboard");
		try {
			$grader = new Grader();
			$log->debug("Sending updated scoreboards");
			$grader->broadcast(
				$contest->alias,
				json_encode(array(
					'message' => '/scoreboard/update/',
					'scoreboard' => $adminScoreboard
				)),
				false,
				-1,
				false
			);
			$grader->broadcast(
				$contest->alias,
				json_encode(array(
					'message' => '/scoreboard/update/',
					'scoreboard' => $contestantScoreboard
				)),
				true,
				-1,
				true
			);
		} catch (Exception $e) {
			$log->error('Error broadcasting scoreboard: ' . $e);
		}
	}

	private static function getScoreboardTimeLimitUnixTimestamp(Contests $contest,
	                                                            $showAllRuns = false) {
		$start = strtotime($contest->getStartTime());
		$finish = strtotime($contest->getFinishTime());

		if ($showAllRuns || (ContestsDAO::hasFinished($contest) && $contest->getShowScoreboardAfter())) {
			// Show full scoreboard to admin users
			// or if the contest finished and the creator wants to show it at the end
			$percentage = 1.0;
		} else {
			$percentage = (double) $contest->getScoreboard() / 100.0;
		}

		$limit = $start + (int) (($finish - $start) * $percentage);

		return $limit;
	}

	private static function getTotalScore($scores) {
		$sumPoints = 0;
		$sumPenalty = 0;
		// Get sum of all scores
		foreach ($scores as $score) {
			$sumPoints += $score["points"];
			$sumPenalty += $score["penalty"];
		}

		return array(
			"points" => $sumPoints,
			"penalty" => $sumPenalty
		);
	}

	private static function getScoreboardFromRuns($runs, $raw_contest_users, $problem_mapping,
	                                              $contest_penalty, $scoreboard_time_limit,
	                                              $contest, $showAllRuns, $sortByName) {
		$test_only = array();
		$no_runs = array();
		$users_info = array();
		$problems = array();

		foreach ($problem_mapping as $problem) {
			array_push($problems, $problem);
		}

		// Calculate score for each contestant x problem
		foreach ($raw_contest_users as $contestant) {
			$user_problems = array();

			$test_only[$contestant->getUserId()] = true;
			$no_runs[$contestant->getUserId()] = true;
			foreach ($problem_mapping as $id=>$problem) {
				array_push($user_problems, array(
					'points' => 0,
					'penalty' => 0,
					'runs' => 0
				));
			}

			// Add the problems' information
			$users_info[$contestant->getUserId()] = array(
				'problems' => $user_problems,
				'username' => $contestant->getUsername(),
				'name' => $contestant->getName() ?
					$contestant->getName() :
					$contestant->getUsername(),
				'total' => null
			);
		}

		foreach ($runs as $run) {
			$user_id = $run->getUserId();
			$problem_id = $run->getProblemId();
			$contest_score = $run->getContestScore();
			$is_test = $run->getTest() != 0;

			$problem =
				&$users_info[$user_id]['problems'][$problem_mapping[$problem_id]['order']];

			$test_only[$user_id] &= $is_test;
			$no_runs[$user_id] = false;
			if (!$showAllRuns) {
				if ($is_test) {
					continue;
				}
				if (strtotime($run->getTime()) >= $scoreboard_time_limit) {
					$problem['runs']++;
					$problem['pending'] = true;
					continue;
				}
			}

			$totalPenalty = $run->getSubmitDelay() +
				$problem['runs'] * $contest_penalty;
			if ($problem['points'] < $contest_score ||
			    $problem['points'] == $contest_score && $problem['penalty'] > $totalPenalty) {
				$problem['points'] = (int)round($contest_score);
				$problem['penalty'] = $totalPenalty;
			}
			$problem['runs']++;
		}

		$result = array();
		foreach ($raw_contest_users as $contestant) {
			$user_id = $contestant->getUserId();

			// Add contestant results to scoreboard data
			if (!$showAllRuns && $test_only[$user_id] && !$no_runs[$user_id]) {
				continue;
			}
			$info = $users_info[$user_id];
			$info[self::total_column] = Scoreboard::getTotalScore($info['problems']);
			array_push($result, $info);
		}

		Scoreboard::sortScoreboard($result, $sortByName);
		usort($problems, array('Scoreboard', 'compareOrder'));

		return array(
			'problems' => $problems,
			'ranking' => $result,
			'start_time' => strtotime($contest->start_time),
			'finish_time' => strtotime($contest->finish_time),
			'title' => $contest->title
		);
	}

	private static function compareUserScores($a, $b) {
		if ($a[self::total_column]["points"] == $b[self::total_column]["points"]) {
			if ($a[self::total_column]["penalty"] == $b[self::total_column]["penalty"])
				return 0;

			return ($a[self::total_column]["penalty"] > $b[self::total_column]["penalty"]) ? 1 : -1;
		}

		return ($a[self::total_column]["points"] < $b[self::total_column]["points"]) ? 1 : -1;
	}

	private static function compareUserNames($a, $b) {
		return strcmp($a['username'], $b['username']);
	}

	private static function compareOrder($a, $b) {
		return $a['order'] - $b['order'];
	}

	private static function sortScoreboard(&$scoreboard, $sortByName = false) {
		if ($sortByName == false) {
			// Sort users by their total column
			usort($scoreboard, array('Scoreboard', 'compareUserScores'));
		} else {
			// Sort users by their name
			usort($scoreboard, array('Scoreboard', 'compareUserNames'));
		}

		// Append the place for each user
		$currentPoints = -1;
		$currentPenalty = -1;
		$place = 1;
		$draws = 1;
		foreach($scoreboard as &$userData) {
			if ($currentPoints === -1) {
				$currentPoints = $userData["total"]["points"];
				$currentPenalty = $userData["total"]["penalty"];
			} else {
				// If not in draw
				if ($userData["total"]["points"] < $currentPoints ||
				    $userData["total"]["penalty"] > $currentPenalty) {
					$currentPoints = $userData["total"]["points"];
					$currentPenalty = $userData["total"]["penalty"];

					$place += $draws;
					$draws = 1;

				} else if ($userData["total"]["points"] == $currentPoints &&
				           $userData["total"]["penalty"] == $currentPenalty) {
					$draws++;
				}
			}

			// Set the place for the current user
			$userData["place"] = $place;
		}
	}

	private static function calculateEvents($contest, $contest_runs, $use_penalty,
	                                        $raw_contest_users, $problem_mapping, $showAllRuns) {
		$contest_users = array();

		foreach ($raw_contest_users as $user) {
			$contest_users[$user->getUserId()] = $user;
		}

		$result = array();

		$user_problems_score = array();

		$contestStart = strtotime($contest->getStartTime());
		$scoreboardLimit = Scoreboard::getScoreboardTimeLimitUnixTimestamp($contest, $showAllRuns);

		// Calculate score for each contestant x problem x run
		foreach ($contest_runs as $run) {
			if (!$showAllRuns && $run->getTest() != 0) {
				continue;
			}

			$run_delay = strtotime($run->getTime());
			if ($run_delay >= $scoreboardLimit) {
				continue;
			}

			$user_id = $run->getUserId();
			$problem_id = $run->getProblemId();
			$contest_score = $run->getContestScore();

			if (!isset($user_problems_score[$user_id])) {
				$user_problems_score[$user_id] = array(
					$problem_id => array('points' => 0, 'penalty' => 0)
				);
			} else if (!isset($user_problems_score[$user_id][$problem_id])) {
				$user_problems_score[$user_id][$problem_id] =
					array('points' => 0, 'penalty' => 0);
			}

			$problem_data = &$user_problems_score[$user_id][$problem_id];

			if ($problem_data['points'] >= $contest_score) {
				continue;
			}

			$problem_data['points'] = round((float) $contest_score, 2);
			$problem_data['penalty'] = 0;

			$user = &$contest_users[$user_id];

			$data = array(
				'name' => $user->getName() ? $user->getName() : $user->getUsername(),
				'username' => $user->getUsername(),
				'delta' => max(0, $use_penalty ?
					(int)$run->getSubmitDelay() :
					($run_delay - $contestStart) / 60),
				'problem' => array(
					'alias' => $problem_mapping[$problem_id]['alias'],
					'points' => round($contest_score, 2),
					'penalty' => 0
				),
				'total' => array(
					'points' => 0,
					'penalty' => 0
				)
			);

			foreach ($user_problems_score[$user_id] as $problem) {
				$data['total']['points'] += $problem['points'];
				$data['total']['penalty'] += $problem['penalty'];
			}

			// Add contestant results to scoreboard data
			array_push($result, $data);
		}

		return $result;
	}
}
