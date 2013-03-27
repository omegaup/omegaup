<?php

/**
 * RunController
 *
 * @author joemmanuel
 */
class RunController extends Controller {

	public static $grader = null;
	private static $practice = false;
	private static $problem = null;
	private static $contest = null;
	private static $run = null;

	/**
	 * Creates an instance of Grader if not already created
	 */
	private static function initialize() {
		if (is_null(self::$grader)) {
			// Create new grader
			self::$grader = new Grader();
		}

		// Set practice mode OFF by default
		self::$practice = false;
	}

	/**
	 * 
	 * Validates Create Run request 
	 * 
	 * @param Request $r
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 * @throws NotAllowedToSubmitException
	 * @throws InvalidParameterException
	 * @throws ForbiddenAccessException
	 */
	private static function validateCreateRequest(Request $r) {
		try {
			Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

			// Check that problem exists
			self::$problem = ProblemsDAO::getByAlias($r["problem_alias"]);

			Validators::isInEnum($r["language"], "language", array('kp', 'kj', 'c', 'cpp', 'java', 'py', 'rb', 'pl', 'cs', 'p'));
			Validators::isStringNonEmpty($r["source"], "source");

			// Check for practice, there is no contest info in this scenario
			if ($r["contest_alias"] == "" && (Authorization::IsSystemAdmin($r["current_user_id"]) || time() > ProblemsDAO::getPracticeDeadline(self::$problem->getProblemId()))) {
				if (!RunsDAO::IsRunInsideSubmissionGap(
								null, self::$problem->getProblemId(), $r["current_user_id"])
						&& !Authorization::IsSystemAdmin($r["current_user_id"])) {
					throw new NotAllowedToSubmitException();
				}

				self::$practice = true;
				return;
			}

			// Validate contest
			Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);

			// Validate that the combination contest_id problem_id is valid            
			if (!ContestProblemsDAO::getByPK(
							self::$contest->getContestId(), self::$problem->getProblemId()
			)) {
				throw new InvalidParameterException("problem_alias and contest_alias combination is invalid.");
			}

			// Contest admins can skip following checks
			if (!Authorization::IsContestAdmin($r["current_user_id"], self::$contest)) {
				// Before submit something, contestant had to open the problem/contest
				if (!ContestsUsersDAO::getByPK($r["current_user_id"], self::$contest->getContestId())) {
					throw new ForbiddenAccessException("Unable to submit run: You must open the problem before trying to submit a solution.");
				}

				// Validate that the run is timely inside contest
				if (!self::$contest->isInsideContest($r["current_user_id"])) {
					throw new ForbiddenAccessException("Unable to submit run: Contest time has expired or not started yet.");
				}

				// Validate if contest is private then the user should be registered
				if (self::$contest->getPublic() == 0
						&& is_null(ContestsUsersDAO::getByPK(
										$r["current_user_id"], self::$contest->getContestId()))) {
					throw new ForbiddenAccessException("Unable to submit run: You are not registered to this contest.");
				}

				// Validate if the user is allowed to submit given the submissions_gap 			
				if (!RunsDAO::IsRunInsideSubmissionGap(
								self::$contest->getContestId(), self::$problem->getProblemId(), $r["current_user_id"])) {
					throw new NotAllowedToSubmitException("Unable to submit run: You have to wait " . self::$contest->getSubmissionsGap() . " seconds between consecutive submissions.");
				}
			}
		} catch (ApiException $apiException) {
			// Propagate ApiException
			throw $apiException;
		} catch (Exception $e) {
			// Operation failed in the data layer			
			throw new InvalidDatabaseOperationException($e);
		}
	}

	/**
	 * Create a new run 
	 * 
	 * @param Request $r
	 * @return array
	 * @throws Exception
	 * @throws InvalidDatabaseOperationException
	 * @throws InvalidFilesystemOperationException
	 */
	public static function apiCreate(Request $r) {
		// Init
		self::initialize();

		// Authenticate user
		self::authenticateRequest($r);

		// Validate request
		self::validateCreateRequest($r);

		Logger::log("New run being submitted !!");
		$response = array();

		if (self::$practice) {
			$submit_delay = 0;
			$contest_id = null;
			$test = 0;
		} else {
			//check the kind of penalty_time_start for this contest
			$penalty_time_start = self::$contest->getPenaltyTimeStart();

			switch ($penalty_time_start) {
				case "contest":
					// submit_delay is calculated from the start
					// of the contest
					$start = self::$contest->getStartTime();
					break;

				case "problem":
					// submit delay is calculated from the 
					// time the user opened the problem
					$opened = ContestProblemOpenedDAO::getByPK(
									self::$contest->getContestId(), self::$problem->getProblemId(), $r["current_user_id"]
					);

					if (is_null($opened)) {
						//holy moly, he is submitting a run 
						//and he hasnt even opened the problem
						//what should be done here?
						Logger::error("User is submitting a run and he has not even opened the problem");
						throw new Exception("User is submitting a run and he has not even opened the problem");
					}

					$start = $opened->getOpenTime();
					break;

				case "none":
					//we dont care
					$start = null;
					break;

				default:
					Logger::error("penalty_time_start for this contests is not a valid option, asuming `none`.");
					$start = null;
			}

			if (!is_null($start)) {
				//ok, what time is it now?
				$c_time = time();
				$start = strtotime($start);

				//asuming submit_delay is in minutes
				$submit_delay = (int) (( $c_time - $start ) / 60);
			} else {
				$submit_delay = 0;
			}

			$contest_id = self::$contest->getContestId();
			$test = Authorization::IsContestAdmin($r["current_user_id"], self::$contest) ? 1 : 0;
		}

		// Populate new run object
		$run = new Runs(array(
					"user_id" => $r["current_user_id"],
					"problem_id" => self::$problem->getProblemId(),
					"contest_id" => $contest_id,
					"language" => $r["language"],
					"source" => $r["source"],
					"status" => "new",
					"runtime" => 0,
					"memory" => 0,
					"score" => 0,
					"contest_score" => 0,
					"ip" => $_SERVER['REMOTE_ADDR'],
					"submit_delay" => $submit_delay, /* based on penalty_time_start */
					"guid" => md5(uniqid(rand(), true)),
					"veredict" => "JE",
					"test" => $test
				));

		try {
			// Push run into DB
			RunsDAO::save($run);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		try {
			// Create file for the run        
			$filepath = RUNS_PATH . DIRECTORY_SEPARATOR . $run->getGuid();
			FileHandler::CreateFile($filepath, $r["source"]);
		} catch (Exception $e) {
			throw new InvalidFilesystemOperationException($e);
		}

		// Call Grader
		try {
			self::$grader->Grade($run->getRunId());
		} catch (Exception $e) {
			Logger::error("Call to Grader::grade() failed:");
			Logger::error($e);
		}

		if (self::$practice) {
			$response['submission_deadline'] = 0;
		} else {
			// Add remaining time to the response
			try {
				$contest_user = ContestsUsersDAO::getByPK($r["current_user_id"], self::$contest->getContestId());

				if (self::$contest->getWindowLength() === null) {
					$response['submission_deadline'] = strtotime(self::$contest->getFinishTime());
				} else {
					$response['submission_deadline'] = min(strtotime(self::$contest->getFinishTime()), strtotime($contest_user->getAccessTime()) + self::$contest->getWindowLength() * 60);
				}
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}
		}

		// Happy ending
		$response["guid"] = $run->getGuid();
		$response["status"] = "ok";

		if (!self::$practice) {
			/// @todo Invalidate cache only when this run changes a user's score
			///       (by improving, adding penalties, etc)
			self::InvalidateScoreboardCache(self::$contest->getContestId());
		}

		return $response;
	}

	/**
	 * Any new run can potentially change the scoreboard.
	 * When a new run is submitted, the scoreboard cache snapshot is deleted
	 * 
	 * @param int $contest_id
	 */
	private static function InvalidateScoreboardCache($contest_id) {
		
		Logger::log("Invalidating scoreboard cache.");
		
		// Invalidar cache del contestant
		$contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $contest_id);
		$contestantScoreboardCache->delete();

		// Invalidar cache del admin
		$adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $contest_id);
		$adminScoreboardCache->delete();
	}

	/**
	 * Validate request of details
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 * @throws ForbiddenAccessException
	 */
	private static function validateDetailsRequest(Request $r) {
		Validators::isStringNonEmpty($r["run_alias"], "run_alias");

		try {
			// If user is not judge, must be the run's owner.
			self::$run = RunsDAO::getByAlias($r["run_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null(self::$run)) {
			throw new NotFoundException();
		}
	}

	/**
	 * Get basic details of a run
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidFilesystemOperationException
	 */
	public static function apiStatus(Request $r) {
		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		if (!(Authorization::CanViewRun($r["current_user_id"], self::$run))) {
			throw new ForbiddenAccessException();
		}

		// Fill response
		$relevant_columns = array("guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");
		$filtered = self::$run->asFilteredArray($relevant_columns);
		$filtered['time'] = strtotime($filtered['time']);
		$filtered['score'] = round((float) $filtered['score'], 4);
		$filtered['contest_score'] = round((float) $filtered['contest_score'], 2);

		$response = $filtered;

		return $response;
	}

	/**
	 * Re-sends a problem to Grader.
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiRejudge(Request $r) {
		// Init
		self::initialize();

		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		if (!(Authorization::CanEditRun($r["current_user_id"], self::$run))) {
			throw new ForbiddenAccessException();
		}

		Logger::log("Run being rejudged!!");

		// Try to delete compile message, if exists.
		try {
			$grade_err = RUNS_PATH . '/../grade/' . self::$run->getRunId() . '.err';
			if (file_exists($grade_err)) {
				unlink($grade_err);
			}
		} catch (Exception $e) {
			// Soft error :P
			Logger::warn($e);
		}

		try {
			self::$grader->Grade(self::$run->getRunId());
		} catch (Exception $e) {
			Logger::error("Call to Grader::grade() failed:");
			Logger::error($e);
		}

		$response = array();
		$response['status'] = 'ok';
		
		// If the run belongs to a contest, we need to invalidate that cache		
		try {
			$contest = ContestsDAO::getByPK(self::$run->getContestId());
			
			if (!is_null($contest)) {
				self::InvalidateScoreboardCache($contest->getContestId());
			}
		} catch (Exception $e) {
			// We did our best effort to invalidate the cache...
			Logger::error($e);			
		}

		return $response;	
	}

	/**
	 * Gets the full details of a run. Includes diff of cases
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiAdminDetails(Request $r) {
		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		if (!(Authorization::CanEditRun($r["current_user_id"], self::$run))) {
			throw new ForbiddenAccessException();
		}

		// Get the problem
		try {
			$problem = ProblemsDAO::getByPK(self::$run->getProblemId());
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();

		$problem_dir = PROBLEMS_PATH . '/' . $problem->getAlias() . '/cases/';
		$grade_dir = RUNS_PATH . '/../grade/' . self::$run->getRunId();

		$cases = array();

		if (file_exists("$grade_dir.err")) {
			$response['compile_error'] = file_get_contents("$grade_dir.err");
		} else if (is_dir($grade_dir)) {
			if ($dir = opendir($grade_dir)) {
				while (($file = readdir($dir)) !== false) {
					if ($file == '.' || $file == '..' || !strstr($file, ".meta"))
						continue;

					$case = array('name' => str_replace(".meta", "", $file), 'meta' => self::ParseMeta(file_get_contents("$grade_dir/$file")));

					if (file_exists("$grade_dir/" . str_replace(".meta", ".out", $file))) {
						$out = str_replace(".meta", ".out", $file);
						$case['out_diff'] = `diff -wuBbi $problem_dir/$out $grade_dir/$out | tail -n +3 | head -n50`;
					}

					if (file_exists("$grade_dir/" . str_replace(".meta", ".err", $file))) {
						$err = "$grade_dir/" . str_replace(".meta", ".err", $file);
						$case['err'] = file_get_contents($err);
					}

					array_push($cases, $case);
				}
				closedir($dir);
			}
		}

		usort($cases, array("RunController", "MetaCompare"));

		$response['cases'] = $cases;
		$response['source'] = file_get_contents(RUNS_PATH . '/' . self::$run->getGuid());
		$response["status"] = "ok";

		return $response;
	}

	/**
	 * Parses Run metadata
	 * 
	 * @param string $meta
	 * @return array
	 */
	private static function ParseMeta($meta) {
		$ans = array();

		foreach (explode("\n", trim($meta)) as $line) {
			list($key, $value) = explode(":", trim($line));
			$ans[$key] = $value;
		}

		return $ans;
	}

	/**
	 * Compare two Run metadata
	 * 
	 * @param array $a
	 * @param array $b
	 * @return boolean
	 */
	public static function MetaCompare($a, $b) {
		if ($a['name'] == $b['name'])
			return 0;

		return ($a['name'] < $b['name']) ? -1 : 1;
	}

	/**
	 * Given the run alias, returns the source code and any compile errors if any
	 * Used in the arena, any contestant can view its own codes and compile errors
	 * 
	 * @param Request $r
	 * @throws ForbiddenAccessException
	 */
	public static function apiSource(Request $r) {
		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		if (!(Authorization::CanViewRun($r["current_user_id"], self::$run))) {
			throw new ForbiddenAccessException();
		}

		$response = array();

		// Get the source
		$response['source'] = file_get_contents(RUNS_PATH . '/' . self::$run->getGuid());

		// Get the error
		$grade_dir = RUNS_PATH . '/../grade/' . self::$run->getRunId();
		if (file_exists("$grade_dir.err")) {
			$response['compile_error'] = file_get_contents("$grade_dir.err");
		}

		$response["status"] = "ok";
		return $response;
	}

}
