<?php

require_once 'libs/FileHandler.php';
require_once 'libs/ZipHandler.php';
require_once 'libs/Markdown/markdown.php';

/**
 * ProblemsController
 */
class ProblemController extends Controller {

	public static $grader = null;

	/**
	 * Creates an instance of Grader if not already created
	 */
	private static function initializeGrader() {
		if (is_null(self::$grader)) {
			// Create new grader
			self::$grader = new Grader();
		}
	}

	/**
	 * 
	 * @param type $pageSize
	 * @param type $pageNumber
	 * @param type $servidor
	 * @param type $orderBy
	 * @return type
	 */
	public static function getProblemList($pageSize = 10, $pageNumber = 1, $servidor = null, $orderBy = null) {

		//$condition = "server = '$servidor' and public = 1";
		//$results = ProblemsDAO::byPage ( $sizePage , $noPage , $condition , $servidor, $orderBy );		

		return ProblemsDAO::getAll($pageNumber, $pageSize, $orderBy);
	}

	/**
	 * 
	 * @return type
	 */
	public static function getJudgesList() {
		return array('uva' => "Universidad de Valladolid |",
			'livearchive' => "ICPC Live Archive |",
			'pku' => "Pekin University |",
			'tju' => " Tianjing University |",
			'spoj' => "SPOJ");
	}

	/**
	 * Adds a problem from a remote server to the list of known
	 * problems.
	 *
	 * @return bool|string True if problem was added successfully.
	 *                     Error message, otherwise.
	 * @todo Add JSON responses
	 */
	public static function addRemoteProblem(
	$judge
	, $remote_id
	, $public = true
	) {

		try {
			$prob = new Problems();
			// Validating that $judge is in fact a valid judge happens
			// in setServidor
			$prob->setServidor($judge);
			$prob->setIdRemoto($remote_id);
			$prob->setPublico($public);

			ProblemsDAO::save($prob);
		} catch (Exception $e) {
			return $e->getMessage();
		}

		// If we make it this far, the problem was added successfully
		return true;
	}

	/**
	 * Adds one or more tags to a problem.
	 * This function should allow tagging multiple problems with multiple tags
	 * in a single function call. Arguments may be single values or arrays.
	 *
	 * @param mixed $problem_id The id may be a numeric problem_id or
	 *              a problem alias.
	 * @param int|array $tag_id Id of the tag (or tags) to be added.
	 *
	 * @return bool True if successful.
	 */
	public static function addTags(
	$problem_id
	, $tag_id
	) {
		
	}

	/**
	 * Validates a Create or Update Problem API request
	 * 
	 * @param Request $r
	 * @throws NotFoundException
	 */
	private static function validateCreateOrUpdate(Request $r, $is_update = false) {

		$is_required = true;

		// In case of update, params are optional
		if ($is_update) {
			$is_required = false;

			// We need to check problem_alias
			Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

			try {
				$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}

			if (is_null($r["problem"])) {
				throw new NotFoundException("Problem not found");
			}

			// We need to check that the user can actually edit the problem
			if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
				throw new ForbiddenAccessException();
			}
		}

