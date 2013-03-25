<?php

/**
 * ContestController
 * 
 */
class ContestController extends Controller {

	private static $private_users_list;
	private static $hasPrivateUsers;
	private static $problems;
	private static $contest;

	/**
	 * Returns a list of contests
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiList(Request $r) {

		// Check who is visiting, but a not logged user can still view
		// the list of contests
		try {
			self::authenticateRequest($r);
		} catch (ForbiddenAccessException $e) {
			// Do nothing
		}

		// Create array of relevant columns
		$relevant_columns = array("contest_id", "title", "description", "start_time", "finish_time", "public", "alias", "director_id", "window_length");

		try {
			// Get all contests using only relevan columns
			$contests = ContestsDAO::getAll(NULL, NULL, 'contest_id', "DESC", $relevant_columns);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		// DAO requires contest_id as relevant column but we don't want to expose it
		array_shift($relevant_columns);

		/**
		 * Ok, lets go 1 by 1, and if its public, show it,
		 * if its not, check if the user has access to it.
		 * */
		$addedContests = array();

		foreach ($contests as $c) {
			// At most we want 10 contests @TODO paginar correctamente
			if ( sizeof($addedContests) == 10 ) {
				break;
			}

			if ($c->getPublic()) {

				$c->toUnixTime();

				$contestInfo = $c->asFilteredArray($relevant_columns);
				$contestInfo["duration"] = (is_null($c->getWindowLength()) ?
								$c->getFinishTime() - $c->getStartTime() : ($c->getWindowLength() * 60));

				$addedContests[] = $contestInfo;
				continue;
			}

			/*
			 * Ok, its not public, lets se if we have a 
			 * valid user
			 * */
			if ($r["current_user_id"] === null) {
				continue;
			}

			/**
			 * Ok, i have a user. Can he see this contest ?
			 * */
			try {
				$contestUser = ContestsUsersDAO::getByPK($r["current_user_id"], $c->getContestId());
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}

			// Admins can see all contests
			if ($contestUser === null && !Authorization::IsSystemAdmin($r["current_user_id"])) {
				/**
				 * Nope, he cant .
				 * */
				continue;
			}

			/**
			 * He can see it !
			 * 
			 * */
			$c->toUnixTime();
			$contestInfo = $c->asFilteredArray($relevant_columns);
			$contestInfo["duration"] = (is_null($c->getWindowLength()) ?
							$c->getFinishTime() - $c->getStartTime() : ($c->getWindowLength() * 60));

			$addedContests[] = $contestInfo;
		}

