<?php

/**
 * RunController
 *
 * @author joemmanuel
 */
class RunController extends Controller {
	public static $defaultSubmissionGap = 60; /*seconds*/
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

			Validators::isInEnum($r["language"], "language", array('kp', 'kj', 'c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'p', 'cat', 'hs'));
			Validators::isStringNonEmpty($r["source"], "source");

			// Check for practice or public problem, there is no contest info in this scenario
			if ($r["contest_alias"] == "") {
				if (Authorization::IsSystemAdmin($r["current_user_id"]) || time() > ProblemsDAO::getPracticeDeadline($r["problem"]->getProblemId()) || $r["problem"]->getPublic() == true) {					
					if (!RunsDAO::IsRunInsideSubmissionGap(
									null, 
									$r["problem"]->getProblemId(),
									$r["current_user_id"])
							&& !Authorization::IsSystemAdmin($r["current_user_id"])) {
							throw new NotAllowedToSubmitException("You have to wait " . self::$defaultSubmissionGap . " between submissions");
					}

					self::$practice = true;
					return;
				} else 
				{
					throw new NotAllowedToSubmitException("The problem is not public.");
				}
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
					throw new NotAllowedToSubmitException("You must open the problem before trying to submit a solution.");
				}

				// Validate that the run is timely inside contest
				if (!ContestsDAO::isInsideContest($r["contest"], $r['current_user_id'])) {
					throw new NotAllowedToSubmitException("Contest time has expired or not started yet.");
				}

				// Validate if contest is private then the user should be registered
				if ($r["contest"]->getPublic() == 0
						&& is_null(ContestsUsersDAO::getByPK(
										$r["current_user_id"], $r["contest"]->getContestId()))) {
					throw new NotAllowedToSubmitException("You are not registered to this contest.");
				}

				// Validate if the user is allowed to submit given the submissions_gap 			
				if (!RunsDAO::IsRunInsideSubmissionGap(
								$r["contest"]->getContestId(), $r["problem"]->getProblemId(), $r["current_user_id"])) {
					throw new NotAllowedToSubmitException("You have to wait " . $r["contest"]->getSubmissionsGap() . " seconds between consecutive submissions.");
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

		self::$log->info("New run being submitted !!");
		$response = array();

		if (self::$practice) {
			if (OMEGAUP_LOCKDOWN) {
				throw new ForbiddenAccessException("lockdown");
			}
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
						self::$log->error("User is submitting a run and he has not even opened the problem");
						throw new Exception("User is submitting a run and he has not even opened the problem");
					}

					$start = $opened->getOpenTime();
					break;

				case "none":
					//we dont care
					$start = null;
					break;

				default:
					self::$log->error("penalty_time_start for this contests is not a valid option, asuming `none`.");
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
			self::$log->error("Call to Grader::grade() failed:");
			self::$log->error($e);
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
		
		// Expire rank cache
		UserController::deleteProblemsSolvedRankCacheList();

		return $response;
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

		self::$log->info("Run being rejudged!!");

		// Try to delete compile message, if exists.
		try {
			$grade_err = RUNS_PATH . '/../grade/' . $r["run"]->getRunId() . '.err';
			if (file_exists($grade_err)) {
				unlink($grade_err);
			}
		} catch (Exception $e) {
			// Soft error :P
			self::$log->warn($e);
		}

		try {
			self::$grader->Grade($r["run"]->getRunId(), $r['debug'] || false);
		} catch (Exception $e) {
			self::$log->error("Call to Grader::grade() failed:");
			self::$log->error($e);
		}

		$response = array();
		$response['status'] = 'ok';
				
		self::invalidateCacheOnRejudge($r["run"]);					
		
		// Expire ranks
		UserController::deleteProblemsSolvedRankCacheList();

		return $response;	
	}
	
	/**
	 * Invalidates relevant caches on run rejudge
	 * 
	 * @param RunsDAO $run
	 */
	public static function invalidateCacheOnRejudge(Runs $run) {
		try {
			// Expire details of the run
			Cache::deleteFromCache(Cache::RUN_ADMIN_DETAILS, $run->getRunId());		
			
			$contest = ContestsDAO::getByPK($run->getContestId());
			
			// Now we need to invalidate problem stats
			$problem = ProblemsDAO::getByPK($run->getProblemId());
			
			if (!is_null($problem)) {
				// Invalidar cache stats
				Cache::deleteFromCache(Cache::PROBLEM_STATS, $problem->getAlias());
			}
		} catch (Exception $e) {
			// We did our best effort to invalidate the cache...
			self::$log->warn("Failed to invalidate cache on Rejudge, skipping: ");
			self::$log->warn($e);			
		}
	}

	/**
	 * Gets the full details of a run. Includes diff of cases
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiAdminDetails(Request $r) {
		if (OMEGAUP_LOCKDOWN) {
			throw new ForbiddenAccessException("lockdown");
		}

		// Get the user who is calling this API
		self::authenticateRequest($r);
		self::validateDetailsRequest($r);

		if (!(Authorization::CanEditRun($r["current_user_id"], $r["run"]))) {
			throw new ForbiddenAccessException();
		}

		// Get the problem
		try {
			$r["problem"] = ProblemsDAO::getByPK($r["run"]->getProblemId());
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
		
		$response = array();

		$problem_dir = PROBLEMS_PATH . '/' . $r["problem"]->getAlias() . '/cases/';
		$grade_dir = RUNS_PATH . '/../grade/' . $r["run"]->getRunId();

		$groups = array();

		if (file_exists("$grade_dir.err")) {
			$response['compile_error'] = file_get_contents("$grade_dir.err");
		} else if (is_dir($grade_dir) && file_exists("$grade_dir/details.json")) {
			$groups = json_decode(file_get_contents("$grade_dir/details.json"), true);
			foreach ($groups as &$group) {
				foreach ($group['cases'] as &$case) {
					$case_name = $case['name'];
					$case['meta'] = RunController::ParseMeta(file_get_contents("$grade_dir/$case_name.meta"));
					unset($case['meta']['status']);
				}
			}
		}

		$response['groups'] = $groups;
		$response['source'] = file_get_contents(RUNS_PATH . '/' . $r["run"]->getGuid());
		if ($response['source'] == null) {
			$response['source'] = '';
		}
		$response['judged_by'] = $r["run"]->judged_by;
		$response["status"] = "ok";
		
		return $response;
	}

	/**
	 * Parses Run metadata
	 * 
	 * @param string $meta
	 * @return array
	 */
	public static function ParseMeta($meta) {
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
		if ($a['group'] == $b['group'])
			return 0;

		return ($a['group'] < $b['group']) ? -1 : 1;
	}

	public static function CaseCompare($a, $b) {
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
		
		if (OMEGAUP_LOCKDOWN) {
			// OMI hotfix
			// @TODO @joemmanuel, hay que localizar este msg :P
			$response['source'] = "Ver el código ha sido temporalmente desactivado.";
		} else {
			// Get the source
			$response['source'] = file_get_contents(RUNS_PATH . '/' . $r["run"]->getGuid());
		}

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
		if (OMEGAUP_LOCKDOWN) {
			throw new ForbiddenAccessException("lockdown");
		}
		// Get the user who is calling this API
		self::authenticateRequest($r);

		self::validateDetailsRequest($r);

		$r["contest"] = ContestsDAO::getByPK($r["run"]->getContestId());

		if (!Authorization::IsSystemAdmin($r['current_user_id']) && !Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
			throw new ForbiddenAccessException();
		}

		$problem = ProblemsDAO::getByPK($r["run"]->getProblemId());

		$problem_dir = PROBLEMS_PATH . '/' . $problem->getAlias() . '/cases/';
		$grade_dir = RUNS_PATH . '/../grade/' . $r["run"]->getRunId();

		$cases = array();

		$zip = new ZipStream($r["run"]->getGuid() . '.zip');

		if (file_exists("$grade_dir.err")) {
			$zip->add_file_from_path("compile.err", "$grade_dir.err");
		} else if (is_dir($grade_dir) && file_exists("$grade_dir/details.json")) {
			$validator = $problem->validator == 'custom';
			$groups = json_decode(file_get_contents("$grade_dir/details.json"), true);
			foreach ($groups as $group) {
				foreach ($group['cases'] as $case) {
					$case_name = $case['name'];
					$zip->add_file_from_path("$case_name.out", "$grade_dir/$case_name.out");

					if (!$validator && $case['veredict'] == 'OK' && ($case['score'] < 1)) {
						$out_diff = `diff -wauBbi $problem_dir/$case_name.out $grade_dir/$case_name.out | tail -n +3 | head -n50`;
						$zip->add_file("$case_name.out.diff", $out_diff);
					}

					if (!$r['complete']) continue;

					$zip->add_file_from_path("$case_name.err", "$grade_dir/$case_name.err");
					$zip->add_file_from_path("$case_name.meta", "$grade_dir/$case_name.meta");

					if ($validator && is_dir("$grade_dir/validator")) {
						$zip->add_file_from_path("validator/$case_name.meta", "$grade_dir/validator/$case_name.meta");
						$zip->add_file_from_path("validator/$case_name.out", "$grade_dir/validator/$case_name.out");
						$zip->add_file_from_path("validator/$case_name.err", "$grade_dir/validator/$case_name.err");
					}
				}
			}
		} else if (is_dir($grade_dir)) {
			// No nice details.json, probably a JE.
			if ($dir = opendir($grade_dir)) {
				while (($file = readdir($dir)) !== false) {
					$path = "$grade_dir/$file";
					if (is_dir($path) || (!$r['complete'] && !strstr($file, ".out")))
						continue;

					$zip->add_file_from_path("$file", "$grade_dir/$file");
				}
				closedir($dir);
			}
			if ($r['complete'] && is_dir("$grade_dir/validator")) {
				if ($dir = opendir("$grade_dir/validator")) {
					while (($file = readdir($dir)) !== false) {
						$path = "$grade_dir/validator/$file";
						if (is_dir($path))
							continue;

						$zip->add_file_from_path("validator/$file", "$grade_dir/validator/$file");
					}
					closedir($dir);
				}
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
		
		$totals = array();
		
		Cache::getFromCacheOrSet(Cache::RUN_COUNTS, "", $r, function(Request $r) {
			
			$totals = array();
			$totals["total"] = array();
			$totals["ac"] = array();
			try {

				$date = date('Y-m-d', strtotime('1 days'));
				
				for ($i = 0; $i < 30 * 3 /*about 3 months*/; $i++) {
					$totals["total"][$date] = RunsDAO::GetRunCountsToDate($date);
					$totals["ac"][$date] = RunsDAO::GetAcRunCountsToDate($date);
					$date = date('Y-m-d', strtotime('-'.$i.' days'));
				}
				
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}
			
			return $totals;
			
		}, $totals, 24*60*60 /*expire in 1 day*/);
										
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

		Validators::isInEnum($r["language"], "language", array('c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'p', 'kp', 'kj', 'cat', 'hs'), false);
		
		// Get user if we have something in username
		if (!is_null($r["username"])) {
			try {
				$r["user"] = UserController::resolveUser($r["username"]);
			} catch (NotFoundException $e) {
				// If not found, simply ignore it
				$r["username"] = null;
				$r["user"] = null;
			}
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

		try {
			$runs = RunsDAO::GetAllRuns(
				null,
				$r["status"],
				$r["veredict"],
				!is_null($r["problem"]) ? $r["problem"]->getProblemId() : null,
				$r["language"],
				!is_null($r["user"]) ? $r["user"]->getUserId() : null,
				$r["offset"],
				$r["rowcount"]
			);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		$result = array();

		foreach ($runs as $run) {
			$run['time'] = (int)$run['time'];
			$run['score'] = round((float)$run['score'], 4);
			$run['contest_score'] = round((float)$run['contest_score'], 2);
			array_push($result, $run);
		}

		$response = array();
		$response["runs"] = $result;
		$response["status"] = "ok";

		return $response;
	}
}
