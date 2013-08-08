<?php

/**
 * RunController
 *
 * @author joemmanuel
 */
class RunController extends Controller {

	public static $grader = null;
	private static $practice = false;		

	/**
	 * Creates an instance of Grader if not already created
	 */
	private static function initializeGrader() {
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
			$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);

			Validators::isInEnum($r["language"], "language", array('kp', 'kj', 'c', 'cpp', 'java', 'py', 'rb', 'pl', 'cs', 'p', 'cat'));
			Validators::isStringNonEmpty($r["source"], "source");

			// Check for practice, there is no contest info in this scenario
			if ($r["contest_alias"] == "" && (Authorization::IsSystemAdmin($r["current_user_id"]) || time() > ProblemsDAO::getPracticeDeadline($r["problem"]->getProblemId()))) {
				if (!RunsDAO::IsRunInsideSubmissionGap(
								null, $r["problem"]->getProblemId(), $r["current_user_id"])
						&& !Authorization::IsSystemAdmin($r["current_user_id"])) {
					throw new NotAllowedToSubmitException();
				}

				self::$practice = true;
				return;
			}

			// Validate contest
			Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");
			$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);

			// Validate that the combination contest_id problem_id is valid            
			if (!ContestProblemsDAO::getByPK(
							$r["contest"]->getContestId(), $r["problem"]->getProblemId()
			)) {
				throw new InvalidParameterException("problem_alias and contest_alias combination is invalid.");
			}

			// Contest admins can skip following checks
			if (!Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
				// Before submit something, contestant had to open the problem/contest
				if (!ContestsUsersDAO::getByPK($r["current_user_id"], $r["contest"]->getContestId())) {
					throw new ForbiddenAccessException("Unable to submit run: You must open the problem before trying to submit a solution.");
				}

				// Validate that the run is timely inside contest
				if (!$r["contest"]->isInsideContest($r["current_user_id"])) {
					throw new ForbiddenAccessException("Unable to submit run: Contest time has expired or not started yet.");
				}

				// Validate if contest is private then the user should be registered
				if ($r["contest"]->getPublic() == 0
						&& is_null(ContestsUsersDAO::getByPK(
										$r["current_user_id"], $r["contest"]->getContestId()))) {
					throw new ForbiddenAccessException("Unable to submit run: You are not registered to this contest.");
				}

				// Validate if the user is allowed to submit given the submissions_gap 			
				if (!RunsDAO::IsRunInsideSubmissionGap(
								$r["contest"]->getContestId(), $r["problem"]->getProblemId(), $r["current_user_id"])) {
					throw new NotAllowedToSubmitException("Unable to submit run: You have to wait " . $r["contest"]->getSubmissionsGap() . " seconds between consecutive submissions.");
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
		self::initializeGrader();

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
			$penalty_time_start = $r["contest"]->getPenaltyTimeStart();

			switch ($penalty_time_start) {
				case "contest":
					// submit_delay is calculated from the start
					// of the contest
					$start = $r["contest"]->getStartTime();
					break;

				case "problem":
					// submit delay is calculated from the 
					// time the user opened the problem
					$opened = ContestProblemOpenedDAO::getByPK(
									$r["contest"]->getContestId(), $r["problem"]->getProblemId(), $r["current_user_id"]
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

			$contest_id = $r["contest"]->getContestId();
			$test = Authorization::IsContestAdmin($r["current_user_id"], $r["contest"]) ? 1 : 0;
		}

		// Populate new run object
		$run = new Runs(array(
					"user_id" => $r["current_user_id"],
					"problem_id" => $r["problem"]->getProblemId(),
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
			
			// Update submissions counter++
			$r["problem"]->setSubmissions($r["problem"]->getSubmissions() + 1);
			ProblemsDAO::save($r["problem"]);
			
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
				$contest_user = ContestsUsersDAO::getByPK($r["current_user_id"], $r["contest"]->getContestId());

				if ($r["contest"]->getWindowLength() === null) {
					$response['submission_deadline'] = strtotime($r["contest"]->getFinishTime());
				} else {
					$response['submission_deadline'] = min(strtotime($r["contest"]->getFinishTime()), strtotime($contest_user->getAccessTime()) + $r["contest"]->getWindowLength() * 60);
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
			self::InvalidateScoreboardCache($r["contest"]->getContestId());
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
			$r["run"] = RunsDAO::getByAlias($r["run_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($r["run"])) {
			throw new NotFoundException("Run not found");
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

		if (!(Authorization::CanViewRun($r["current_user_id"], $r["run"]))) {
			throw new ForbiddenAccessException();
		}

		// Fill response
		$relevant_columns = array("guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");
		$filtered = $r["run"]->asFilteredArray($relevant_columns);
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
		self::initializeGrader();

		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		if (!(Authorization::CanEditRun($r["current_user_id"], $r["run"]))) {
			throw new ForbiddenAccessException();
		}

		Logger::log("Run being rejudged!!");

		// Try to delete compile message, if exists.
		try {
			$grade_err = RUNS_PATH . '/../grade/' . $r["run"]->getRunId() . '.err';
			if (file_exists($grade_err)) {
				unlink($grade_err);
			}
		} catch (Exception $e) {
			// Soft error :P
			Logger::warn($e);
		}

		try {
			self::$grader->Grade($r["run"]->getRunId());
		} catch (Exception $e) {
			Logger::error("Call to Grader::grade() failed:");
			Logger::error($e);
		}

		$response = array();
		$response['status'] = 'ok';
		
		
		self::invalidateCacheOnRejudge($r["run"]);	
		
		// Expire details of the run
		$runAdminDetailsCache = new Cache(Cache::RUN_ADMIN_DETAILS, $r["run"]->getRunId());
		$runAdminDetailsCache->delete();

		return $response;	
	}
	
	/**
	 * Invalidates relevant caches on run rejudge
	 * 
	 * @param RunsDAO $run
	 */
	public static function invalidateCacheOnRejudge(Runs $run) {
						
		try {
			$contest = ContestsDAO::getByPK($run->getContestId());
			
			// If the run belongs to a contest, we need to invalidate that scoreboard
			if (!is_null($contest)) {
				self::InvalidateScoreboardCache($contest->getContestId());
			}
			
			// Now we need to invalidate problem stats
			$problem = ProblemsDAO::getByPK($run->getProblemId());
			
			if (!is_null($problem)) {
				// Invalidar cache stats
				$problemStatsCache = new Cache(Cache::PROBLEM_STATS, $problem->getAlias());
				$problemStatsCache->delete();
			}
			
		} catch (Exception $e) {
			// We did our best effort to invalidate the cache...
			Logger::warn("Failed to invalidate cache on Rejudge, skipping: ");
			Logger::warn($e);			
		}
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

		if (!(Authorization::CanEditRun($r["current_user_id"], $r["run"]))) {
			throw new ForbiddenAccessException();
		}

		// Get the problem
		try {
			$problem = ProblemsDAO::getByPK($r["run"]->getProblemId());
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}		
		
		$runAdminDetailsCache = new Cache(Cache::RUN_ADMIN_DETAILS, $r["run"]->getRunId());
		$response = $runAdminDetailsCache->get();
		
		if (is_null($response))
		{
			$response = array();		

			$problem_dir = PROBLEMS_PATH . '/' . $problem->getAlias() . '/cases/';
			$grade_dir = RUNS_PATH . '/../grade/' . $r["run"]->getRunId();

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
			$response['source'] = file_get_contents(RUNS_PATH . '/' . $r["run"]->getGuid());
			$response["status"] = "ok";
			
			// Save cache only if run was already graded
			if ($r["run"]->getStatus() === 'ready') {
				$runAdminDetailsCache->set($response, 0);
			}
		}

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

		if (!(Authorization::CanViewRun($r["current_user_id"], $r["run"]))) {
			throw new ForbiddenAccessException();
		}

		$response = array();

		// Get the source
		$response['source'] = file_get_contents(RUNS_PATH . '/' . $r["run"]->getGuid());

		// Get the error
		$grade_dir = RUNS_PATH . '/../grade/' . $r["run"]->getRunId();
		if (file_exists("$grade_dir.err")) {
			$response['compile_error'] = file_get_contents("$grade_dir.err");
		}

		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Given the run alias, returns a .zip file with all the .out files generated for a run.
	 * 
	 * @param Request $r
	 * @throws ForbiddenAccessException
	 */
	public static function apiDownload(Request $r) {
		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		$r["contest"] = ContestsDAO::getByPK($r["run"]->getContestId());

		if (!Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
			throw new ForbiddenAccessException();
		}

		$problem = ProblemsDAO::getByPK($r["run"]->getProblemId());

		$problem_dir = PROBLEMS_PATH . '/' . $problem->getAlias() . '/cases/';
		$grade_dir = RUNS_PATH . '/../grade/' . $r["run"]->getRunId();

		$cases = array();

		$zip = new ZipStream($r["run"]->getGuid() . '.zip');

		if (is_dir($grade_dir)) {
			if ($dir = opendir($grade_dir)) {
				while (($file = readdir($dir)) !== false) {
					if ($file == '.' || $file == '..' || !strstr($file, ".out"))
						continue;

					$zip->add_file_from_path("$file", "$grade_dir/$file");
				}
				closedir($dir);
			}
		}

		$zip->finish();
		die();
	}
	
	/**
	 * Get total of last 6 months
	 * 
	 * @param Request $r
	 * @return type
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiCounts(Request $r) {
		
		$totalsCache = new Cache(Cache::RUN_COUNTS, "");
		$totals = $totalsCache->get();
		
		if (is_null($totals)) {
			$totals = array();
			$totals["total"] = array();
			$totals["ac"] = array();
			try {

				// I don't like this approach but adodb didn't like too much to execute
				// store procedures. anyways we will cache the totals
				$date = date('Y-m-d', strtotime('1 days'));
				for ($i = 0; $i < 30 * 6; $i++) {
					$totals["total"][$date] = RunsDAO::GetRunCountsToDate($date);
					$totals["ac"][$date] = RunsDAO::GetAcRunCountsToDate($date);
					$date = date('Y-m-d', strtotime('-'.$i.' days'));
				}
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}
			
			$totalsCache->set($totals, 24*60*60);
		}
						
		return $totals;
	}
	
	/**
	 * Validator for List API
	 * 
	 * @param Request $r
	 * @throws ForbiddenAccessException
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	private static function validateList(Request $r) {
		
		// Defaults for offset and rowcount
		if (!isset($r["offset"])) {
			$r["offset"] = 0;
		}
		if (!isset($r["rowcount"])) {
			$r["rowcount"] = 100;
		}
		
		if (!Authorization::IsSystemAdmin($r["current_user_id"])) {
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

		Validators::isInEnum($r["language"], "language", array('c', 'cpp', 'java', 'py', 'rb', 'pl', 'cs', 'p', 'kp', 'kj', 'cat'), false);
		
		// Get user if we have something in username
		if (!is_null($r["username"])) {
			$r["user"] = UserController::resolveUser($r["username"]);
		}
		
	}
	
	/**
	 * Gets a list of latest runs overall
	 * 
	 * @param Request $r
	 * @return string
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiList(Request $r) {
		
		// Authenticate request
		self::authenticateRequest($r);
		
		self::validateList($r);
		
		$runs_mask = null;

		// Get all runs for problem given        
		$runs_mask = new Runs(array(					
					"status" => $r["status"],
					"veredict" => $r["veredict"],
					"problem_id" => !is_null($r["problem"]) ? $r["problem"]->getProblemId() : null,
					"language" => $r["language"],
					"user_id" => !is_null($r["user"]) ? $r["user"]->getUserId() : null,
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