		Validators::isStringNonEmpty($r["title"], "title", $is_required);
		Validators::isStringNonEmpty($r["source"], "source", $is_required);
		Validators::isInEnum($r["public"], "public", array("0", "1"), $is_required);
		Validators::isInEnum($r["validator"], "validator", array("remote", "literal", "token", "token-caseless", "token-numeric", "custom"), $is_required);
		Validators::isNumberInRange($r["time_limit"], "time_limit", 0, INF, $is_required);
		Validators::isNumberInRange($r["memory_limit"], "memory_limit", 0, INF, $is_required);
	}

	/**
	 * Builds a problem alias from a problem title
	 * 
	 * @param string $title
	 */
	private static function buildAlias($title) {

		$alias = "";

		// Remove accents				
		$alias = ApiUtils::RemoveAccents($title);

		// To lower-case
		$alias = strtolower($alias);

		// Replace spaces for -
		$alias = str_replace(' ', '-', $alias);

		// Sanity url encode
		$alias = urlencode($alias);

		// Limit result to 32 chars
		$alias = substr($alias, 0, 32);

		return $alias;
	}

	/**
	 * Create a new problem
	 * 
	 * @throws ApiException
	 * @throws DuplicatedEntryInDatabaseException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiCreate(Request $r) {

		self::authenticateRequest($r);

		// Validates request
		self::validateCreateOrUpdate($r);

		// Populate a new Problem object
		$problem = new Problems();
		$problem->setPublic($r["public"]); /* private by default */
		$problem->setTitle($r["title"]);
		$problem->setValidator($r["validator"]);
		$problem->setTimeLimit($r["time_limit"]);
		$problem->setMemoryLimit($r["memory_limit"]);
		$problem->setVisits(0);
		$problem->setSubmissions(0);
		$problem->setAccepted(0);
		$problem->setDifficulty(0);
		$problem->setSource($r["source"]);
		$problem->setOrder("normal"); /* defaulting to normal */
		$problem->setAuthorId($r["current_user_id"]);

		$r["alias"] = self::buildAlias($r["title"]);
		$problem->setAlias($r["alias"]);

		$problemDeployer = new ProblemDeployer();

		// Insert new problem
		try {

			ProblemsDAO::transBegin();

			// Save the contest object with data sent by user to the database
			ProblemsDAO::save($problem);

			// Create file after we know that alias is unique			
			$problemDeployer->deploy($r);

			ProblemsDAO::transEnd();
		} catch (ApiException $e) {
			// Operation failed in something we know it could fail, rollback transaction 
			ProblemsDAO::transRollback();

			// Rollback the problem if deployed partially
			$problemDeployer->deleteProblemFromFilesystem($r);

			throw $e;
		} catch (Exception $e) {

			// Operation failed unexpectedly, rollback transaction 
			ProblemsDAO::transRollback();

			// Alias may be duplicated, 1062 error indicates that
			if (strpos($e->getMessage(), "1062") !== FALSE) {
				throw new DuplicatedEntryInDatabaseException("Problem title already exists. Please try a different one.", $e);
			} else {

				// Rollback the problem if deployed partially
				$problemDeployer->deleteProblemFromFilesystem($r);

				throw new InvalidDatabaseOperationException($e);
			}
		}

		// Adding unzipped files to response
		$result["uploaded_files"] = $problemDeployer->filesToUnzip;
		$result["status"] = "ok";
		$result["alias"] = $r["alias"];

		return $result;
	}

	/**
	 * Validates a Rejudge Problem API request
	 * 
	 * @param Request $r
	 * @throws NotFoundException
	 */
	private static function validateRejudge(Request $r) {
		// We need to check problem_alias
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		try {
			$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($r["problem"])) {
			throw new NotFoundException("Problem not found");
		}

		// We need to check that the user can actually edit the problem
		if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
			throw new ForbiddenAccessException();
		}
	}

	/**
	 * Rejudge problem
	 * 
	 * @param Request $r
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiRejudge(Request $r) {
		self::authenticateRequest($r);

		self::validateRejudge($r);

		// We need to rejudge runs after an update, let's initialize the grader
		self::initializeGrader();

		// Call Grader
		$runs = array();
		try {
			$runs = RunsDAO::search(new Runs(array(
								"problem_id" => $r["problem"]->getProblemId()
							)));

			foreach ($runs as $run) {
				$run->setStatus('new');
				$run->setVeredict('JE');
				$run->setScore(0);
				$run->setContestScore(0);
				RunsDAO::save($run);
				self::$grader->Grade($run->getRunId());

				// Expire details of the run
				$runAdminDetailsCache = new Cache(Cache::RUN_ADMIN_DETAILS, $run->getRunId());
				$runAdminDetailsCache->delete();
			}
		} catch (Exception $e) {
			Logger::error("Failed to rejudge runs after problem update");
			Logger::error($e);
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();

		// All clear
		$response["status"] = "ok";

		// Invalidate contest & problem caches
		if (!is_null($runs) && count($runs) > 0) {
			RunController::invalidateCacheOnRejudge($runs[0]);
		}

		return $response;
	}

	/**
	 * Update problem contents
	 * 
	 * @param Request $r
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiUpdate(Request $r) {

		self::authenticateRequest($r);

		self::validateCreateOrUpdate($r, true /* is update */);

		$requiresRejudge = false;

		// Update the Problem object        
		if (!is_null($r["public"])) {
			$r["problem"]->setPublic($r["public"]);
		}

		if (!is_null($r["title"])) {
			$r["problem"]->setTitle($r["title"]);
		}

		if (!is_null($r["validator"])) {
			if ($r["validator"] != $r["problem"]->getValidator()) {
				$requiresRejudge = true;
			}

			$r["problem"]->setValidator($r["validator"]);
		}

		if (!is_null($r["time_limit"])) {
			if ($r["time_limit"] != $r["problem"]->getTimeLimit()) {
				$requiresRejudge = true;
			}

			$r["problem"]->setTimeLimit($r["time_limit"]);
		}

		if (!is_null($r["memory_limit"])) {
			if ($r["memory_limit"] != $r["problem"]->getMemoryLimit()) {
				$requiresRejudge = true;
			}

			$r["problem"]->setMemoryLimit($r["memory_limit"]);
		}

		if (!is_null($r["source"])) {
			$r["problem"]->setSource($r["source"]);
		}

		if (!is_null($r["order"])) {
			$r["problem"]->setOrder($r["order"]);
		}

		$response = array();
		$problemDeployer = new ProblemDeployer();

		// Insert new problem
		try {
			//Begin transaction
			ProblemsDAO::transBegin();

			// Save the contest object with data sent by user to the database
			ProblemsDAO::save($r["problem"]);

			if (isset($_FILES['problem_contents']) && FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
				$requiresRejudge = true;

				// DeployProblemZip requires alias => problem_alias
				$r["alias"] = $r["problem_alias"];

				$problemDeployer->deploy($r, true /* is update */);
				$response["uploaded_files"] = $problemDeployer->filesToUnzip;
			}

			//End transaction
			ProblemsDAO::transEnd();
		} catch (ApiException $e) {
			// Operation failed in the data layer, rollback transaction 
			ProblemsDAO::transRollback();

			throw $e;
		} catch (Exception $e) {
			// Operation failed in the data layer, rollback transaction 
			ProblemsDAO::transRollback();

			throw new InvalidDatabaseOperationException($e);
		}

		if (($requiresRejudge === true) && (OMEGAUP_FORCE_REJUDGE_ON_PROBLEM_UPDATE === true)) {

			// We need to rejudge runs after an update, let's initialize the grader
			self::initializeGrader();

			// Call Grader
			try {
				$runs = RunsDAO::search(new Runs(array(
									"problem_id" => $r["problem"]->getProblemId()
								)));

				foreach ($runs as $run) {
					$run->setStatus('new');
					$run->setVeredict('JE');
					RunsDAO::save($run);
					self::$grader->Grade($run->getRunId());
				}
			} catch (Exception $e) {
				Logger::error("Failed to rejudge runs after problem update");
				Logger::error($e);
				throw new InvalidDatabaseOperationException($e);
			}
		}

		if ($r["redirect"] === true) {
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		}

		// All clear
		$response["status"] = "ok";

		// Invalidar cache @todo invalidar todos los lenguajes
		$statementCache = new Cache(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-es");
		$statementCache->delete();

		return $response;
	}

	/**
	 * Validate problem Details API
	 * 
	 * @param Request $r
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 * @throws ForbiddenAccessException
	 */
	private static function validateDetails(Request $r) {

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias", false);
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		// Lang is optional. Default is ES
		if (!is_null($r["lang"])) {
			Validators::isStringOfMaxLength($r["lang"], "lang", 2);
		} else {
			$r["lang"] = "es";
		}

		try {
			$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($r["problem"])) {
			throw new NotFoundException("Problem not found");
		}

		// If we request a problem inside a contest
		if (!is_null($r["contest_alias"])) {
			// Is the combination contest_id and problem_id valid?        
			try {
				$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);

				if (is_null($r["contest"])) {
					throw new NotFoundException("Contest not found");
				}

				if (is_null(ContestProblemsDAO::getByPK($r["contest"]->getContestId(), $r["problem"]->getProblemId()))) {
					throw new NotFoundException("Problem not found in contest given");
				}
			} catch (ApiException $apiException) {
				throw $apiException;
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}


			// If the contest is private, verify that our user is invited                        
			if ($r["contest"]->getPublic() === 0) {
				if (is_null(ContestsUsersDAO::getByPK($r["current_user_id"], $r["contest"]->getContestId())) && !Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
					throw new ForbiddenAccessException();
				}
			}

			// If the contest has not started, user should not see it, unless it is admin
			if (!$r["contest"]->hasStarted($r["current_user_id"]) && !Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
				throw new ForbiddenAccessException("Contest has not started yet.");
			}
		} else {

			if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
				// If the problem is requested outside a contest, we need to check that it is not private
				if ($r["problem"]->getPublic() == "0") {
					throw new ForbiddenAccessException("Problem is marked as private.");
				}
			}
		}
	}

	/**
	 * Entry point for Problem Details API
	 * 
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiDetails(Request $r) {
		// Get user
		self::authenticateRequest($r);

		// Validate request
		self::validateDetails($r);

		$response = array();

		// Create array of relevant columns
		$relevant_columns = array("title", "author_id", "alias", "validator", "time_limit", "memory_limit", "visits", "submissions", "accepted", "difficulty", "creation_date", "source", "order", "points", "public");

		// Read the file that contains the source
		if ($r["problem"]->getValidator() != 'remote') {
			$statementCache = new Cache(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-" . $r["lang"]);
			$file_content = null;

			// check cache
			$file_content = $statementCache->get();

			if (is_null($file_content)) {

				$source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["problem"]->getAlias() . DIRECTORY_SEPARATOR . 'statements' . DIRECTORY_SEPARATOR . $r["lang"] . ".html";

				try {
					$file_content = FileHandler::ReadFile($source_path);
				} catch (Exception $e) {
					throw new InvalidFilesystemOperationException($e);
				}

				// Add to cache
				$statementCache->set($file_content, APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT);
			}

			// Add problem statement to source
			$response["problem_statement"] = $file_content;
		} else if ($r["problem"]->getServer() == 'uva') {
			$response["problem_statement"] = '<iframe src="http://acm.uva.es/p/v' . substr($r["problem"]->getRemoteId(), 0, strlen($r["problem"]->getRemoteId()) - 2) . '/' . $r["problem"]->getRemoteId() . '.html"></iframe>';
		}

		// Add the problem the response
		$response = array_merge($response, $r["problem"]->asFilteredArray($relevant_columns));

		// Create array of relevant columns for list of runs
		$relevant_columns = array("guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");

		// Search the relevant runs from the DB
		$contest = ContestsDAO::getByAlias($r["contest_alias"]);

		$keyrun = new Runs(array(
					"user_id" => $r["current_user_id"],
					"problem_id" => $r["problem"]->getProblemId(),
					"contest_id" => is_null($r["contest"]) ? null : $r["contest"]->getContestId()
				));

		// Get all the available runs
		try {
			$runs_array = RunsDAO::search($keyrun);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		// Add each filtered run to an array
		if (count($runs_array) >= 0) {
			$runs_filtered_array = array();
			foreach ($runs_array as $run) {
				$filtered = $run->asFilteredArray($relevant_columns);
				$filtered['time'] = strtotime($filtered['time']);
				array_push($runs_filtered_array, $filtered);
			}
		}

		if (!is_null($r["contest"])) {
			// At this point, contestant_user relationship should be established.        
			try {
				$contest_user = ContestsUsersDAO::CheckAndSaveFirstTimeAccess(
								$r["current_user_id"], $r["contest"]->getContestId());
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}

			// As last step, register the problem as opened                
			if (!ContestProblemOpenedDAO::getByPK(
							$r["contest"]->getContestId(), $r["problem"]->getProblemId(), $r["current_user_id"])) {
				//Create temp object
				$keyContestProblemOpened = new ContestProblemOpened(array(
							"contest_id" => $r["contest"]->getContestId(),
							"problem_id" => $r["problem"]->getProblemId(),
							"user_id" => $r["current_user_id"]
						));

				try {
					// Save object in the DB
					ContestProblemOpenedDAO::save($keyContestProblemOpened);
				} catch (Exception $e) {
					// Operation failed in the data layer
					throw new InvalidDatabaseOperationException($e);
				}
			}
		}

		// Add the procesed runs to the request
		$response["runs"] = $runs_filtered_array;
		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Validate problem Details API
	 * 
	 * @param Request $r
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 * @throws ForbiddenAccessException
	 */
	private static function validateRuns(Request $r) {
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		// Is the problem valid?
		try {
			$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (ApiException $apiException) {
			throw $apiException;
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
	}

	/**
	 * Entry point for Problem runs API
	 * 
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiRuns(Request $r) {
		// Get user
		self::authenticateRequest($r);

		// Validate request
		self::validateRuns($r);

		$response = array();

		$keyrun = new Runs(array(
					"user_id" => $r["current_user_id"],
					"problem_id" => $r["problem"]->getProblemId()
				));

		// Get all the available runs
		try {
			$runs_array = RunsDAO::search($keyrun);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		// Create array of relevant columns for list of runs
		$relevant_columns = array("guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");

		// Add each filtered run to an array
		$response["runs"] = array();
		if (count($runs_array) >= 0) {
			$runs_filtered_array = array();
			foreach ($runs_array as $run) {
				$filtered = $run->asFilteredArray($relevant_columns);
				$filtered['time'] = strtotime($filtered['time']);
				array_push($response['runs'], $filtered);
			}
		}

		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Stats of a problem
	 * 
	 * @param Request $r
	 * @return array
	 * @throws ForbiddenAccessException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiStats(Request $r) {

		// Get user
		self::authenticateRequest($r);

		// Validate request
		self::validateRuns($r);

		// We need to check that the user has priviledges on the problem
		if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
			throw new ForbiddenAccessException();
		}

		try {
			// Array of GUIDs of pending runs
			$pendingRunsGuids = RunsDAO::GetPendingRunsOfProblem($r["problem"]->getProblemId());

			// Count of pending runs (int)
			$totalRunsCount = RunsDAO::CountTotalRunsOfProblem($r["problem"]->getProblemId());

			// List of veredicts			
			$veredict_counts = array();

			foreach (self::$veredicts as $veredict) {
				$veredict_counts[$veredict] = RunsDAO::CountTotalRunsOfProblemByVeredict($r["problem"]->getProblemId(), $veredict);
			}

			// Array to count AC stats per case.
			// Let's try to get the last snapshot from cache.
			$problemStatsCache = new Cache(Cache::PROBLEM_STATS, $r["problem"]->getAlias());
			$cases_stats = $problemStatsCache->get();
			if (is_null($cases_stats)) {
				// Initialize the array at counts = 0
				$cases_stats = array();
				$cases_stats["counts"] = array();

				// We need to save the last_id that we processed, so next time we do not repeat this
				$cases_stats["last_id"] = 0;

				// Build problem dir
				$problem_dir = PROBLEMS_PATH . '/' . $r["problem"]->getAlias() . '/cases/';

				// Get list of cases
				$dir = opendir($problem_dir);
				if (is_dir($problem_dir)) {
					while (($file = readdir($dir)) !== false) {
						// If we have an input
						if (strstr($file, ".in")) {
							// Initialize it to 0
							$cases_stats["counts"][str_replace(".in", "", $file)] = 0;
						}
					}
					closedir($dir);
				}
			}

			// Get all runs of this problem after the last id we had
			$runs = RunsDAO::searchRunIdGreaterThan(new Runs(array("problem_id" => $r["problem"]->getProblemId())), $cases_stats["last_id"], "run_id");

			// For each run we got
			foreach ($runs as $run) {
				// Build grade dir
				$grade_dir = RUNS_PATH . '/../grade/' . $run->getRunId();

				// Skip it if it didn't produce outputs 
				if (file_exists("$grade_dir.err")) {
					continue;
				} else if (is_dir($grade_dir)) {
					// Try to open the details file.
					if (file_exists("$grade_dir/details.json")) {
						$details = json_decode(file_get_contents("$grade_dir/details.json"));
						foreach ($details as $group) {
							foreach ($group->cases as $case) {
								if ($case->score > 0) {
									$cases_stats["counts"][$case->name]++;
								}
							}
						}
					} else if ($dir = opendir($grade_dir)) {
						// Read all files in this run directory
						while (($file = readdir($dir)) !== false) {

							// Skip non output cases
							if ($file == '.' || $file == '..' || !strstr($file, ".meta")) {
								continue;
							}

							// Get the case name
							$case_name = str_replace(".meta", "", $file);

							// If we have an output
							if (file_exists("$grade_dir/" . str_replace(".meta", ".out", $file))) {

								// Get the output of this case
								$out = str_replace(".meta", ".out", $file);
								$case_out = `diff -wuBbi $problem_dir/$out $grade_dir/$out | tail -n +3 | head -n50`;

								// If the output was empty
								if (strcmp($case_out, "") === 0) {
									$cases_stats["counts"][$case_name]++;
								}
							}
						}

						// Close this run dir
						closedir($dir);
					}
				}
			}
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		// Save the last id we saw in case we saw something
		if (!is_null($runs) && count($runs) > 0) {
			$cases_stats["last_id"] = $runs[count($runs) - 1]->getRunId();
		}

		// Save in cache what we got
		$problemStatsCache->set($cases_stats, APC_USER_CACHE_PROBLEM_STATS_TIMEOUT);

		return array(
			"total_runs" => $totalRunsCount,
			"pending_runs" => $pendingRunsGuids,
			"veredict_counts" => $veredict_counts,
			"cases_stats" => $cases_stats["counts"],
			"status" => "ok"
		);
	}

	/**
	 * Validate list request
	 * 
	 * @param Request $r
	 */
	private static function validateList(Request $r) {

		Validators::isNumber($r["offset"], "offset", false);
		Validators::isNumber($r["rowcount"], "rowcount", false);

		// Defaults for offset and rowcount
		if (!isset($r["offset"])) {
			$r["offset"] = 0;
		}
		if (!isset($r["rowcount"])) {
			$r["rowcount"] = 200;
		}
	}

	/**
	 * List of public and user's private problems
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiList(Request $r) {

		// Authenticate request
		try {
			self::authenticateRequest($r);
		} catch (ForbiddenAccessException $e) {
			// Do nothing, we allow unauthenticated users to use this API
		}

		self::validateList($r);

		$response = array();
		$response["results"] = array();
		for ($i = 0; $i < 2; $i++) {

			// Add private in the first pass, public in the second
			try {
				$problem_mask = NULL;
				if ($i == 0 && !is_null($r["current_user_id"])) {
					$problem_mask = new Problems(array(
								"private" => 0,
								"author_id" => $r["current_user_id"]
							));
				} else if ($i == 1) {
					$problem_mask = new Problems(array(
								"public" => 1
							));
				}

				if (!is_null($problem_mask)) {
					$problems = ProblemsDAO::search($problem_mask, "problem_id", 'DESC', $r["offset"], $r["rowcount"]);

					foreach ($problems as $problem) {
						array_push($response["results"], $problem->asArray());
					}
				}
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}
		}

		$response["status"] = "ok";
		return $response;
	}

	/**
	 * 
	 * Gets a list of problems where current user is the owner
	 * 
	 * @param Request $r
	 */
	public static function apiMyList(Request $r) {

		self::authenticateRequest($r);
		self::validateList($r);

		$response = array();
		$response["results"] = array();

		try {
			$problems = NULL;
			if (Authorization::IsSystemAdmin($r["current_user_id"])) {
				$problems = ProblemsDAO::getAll(NULL, NULL, "problem_id", 'DESC');
			} else {
				$problem_mask = new Problems(array(
							"author_id" => $r["current_user_id"]
						));
				$problems = ProblemsDAO::search($problem_mask, "problem_id", 'DESC', $r["offset"], $r["rowcount"]);
			}

			foreach ($problems as $problem) {
				array_push($response["results"], $problem->asArray());
			}
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response["status"] = "ok";
		return $response;
	}

}
