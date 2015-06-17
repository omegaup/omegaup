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

	public static function getGradePath($run) {
		return GRADE_PATH . '/' .
			substr($run->guid, 0, 2) . '/' .
			substr($run->guid, 2);
	}

	/**
	 * Gets the path of the file that contains the submission.
	 */
	public static function getSubmissionPath($run) {
		return RUNS_PATH .
			DIRECTORY_SEPARATOR . substr($run->guid, 0, 2) .
			DIRECTORY_SEPARATOR . substr($run->guid, 2);
	}

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

			if ($r['problem']->deprecated) {
				throw new PreconditionFailedException('problemDeprecated');
			}

			Validators::isInEnum($r["language"], "language", array('kp', 'kj', 'c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'pas', 'cat', 'hs'));
			Validators::isStringNonEmpty($r["source"], "source");

			// Check for practice or public problem, there is no contest info in this scenario
			if ($r["contest_alias"] == "") {
				if (Authorization::IsProblemAdmin($r['current_user_id'], $r['problem']) ||
					  time() > ProblemsDAO::getPracticeDeadline($r["problem"]->getProblemId()) ||
					  $r["problem"]->getPublic() == true) {
					if (!RunsDAO::IsRunInsideSubmissionGap(
									null, 
									$r["problem"]->getProblemId(),
									$r["current_user_id"])
							&& !Authorization::IsSystemAdmin($r["current_user_id"])) {
							throw new NotAllowedToSubmitException("runWaitGap");
					}

					self::$practice = true;
					return;
				} else {
					throw new NotAllowedToSubmitException("problemIsNotPublic");
				}
			}

			// Validate contest
			Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");
			$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);

			if ($r["contest"] == NULL) {
				throw new InvalidParameterException("parameterNotFound", "contest_alias");
			}

			// Validate that the combination contest_id problem_id is valid
			if (!ContestProblemsDAO::getByPK(
							$r["contest"]->getContestId(), $r["problem"]->getProblemId()
			)) {
				throw new InvalidParameterException("parameterNotFound", "problem_alias");
			}

			// Contest admins can skip following checks
			if (!Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
				// Before submit something, contestant had to open the problem/contest
				if (!ContestsUsersDAO::getByPK($r["current_user_id"], $r["contest"]->getContestId())) {
					throw new NotAllowedToSubmitException("runNotEvenOpened");
				}

				// Validate that the run is timely inside contest
				if (!ContestsDAO::isInsideContest($r["contest"], $r['current_user_id'])) {
					throw new NotAllowedToSubmitException("runNotInsideContest");
				}

				// Validate if contest is private then the user should be registered
				if ($r["contest"]->getPublic() == 0
						&& is_null(ContestsUsersDAO::getByPK(
										$r["current_user_id"], $r["contest"]->getContestId()))) {
					throw new NotAllowedToSubmitException("runNotRegistered");
				}

				// Validate if the user is allowed to submit given the submissions_gap 			
				if (!RunsDAO::IsRunInsideSubmissionGap(
								$r["contest"]->getContestId(), $r["problem"]->getProblemId(), $r["current_user_id"])) {
					throw new NotAllowedToSubmitException("runWaitGap");
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

		self::$log->info("New run being submitted!!");
		$response = array();

		if (self::$practice) {
			if (OMEGAUP_LOCKDOWN) {
				throw new ForbiddenAccessException("lockdown");
			}
			$submit_delay = 0;
			$contest_id = null;
			$test = 0;
		} else {
			//check the kind of penalty_type for this contest
			$penalty_type = $r["contest"]->penalty_type;

			switch ($penalty_type) {
				case "contest_start":
					// submit_delay is calculated from the start
					// of the contest
					$start = $r["contest"]->getStartTime();
					break;

				case "problem_open":
					// submit delay is calculated from the 
					// time the user opened the problem
					$opened = ContestProblemOpenedDAO::getByPK(
									$r["contest"]->getContestId(), $r["problem"]->getProblemId(), $r["current_user_id"]
					);

					if (is_null($opened)) {
						//holy moly, he is submitting a run 
						//and he hasnt even opened the problem
						//what should be done here?
						throw new NotAllowedToSubmitException("runEvenOpened");
					}

					$start = $opened->getOpenTime();
					break;

				case "none":
				case "runtime":
					//we dont care
					$start = null;
					break;

				default:
					self::$log->error("penalty_type for this contests is not a valid option, asuming `none`.");
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
					"penalty" => $submit_delay,
					"memory" => 0,
					"score" => 0,
					"contest_score" => $contest_id != null ? 0 : null,
					"ip" => $_SERVER['REMOTE_ADDR'],
					"submit_delay" => $submit_delay, /* based on penalty_type */
					"guid" => md5(uniqid(rand(), true)),
					"verdict" => "JE",
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
			$filepath = RunController::getSubmissionPath($run);
			FileHandler::CreateFile($filepath, $r["source"]);
		} catch (Exception $e) {
			throw new InvalidFilesystemOperationException($e);
		}

		// Call Grader
		try {
			self::$grader->Grade([$run->guid], false, false);
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
			throw new NotFoundException("runNotFound");
		}
	}

	/**
	 * Validate request of admin details
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 * @throws ForbiddenAccessException
	 */
	private static function validateAdminDetailsRequest(Request $r) {
		Validators::isStringNonEmpty($r["run_alias"], "run_alias");

		try {
			$r["run"] = RunsDAO::getByAlias($r["run_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($r["run"])) {
			throw new NotFoundException("runNotFound");
		}

		try {
			$r["problem"] = ProblemsDAO::getByPK($r['run']->problem_id);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($r["problem"])) {
			throw new NotFoundException("problemNotFound");
		}

		if (!(Authorization::IsProblemAdmin($r["current_user_id"], $r["problem"]))) {
			throw new ForbiddenAccessException("userNotAllowed");
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
			throw new ForbiddenAccessException("userNotAllowed");
		}

		// Fill response
		$relevant_columns = array("guid", "language", "status", "verdict", 
			"runtime", "penalty", "memory", "score", "contest_score", "time", 
			"submit_delay");
		$filtered = $r["run"]->asFilteredArray($relevant_columns);
		$filtered['time'] = strtotime($filtered['time']);
		$filtered['score'] = round((float) $filtered['score'], 4);
		if ($filtered['contest_score'] != null) {
			$filtered['contest_score'] = round((float) $filtered['contest_score'], 2);
		}

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
			throw new ForbiddenAccessException("userNotAllowed");
		}

		self::$log->info("Run being rejudged!!");

		// Try to delete existing directory, if exists.
		try {
			$grade_dir = RunController::getGradePath($r['run']);
			FileHandler::DeleteDirRecursive($grade_dir);
		} catch (Exception $e) {
			// Soft error :P
			self::$log->warn($e);
		}

		try {
			self::$grader->Grade([$r["run"]->guid], true, $r['debug'] || false);
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
		self::validateAdminDetailsRequest($r);
		
		$response = array();

		$problem_dir = PROBLEMS_PATH . '/' . $r["problem"]->getAlias() . '/cases/';
		$grade_dir = RunController::getGradePath($r['run']);

		$groups = array();

		if (file_exists("$grade_dir/compile_error.log")) {
			$response['compile_error'] = file_get_contents("$grade_dir/compile_error.log");
		}
		if (file_exists("$grade_dir/details.json")) {
			$groups = json_decode(file_get_contents("$grade_dir/details.json"), true);
		}
		if (file_exists("$grade_dir/run.log")) {
			$response['logs'] = file_get_contents("$grade_dir/run.log");
		}

		$response['groups'] = $groups;
		$response['source'] = file_get_contents(RunController::getSubmissionPath($r['run']));
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
			throw new ForbiddenAccessException("userNotAllowed");
		}

		$response = array();
		
		if (OMEGAUP_LOCKDOWN) {
			// OMI hotfix
			// @TODO @joemmanuel, hay que localizar este msg :P
			$response['source'] = "Ver el código ha sido temporalmente desactivado.";
		} else {
			// Get the source
			$response['source'] = file_get_contents(RunController::getSubmissionPath($r['run']));
		}

		// Get the error
		$grade_dir = RunController::getGradePath($r['run']);
		if (file_exists("$grade_dir/compile_error.log")) {
			$response['compile_error'] = file_get_contents("$grade_dir/compile_error.log");
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

		self::validateAdminDetailsRequest($r);

		$grade_dir = RunController::getGradePath($r['run']);
		$results_zip = "$grade_dir/results.zip";

		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $r['run']->guid . '.zip');
		header('Content-Length: ' . filesize($results_zip));
		readfile($results_zip);
		exit;
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
			throw new ForbiddenAccessException("userNotAllowed");
		}

		Validators::isNumber($r["offset"], "offset", false);
		Validators::isNumber($r["rowcount"], "rowcount", false);
		Validators::isInEnum($r["status"], "status", array('new', 'waiting', 'compiling', 'running', 'ready'), false);
		Validators::isInEnum($r["verdict"], "verdict", array("AC", "PA", "WA", "TLE", "MLE", "OLE", "RTE", "RFE", "CE", "JE", "NO-AC"), false);


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
				throw new NotFoundException("problemNotFound");
			}
		}

		Validators::isInEnum($r["language"], "language", array('c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'pas', 'kp', 'kj', 'cat', 'hs'), false);
		
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
				$r["verdict"],
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
			if ($run['contest_score'] != null) {
				$run['contest_score'] = round((float)$run['contest_score'], 2);
			}
			array_push($result, $run);
		}

		$response = array();
		$response["runs"] = $result;
		$response["status"] = "ok";

		return $response;
	}
}