		return array(
			"number_of_results" => sizeof($addedContests),
			"results" => $addedContests
		);
	}

	/**
	 * Validate request of a details contest
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 * @throws Exception
	 * @throws ForbiddenAccessException
	 * @throws PreconditionFailedException
	 */
	private static function validateDetails(Request $r) {

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		// If the contest is private, verify that our user is invited
		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null(self::$contest)) {
			throw new NotFoundException("Contest not found");
		}


		if (self::$contest->getPublic() === '0') {
			try {
				if (is_null(ContestsUsersDAO::getByPK($r["current_user_id"], self::$contest->getContestId())) && !Authorization::IsContestAdmin($r["current_user_id"], self::$contest)) {
					throw new ForbiddenAccessException();
				}
			} catch (ApiException $e) {
				// Propagate exception
				throw $e;
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}
		}

		// If the contest has not started, user should not see it, unless it is admin
		if (!self::$contest->hasStarted($r["current_user_id"]) && !Authorization::IsContestAdmin($r["current_user_id"], self::$contest)) {
			$exception = new PreconditionFailedException("Contest has not started yet.");
			$exception->addCustomMessageToArray("start_time", strtotime(self::$contest->getStartTime()));

			throw $exception;
		}
	}

	/**
	 * Returns details of a Contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiDetails(Request $r) {

		// Crack the request to get the current user
		self::authenticateRequest($r);

		self::validateDetails($r);

		// Check cache first
		$cache = new Cache(Cache::CONTEST_INFO, $r["contest_alias"]);
		$result = $cache->get();

		if (is_null($result)) {
			// Create array of relevant columns
			$relevant_columns = array("title", "description", "start_time", "finish_time", "window_length", "alias", "scoreboard", "points_decay_factor", "partial_score", "submissions_gap", "feedback", "penalty", "time_start", "penalty_time_start", "penalty_calc_policy");

			// Initialize response to be the contest information
			$result = self::$contest->asFilteredArray($relevant_columns);

			$result['start_time'] = strtotime($result['start_time']);
			$result['finish_time'] = strtotime($result['finish_time']);

			// Get problems of the contest
			$key_problemsInContest = new ContestProblems(
							array("contest_id" => self::$contest->getContestId()));
			try {
				$problemsInContest = ContestProblemsDAO::search($key_problemsInContest, "order");
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}

			// Add info of each problem to the contest
			$problemsResponseArray = array();

			// Set of columns that we want to show through this API. Doesn't include the SOURCE
			$relevant_columns = array("title", "alias", "validator", "time_limit", "memory_limit", "visits", "submissions", "accepted", "dificulty", "order");

			foreach ($problemsInContest as $problemkey) {
				try {
					// Get the data of the problem
					$temp_problem = ProblemsDAO::getByPK($problemkey->getProblemId());
				} catch (Exception $e) {
					// Operation failed in the data layer
					throw new InvalidDatabaseOperationException($e);
				}

				// Add the 'points' value that is stored in the ContestProblem relationship
				$temp_array = $temp_problem->asFilteredArray($relevant_columns);
				$temp_array["points"] = $problemkey->getPoints();

				// Save our array into the response
				array_push($problemsResponseArray, $temp_array);
			}

			// Save the time of the first access
			try {
				$contest_user = ContestsUsersDAO::CheckAndSaveFirstTimeAccess(
								$r["current_user_id"], self::$contest->getContestId());
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}

			// Add problems to response
			$result['problems'] = $problemsResponseArray;

			$cache->set($result, APC_USER_CACHE_CONTEST_INFO_TIMEOUT);
		}// closes if( $result == null )
		// Adding timer info separately as it depends on the current user and we don't
		// want this to get generally cached for everybody
		// Save the time of the first access
		try {
			$contest_user = ContestsUsersDAO::CheckAndSaveFirstTimeAccess(
							$r["current_user_id"], self::$contest->getContestId());
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		// Add time left to response
		if (self::$contest->getWindowLength() === null) {
			$result['submission_deadline'] = strtotime(self::$contest->getFinishTime());
		} else {
			$result['submission_deadline'] = min(
					strtotime(self::$contest->getFinishTime()), strtotime($contest_user->getAccessTime()) + self::$contest->getWindowLength() * 60);
		}

		$result["status"] = "ok";
		return $result;
	}

	/**
	 * Creates a new contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws DuplicatedEntryInDatabaseException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiCreate(Request $r) {

		// Authenticate user
		self::authenticateRequest($r);

		// Validate request
		self::validateCreateOrUpdate($r);

		// Create and populate a new Contests object
		$contest = new Contests();

		$contest->setPublic($r["public"]);
		$contest->setTitle($r["title"]);
		$contest->setDescription($r["description"]);
		$contest->setStartTime(gmdate('Y-m-d H:i:s', $r["start_time"]));
		$contest->setFinishTime(gmdate('Y-m-d H:i:s', $r["finish_time"]));
		$contest->setWindowLength($r["window_length"] == "NULL" ? NULL : $r["window_length"]);
		$contest->setDirectorId($r["current_user_id"]);
		$contest->setRerunId(0); // NYI
		$contest->setAlias($r["alias"]);
		$contest->setScoreboard($r["scoreboard"]);
		$contest->setPointsDecayFactor($r["points_decay_factor"]);
		$contest->setPartialScore($r["partial_score"]);
		$contest->setSubmissionsGap($r["submissions_gap"]);
		$contest->setFeedback($r["feedback"]);
		$contest->setPenalty(max(0, intval($r["penalty"])));
		$contest->setPenaltyTimeStart($r["penalty_time_start"]);
		$contest->setPenaltyCalcPolicy($r["penalty_calc_policy"]);

		if (!is_null($r["show_scoreboard_after"])) {
			$contest->setShowScoreboardAfter($r["show_scoreboard_after"]);
		} else {
			$contest->setShowScoreboardAfter("1");
		}


		// Push changes
		try {
			// Begin a new transaction
			ContestsDAO::transBegin();

			// Save the contest object with data sent by user to the database
			ContestsDAO::save($contest);

			// If the contest is private, add the list of allowed users
			if ($r["public"] == 0 && self::$hasPrivateUsers) {
				foreach (self::$private_users_list as $userkey) {
					// Create a temp DAO for the relationship
					$temp_user_contest = new ContestsUsers(array(
								"contest_id" => $contest->getContestId(),
								"user_id" => $userkey,
								"access_time" => "0000-00-00 00:00:00",
								"score" => 0,
								"time" => 0
							));

					// Save the relationship in the DB
					ContestsUsersDAO::save($temp_user_contest);
				}
			}

			if (!is_null($r['problems'])) {
				foreach (self::$problems as $problem) {
					$contest_problem = new ContestProblems(array(
								'contest_id' => $contest->getContestId(),
								'problem_id' => $problem['id'],
								'points' => $problem['points']
							));

					ContestProblemsDAO::save($contest_problem);
				}
			}

			// End transaction transaction
			ContestsDAO::transEnd();
		} catch (Exception $e) {
			// Operation failed in the data layer, rollback transaction 
			ContestsDAO::transRollback();

			// Alias may be duplicated, 1062 error indicates that
			if (strpos($e->getMessage(), "1062") !== FALSE) {
				throw new DuplicatedEntryInDatabaseException("alias already exists. Please choose a different alias.", $e);
			} else {
				throw new InvalidDatabaseOperationException($e);
			}
		}

		Logger::log("New Contest Created: " . $r['alias']);
		return array("status" => "ok");
	}

	/**
	 * Validates that Request contains expected data to create or update a contest
	 * In case of update, everything is optional except the contest_alias
	 * In case of error, this function throws.
	 * 
	 * @param Request $r
	 * @throws InvalidParameterException
	 */
	private static function validateCreateOrUpdate(Request $r, $is_update = false) {

		// Is the parameter required? 
		$is_required = true;

		if ($is_update === true) {

			// In case of Update API, required parameters for Create API are not required
			$is_required = false;

			try {
				$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}

			if (is_null($r["contest_alias"])) {
				throw new NotFoundException();
			}
		}

		Validators::isStringNonEmpty($r["title"], "title", $is_required);
		Validators::isStringNonEmpty($r["description"], "description", $is_required);

		Validators::isNumber($r["start_time"], "start_time", $is_required);
		Validators::isNumber($r["finish_time"], "finish_time", $is_required);

		// Get the actual start and finish time of the contest, considering that
		// in case of update, parameters can be optional
		$start_time = !is_null($r["start_time"]) ? $r["start_time"] : $r["contest"]->getStartTime();
		$finish_time = !is_null($r["finish_time"]) ? $r["finish_time"] : $r["contest"]->getFinishTime();

		// Validate start & finish time
		if ($start_time > $finish_time) {
			throw new InvalidParameterException("start_time cannot be after finish_time");
		}

		// Calculate the actual contest length
		$contest_length = $finish_time - $start_time;

		// Window_length is optional
		Validators::isNumberInRange(
				$r["window_length"], "window_length", 0, floor($contest_length) / 60, false
		);

		Validators::isInEnum($r["public"], "public", array("0", "1"), $is_required);
		Validators::isStringOfMaxLength($r["alias"], "alias", 32, $is_required);
		Validators::isNumberInRange($r["scoreboard"], "scoreboard", 0, 100, $is_required);
		Validators::isNumberInRange($r["points_decay_factor"], "points_decay_factor", 0, 1, $is_required);
		Validators::isInEnum($r["partial_score"], "partial_score", array("0", "1"), $is_required);
		Validators::isNumberInRange($r["submissions_gap"], "submissions_gap", 0, $contest_length, $is_required);

		Validators::isInEnum($r["feedback"], "feedback", array("no", "yes", "partial"), $is_required);
		Validators::isInEnum($r["penalty_time_start"], "penalty_time_start", array("contest", "problem", "none"), $is_required);
		Validators::isInEnum($r["penalty_calc_policy"], "penalty_calc_policy", array("sum", "max"), $is_required);

		// Check that the users passed through the private_users parameter are valid
		if (!is_null($r["public"]) && $r["public"] == 0 && !is_null($r["private_users"])) {
			// Validate that the request is well-formed			
			self::$private_users_list = json_decode($r["private_users"]);
			if (is_null(self::$private_users_list)) {
				throw new InvalidParameterException("private_users" . Validators::IS_INVALID);
			}

			// Validate that all users exists in the DB
			foreach (self::$private_users_list as $userkey) {
				if (is_null(UsersDAO::getByPK($userkey))) {
					throw new InvalidParameterException("private_users contains a user that doesn't exists");
				}
			}

			// Turn on flag to add private users later
			self::$hasPrivateUsers = true;
		}

		// Problems is optional
		if (!is_null($r['problems'])) {
			self::$problems = array();

			foreach (json_decode($r['problems']) as $problem) {
				$p = ProblemsDAO::getByAlias($problem->problem);
				array_push(self::$problems, array(
					'id' => $p->getProblemId(),
					'alias' => $problem->problem,
					'points' => $problem->points
				));
			}
		}

		// Show scoreboard is always optional
		Validators::isInEnum($r["show_scoreboard_after"], "show_scoreboard_after", array("0", "1"), false);
	}

	/**
	 * Adds a problem to a contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiAddProblem(Request $r) {

		// Authenticate user
		self::authenticateRequest($r);

		// Validate the request and get the problem and the contest in an array
		$params = self::validateAddToContestRequest($r);

		try {
			$relationship = new ContestProblems(array(
						"contest_id" => $params["contest"]->getContestId(),
						"problem_id" => $params["problem"]->getProblemId(),
						"points" => $r["points"],
						"order" => is_null($r["order_in_contest"]) ?
								1 : $r["order_in_contest"]));

			ContestProblemsDAO::save($relationship);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		// Invalidar cache
		$contestCache = new Cache(Cache::CONTEST_INFO, $r["contest_alias"]);
		$contestCache->delete();				
		
		return array("status" => "ok");
	}

	/**
	 * Validates the request for AddToContest and returns an array with 
	 * the problem and contest DAOs
	 * 
	 * @throws InvalidDatabaseOperationException
	 * @throws InvalidParameterException
	 * @throws ForbiddenAccessException
	 */
	private static function validateAddToContestRequest(Request $r) {

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		// Only director is allowed to create problems in contest
		try {
			$contest = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($contest)) {
			throw new InvalidParameterException("Contest not found");
		}

		// Only contest admin is allowed to create problems in contest
		if (!Authorization::IsContestAdmin($r["current_user_id"], $contest)) {
			throw new ForbiddenAccessException("Cannot add problem. You are not the contest director.");
		}

		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		try {
			$problem = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($problem)) {
			throw new InvalidParameterException("Problem not found");
		}

		Validators::isNumberInRange($r["points"], "points", 0, INF);
		Validators::isNumberInRange($r["order_in_contest"], "order_in_contest", 0, INF, false);

		return array(
			"contest" => $contest,
			"problem" => $problem);
	}

	/**
	 * Adds a user to a contest.
	 * By default, any user can view details of public contests.
	 * Only users added through this API can view private contests
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	public static function apiAddUser(Request $r) {

		// Authenticate logged user
		self::authenticateRequest($r);


		$user_to_add = null;

		// Check contest_alias        
		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		Validators::isNumber($r["user_id"], "user_id");

		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
			$user_to_add = UsersDAO::getByPK($r["user_id"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($user_to_add)) {
			throw new InvalidParameterException("User provided does not exists");
		}

		// Only director is allowed to create problems in contest
		if (!Authorization::IsContestAdmin($r["current_user_id"], self::$contest)) {
			throw new ForbiddenAccessException();
		}

		$contest_user = new ContestsUsers();
		$contest_user->setContestId(self::$contest->getContestId());
		$contest_user->setUserId($r["user_id"]);
		$contest_user->setAccessTime("0000-00-00 00:00:00");
		$contest_user->setScore("0");
		$contest_user->setTime("0");

		// Save the contest to the DB
		try {
			ContestsUsersDAO::save($contest_user);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		return array("status" => "ok");
	}

	/**
	 * Validate the Clarifications request
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 */
	private static function validateClarifications(Request $r) {

		// Check contest_alias        
		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		Validators::isNumber($r["offset"], "offset", false /* optional */);
		Validators::isNumber($r["rowcount"], "rowcount", false /* optional */);
	}

	/**
	 * 
	 * Get clarifications of a contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiClarifications(Request $r) {

		// Authenticate user
		self::authenticateRequest($r);

		// Validate request
		self::validateClarifications($r);

		$offset = is_null($r["offset"]) ? 0 : $r["offset"];
		$rowcount = is_null($r["rowcount"]) ? 0 : $r["rowcount"];

		// Create array of relevant columns
		$relevant_columns = array("clarification_id", "problem_alias", "message", "answer", "time", "public");


		$public_clarification_mask = new Clarifications(array(
					"public" => '1',
					"contest_id" => self::$contest->getContestId()
				));

		$is_contest_director = Authorization::IsContestAdmin($r["current_user_id"], self::$contest);

		// If user is the contest director, get all private clarifications        
		if ($is_contest_director) {
			// Get all private clarifications 
			$private_clarification_mask = new Clarifications(array(
						"public" => '0',
						"contest_id" => self::$contest->getContestId()
					));
		} else {
			// Get private clarifications of the user 
			$private_clarification_mask = new Clarifications(array(
						"public" => '0',
						"contest_id" => self::$contest->getContestId(),
						"author_id" => $r["current_user_id"]
					));
		}

		//@todo This query could be merged and optimized 
		// Get our clarifications given the masks
		try {
			$clarifications_public = ClarificationsDAO::search($public_clarification_mask);
			$clarifications_private = ClarificationsDAO::search($private_clarification_mask);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		$clarifications_array = array();

		// Filter each Public clarification and add it to the response        
		foreach ($clarifications_public as $clarification) {
			$clar = $clarification->asFilteredArray($relevant_columns);
			$clar['can_answer'] = $is_contest_director;

			// Add author in case of contest_director
			if ($is_contest_director) {
				try {
					$author_user = UsersDAO::getByPK($clarification->getAuthorId());
					$clar['author'] = $author_user->getUsername();
				} catch (Exception $e) {
					throw new InvalidDatabaseOperationException($e);
				}
			}

			array_push($clarifications_array, $clar);
		}

		// Filter each Private clarification and add it to the response
		foreach ($clarifications_private as $clarification) {
			$clar = $clarification->asFilteredArray($relevant_columns);
			$clar['can_answer'] = $is_contest_director;

			// Add author in case of contest_director
			if ($is_contest_director) {
				try {
					$author_user = UsersDAO::getByPK($clarification->getAuthorId());
					$clar['author'] = $author_user->getUsername();
				} catch (Exception $e) {
					throw new InvalidDatabaseOperationException($e);
				}
			}

			array_push($clarifications_array, $clar);
		}

		// Sort final array by time
		usort($clarifications_array, function($a, $b) {
					// First, let's order by answer
					$a_answered = strlen($a['answer']) > 0;
					$b_answered = strlen($b['answer']) > 0;

					// If they have the same status, check 
					if ($a_answered === $b_answered) {
						$t1 = strtotime($a["time"]);
						$t2 = strtotime($b["time"]);

						if ($t1 === $t2)
							return 0;

						return ($t1 > $t2) ? 1 : -1;
					}

					// If a is not answered, it has priority
					if ($a_answered === false) {
						return -1;
					} else {
						return 1;
					}
				});

		// LIMIT the array if rowcount !== 0
		if ($rowcount !== 0) {
			$clarifications_array = array_slice($clarifications_array, $offset, $rowcount, false);
		}

		// Add response to array
		$response = array();
		$response['clarifications'] = $clarifications_array;
		$response['status'] = "ok";

		return $response;
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
		// Get the current user
		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null(self::$contest)) {
			throw new NotFoundException();
		}

		// Create scoreboard
		$scoreboard = new Scoreboard(
						self::$contest->getContestId(),
						Authorization::IsContestAdmin($r["current_user_id"], self::$contest)
		);

		// Push scoreboard data in response
		$response = array();
		$response["events"] = $scoreboard->events();

		return $response;
	}

	/**
	 * Returns the Scoreboard
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	public static function apiScoreboard(Request $r) {

		// Get the current user
		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null(self::$contest)) {
			throw new NotFoundException();
		}

		// Create scoreboard
		$scoreboard = new Scoreboard(
						self::$contest->getContestId(),
						Authorization::IsContestAdmin($r["current_user_id"], self::$contest)
		);

		// Push scoreboard data in response
		$response = array();
		$response["ranking"] = $scoreboard->generate();

		return $response;
	}

	/**
	 * Returns ALL users participating in a contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiUsers(Request $r) {

		// Authenticate request
		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		try {
			$contest = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (!Authorization::IsContestAdmin($r["current_user_id"], $contest)) {
			throw new ForbiddenAccessException();
		}

		// Get users from DB
		$contest_user_key = new ContestsUsers();
		$contest_user_key->setContestId($contest->getContestId());

		try {
			$db_results = ContestsUsersDAO::search($contest_user_key);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		$users = array();

		// Add all users to an array
		foreach ($db_results as $result) {
			$user_id = $result->getUserId();
			$user = UsersDAO::getByPK($user_id);
			$users[] = array("user_id" => $user_id, "username" => $user->getUsername());
		}

		$response = array();
		$response["users"] = $users;
		$response["status"] = "ok";

		return $response;
	}

	/**
	 * Update a Contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiUpdate(Request $r) {

		// Authenticate request
		self::authenticateRequest($r);

		// Validate request
		self::validateCreateOrUpdate($r, true /* is update */);

		// Update contest DAO                
		if (!is_null($r["public"])) {
			$r["contest"]->setPublic($r["public"]);
		}

		if (!is_null($r["title"])) {
			$r["contest"]->setTitle($r["title"]);
		}

		if (!is_null($r["description"])) {
			$r["contest"]->setDescription($r["description"]);
		}

		if (!is_null($r["start_time"])) {
			$r["contest"]->setStartTime(gmdate('Y-m-d H:i:s', $r["start_time"]));
		}

		if (!is_null($r["finish_time"])) {
			$r["contest"]->setFinishTime(gmdate('Y-m-d H:i:s', $r["finish_time"]));
		}

		if (!is_null($r["window_length"])) {
			$r["contest"]->setWindowLength($r["window_length"] == "NULL" ? NULL : $r["window_length"]);
		}

		if (!is_null($r["scoreboard"])) {
			$r["contest"]->setScoreboard($r["scoreboard"]);
		}

		if (!is_null($r["points_decay_factor"])) {
			$r["contest"]->setPointsDecayFactor($r["points_decay_factor"]);
		}

		if (!is_null($r["partial_score"])) {
			$r["contest"]->setPartialScore($r["partial_score"]);
		}

		if (!is_null($r["submissions_gap"])) {
			$r["contest"]->setSubmissionsGap($r["submissions_gap"]);
		}

		if (!is_null($r["feedback"])) {
			$r["contest"]->setFeedback($r["feedback"]);
		}

		if (!is_null($r["penalty"])) {
			$r["contest"]->setPenalty(max(0, intval($r["penalty"])));
		}

		if (!is_null($r["penalty_time_start"])) {
			$r["contest"]->setPenaltyTimeStart($r["penalty_time_start"]);
		}

		if (!is_null($r["penalty_calc_policy"])) {
			$r["contest"]->setPenaltyCalcPolicy($r["penalty_calc_policy"]);
		}

		if (!is_null($r["show_scoreboard_after"])) {
			$contest->setShowScoreboardAfter($r["show_scoreboard_after"]);
		}

		// Push changes
		try {
			// Begin a new transaction
			ContestsDAO::transBegin();

			// Save the contest object with data sent by user to the database
			ContestsDAO::save($r["contest"]);

			// If the contest is private, add the list of allowed users
			if (!is_null($r["public"]) && $r["public"] == 0 && self::$hasPrivateUsers) {
				// Get current users
				$cu_key = new ContestsUsers(array("contest_id" => $r["contest"]->getContestId()));
				$current_users = ContestsUsersDAO::search($cu_key);
				$current_users_id = array();

				foreach ($current_users as $cu) {
					array_push($current_users_id, $current_users->getUserId());
				}

				// Check who needs to be deleted and who needs to be added
				$to_delete = array_diff($current_users_id, self::$private_users_list);
				$to_add = array_diff(self::$private_users_list, $current_users_id);

				// Add users in the request
				foreach ($to_add as $userkey) {
					// Create a temp DAO for the relationship
					$temp_user_contest = new ContestsUsers(array(
								"contest_id" => $r["contest"]->getContestId(),
								"user_id" => $userkey,
								"access_time" => "0000-00-00 00:00:00",
								"score" => 0,
								"time" => 0
							));

					// Save the relationship in the DB
					ContestsUsersDAO::save($temp_user_contest);
				}

				// Delete users 
				foreach ($to_delete as $userkey) {
					// Create a temp DAO for the relationship
					$temp_user_contest = new ContestsUsers(array(
								"contest_id" => $r["contest"]->getContestId(),
								"user_id" => $userkey,
							));

					// Delete the relationship in the DB
					ContestsUsersDAO::delete(ContestProblemsDAO::search($temp_user_contest));
				}
			}

			if (!is_null($r['problems'])) {
				// Get current problems
				$p_key = new Problems(array("contest_id" => $r["contest"]->getContestId()));
				$current_problems = ProblemsDAO::search($p_key);
				$current_problems_id = array();

				foreach ($current_problems as $p) {
					array_push($current_problems_id, $p->getProblemId());
				}

				// Check who needs to be deleted and who needs to be added
				$to_delete = array_diff($current_problems_id, self::$problems_id);
				$to_add = array_diff(self::$problems_id, $current_problems_id);

				foreach ($to_add as $problem) {
					$contest_problem = new ContestProblems(array(
								'contest_id' => $r["contest"]->getContestId(),
								'problem_id' => $problem,
								'points' => self::$problems[$problem]['points']
							));

					ContestProblemsDAO::save($contest_problem);
				}

				foreach ($to_delete as $problem) {
					$contest_problem = new ContestProblems(array(
								'contest_id' => $r["contest"]->getContestId(),
								'problem_id' => $problem,
							));

					ContestProblemsDAO::delete(ContestProblemsDAO::search($contest_problem));
				}
			}

			// End transaction 
			ContestsDAO::transEnd();
		} catch (Exception $e) {
			// Operation failed in the data layer, rollback transaction 
			ContestsDAO::transRollback();

			throw new InvalidDatabaseOperationException($e);
		}

		// Happy ending
		$response = array();
		$response["status"] = 'ok';

		Logger::log("Contest updated (alias): " . $r['contest_alias']);

		return $response;
	}

	/**
	 * Validates runs API
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 * @throws ForbiddenAccessException
	 */
	private static function validateRuns(Request $r) {

		$r["offset"] = 0;
		$r["rowcount"] = 100;

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");

		try {
			$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($r["contest"])) {
			throw new NotFoundException("Contest not found.");
		}

		if (!Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
			throw new ForbiddenAccessException();
		}

		Validators::isNumber($r["offset"], "offset", false);
		Validators::isNumber($r["rowcount"], "rowcount", false);
		Validators::isInEnum($r["status"], "status", array('new', 'waiting', 'compiling', 'running', 'ready'), false);
		Validators::isInEnum($r["veredict"], "veredict", array("AC", "PA", "WA", "TLE", "MLE", "OLE", "RTE", "RFE", "CE", "JE", "NO-AC"), false);


		// Check filter by problem, is optional
		if (!is_null($r["problem_alias"])) {
			Validators::isStringNonEmpty($r["problem_alias"], "problem");

			try {
				$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}

			if (is_null($r["problem"])) {
				throw new NotFoundException("Problem not found.");
			}
		}

		Validators::isInEnum($r["language"], "language", array('c', 'cpp', 'java', 'py', 'rb', 'pl', 'cs', 'p', 'kp', 'kj'), false);
	}

	/**
	 * Returns all runs for a contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiRuns(Request $r) {

		// Authenticate request
		self::authenticateRequest($r);

		// Validate request
		self::validateRuns($r);

		$runs_mask = null;

		// Get all runs for problem given        
		$runs_mask = new Runs(array(
					"contest_id" => $r["contest"]->getContestId(),
					"status" => $r["status"],
					"veredict" => $r["veredict"],
					"problem_id" => !is_null($r["problem"]) ? $r["problem"]->getProblemId() : null,
					"language" => $r["language"]
				));

		// Filter relevant columns
		$relevant_columns = array("run_id", "guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay", "Users.username", "Problems.alias");

		// Get our runs
		try {
			$runs = RunsDAO::search($runs_mask, "time", "DESC", $relevant_columns, $r["offset"], $r["rowcount"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}				

		$relevant_columns[11] = 'username';
		$relevant_columns[12] = 'alias';

		$result = array();

		foreach ($runs as $run) {
			$filtered = $run->asFilteredArray($relevant_columns);
			$filtered['time'] = strtotime($filtered['time']);
			$filtered['score'] = round((float) $filtered['score'], 4);
			$filtered['contest_score'] = round((float) $filtered['contest_score'], 2);
			array_push($result, $filtered);
		}

		$response = array();
		$response["runs"] = $result;
		$response["status"] = "ok";

		return $response;
	}

}
