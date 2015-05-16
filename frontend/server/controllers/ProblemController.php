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

			if ($r['problem']->deprecated) {
				throw new PreconditionFailedException('problemDeprecated');
			}
		} else {
			Validators::isValidAlias($r['alias'], 'alias');
		}

		Validators::isStringNonEmpty($r["title"], "title", $is_required);
		Validators::isStringNonEmpty($r["source"], "source", $is_required);
		Validators::isInEnum($r["public"], "public", array("0", "1"), $is_required);
		Validators::isInEnum($r["validator"], "validator",
			array("token", "token-caseless", "token-numeric", "custom", "literal"), $is_required);
		Validators::isNumberInRange($r["time_limit"], "time_limit", 0, INF, $is_required);
		Validators::isNumberInRange($r["validator_time_limit"], "validator_time_limit", 0, INF, $is_required);
		Validators::isNumberInRange($r["overall_wall_time_limit"], "overall_wall_time_limit", 0, 60000, $is_required);
		Validators::isNumberInRange($r["extra_wall_time"], "extra_wall_time", 0, 5000, $is_required);
		Validators::isNumberInRange($r["memory_limit"], "memory_limit", 0, INF, $is_required);
		Validators::isNumberInRange($r["output_limit"], "output_limit", 0, INF, $is_required);

		// HACK! I don't know why "languages" doesn't make it into $r, and I've spent far too much time
		// on it already, so I'll just leave this here for now...
		if (!isset($r["languages"]) && isset($_REQUEST["languages"])) {
			$r["languages"] = implode(",", $_REQUEST["languages"]);
		} else if (isset($r["languages"]) && is_array($r["languages"])) {
			$r["languages"] = implode(",", $r["languages"]);
		}
		Validators::isValidSubset($r["languages"], "languages", array('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11'), $is_required);
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
		$problem->setValidatorTimeLimit($r["validator_time_limit"]);
		$problem->setOverallWallTimeLimit($r["overall_wall_time_limit"]);
		$problem->setExtraWallTime($r["extra_wall_time"]);
		$problem->setMemoryLimit($r["memory_limit"]);
		$problem->setOutputLimit($r["output_limit"]);
		$problem->setVisits(0);
		$problem->setSubmissions(0);
		$problem->setAccepted(0);
		$problem->setDifficulty(0);
		$problem->setSource($r["source"]);
		$problem->setOrder("normal"); /* defaulting to normal */
		$problem->setAuthorId($r["current_user_id"]);
		$problem->setAlias($r["alias"]);
		$problem->setLanguages($r["languages"]);
		$problem->setStackLimit($r["stack_limit"]);

		if (file_exists(PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['alias'])) {
			throw new DuplicatedEntryInDatabaseException('problemExists');
		}

		$problemDeployer = new ProblemDeployer($r['alias'], ProblemDeployer::CREATE);

		// Insert new problem
		try {

			ProblemsDAO::transBegin();

			// Create file after we know that alias is unique			
			$problemDeployer->deploy();
			if ($problemDeployer->hasValidator) {
				$problem->validator = 'custom';
			} else if ($problem->validator == 'custom') {
				throw new ProblemDeploymentFailedException('problemDeployerValidatorRequired');
			}
			$problem->slow = $problemDeployer->isSlow($problem);

			// Calculate output limit.
			$output_limit = $problemDeployer->getOutputLimit();

			if ($output_limit != -1) {
				$problem->setOutputLimit($output_limit);
			}

			// Save the contest object with data sent by user to the database
			ProblemsDAO::save($problem);

			ProblemsDAO::transEnd();

			// Commit at the very end
			$problemDeployer->commit("Initial commit", $r['current_user']);
		} catch (ApiException $e) {
			// Operation failed in something we know it could fail, rollback transaction 
			ProblemsDAO::transRollback();

			throw $e;
		} catch (Exception $e) {
			self::$log->error("Failed to upload problem");
			self::$log->error($e);

			// Operation failed unexpectedly, rollback transaction 
			ProblemsDAO::transRollback();

			// Alias may be duplicated, 1062 error indicates that
			if (strpos($e->getMessage(), "1062") !== FALSE) {
				throw new DuplicatedEntryInDatabaseException("problemTitleExists");
			} else {

				throw new InvalidDatabaseOperationException($e);
			}
		} finally {
			$problemDeployer->cleanup();
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
			throw new NotFoundException("problemNotFound");
		}

		if ($r['problem']->deprecated) {
			throw new PreconditionFailedException('problemDeprecated');
		}

		// We need to check that the user can actually edit the problem
		if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
			throw new ForbiddenAccessException();
		}
	}
	
	/**
	 * Adds an admin to a problem
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	public static function apiAddAdmin(Request $r) {
		// Authenticate logged user
		self::authenticateRequest($r);

		// Check problem_alias
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		$user = UserController::resolveUser($r["usernameOrEmail"]);

		try {
			$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}
		
		if (!Authorization::IsProblemAdmin($r["current_user_id"], $r["problem"])) {
			throw new ForbiddenAccessException();
		}				

		$problem_user = new UserRoles();
		$problem_user->setContestId($r["problem"]->problem_id);
		$problem_user->setUserId($user->user_id);
		$problem_user->setRoleId(PROBLEM_ADMIN_ROLE);

		// Save the contest to the DB
		try {
			UserRolesDAO::save($problem_user);
		} catch (Exception $e) {
			// Operation failed in the data layer
			self::$log->error("Failed to save user roles");
			self::$log->error($e);
			throw new InvalidDatabaseOperationException($e);
		}

		return array("status" => "ok");
	}
	
	/**
	 * Adds a tag to a problem
	 *
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	public static function apiAddTag(Request $r) {
		// Check problem_alias
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		// Authenticate logged user
		self::authenticateRequest($r);

		$problem = ProblemsDAO::getByAlias($r['problem_alias']);

		if (!Authorization::IsProblemAdmin($r["current_user_id"], $problem)) {
			throw new ForbiddenAccessException();
		}

		// Normalize name.
		$tag_name = $r['name'];
		Validators::isStringNonEmpty($tag_name, 'name');
		$tag_name = TagController::normalize($tag_name);

		try {
			$tag = TagsDAO::getByName($tag_name);
		} catch (Exception $e) {
			$this->log->info($e);
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if ($tag == null) {
			try {
				$tag = new Tags();
				$tag->name = $tag_name;
				TagsDAO::save($tag);
			} catch (Exception $inner) {
				$this->log->info($e);
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($inner);
			}
		}

		if (is_null($tag->tag_id)) {
			throw new InvalidDatabaseOperationException(new Exception("tag"));
		}

		$problem_tag = new ProblemsTags();
		$problem_tag->problem_id = $problem->problem_id;
		$problem_tag->tag_id = $tag->tag_id;
		$problem_tag->public = $r['public'] ? 1 : 0;

		// Save the tag to the DB
		try {
			ProblemsTagsDAO::save($problem_tag);
		} catch (Exception $e) {
			// Operation failed in the data layer
			self::$log->error("Failed to save tag", $e);
			throw new InvalidDatabaseOperationException($e);
		}

		return array('status' => 'ok', 'name' => $tag_name);
	}

	/**
	 * Removes an admin from a contest
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	public static function apiRemoveAdmin(Request $r) {
		// Authenticate logged user
		self::authenticateRequest($r);

		// Check whether problem exists
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		$r["user"] = UserController::resolveUser($r["usernameOrEmail"]);

		try {
			$r["problem"] = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}
		
		if (!Authorization::IsProblemAdmin($r["current_user_id"], $r["problem"])) {
			throw new ForbiddenAccessException();
		}	
		
		// Check if admin to delete is actually an admin
		if (!Authorization::IsProblemAdmin($r["user"]->user_id, $r["problem"])){
			throw new NotFoundException();
		}

		$problem_user = new UserRoles();
		$problem_user->setContestId($r["problem"]->problem_id);
		$problem_user->setUserId($r["user"]->user_id);
		$problem_user->setRoleId(PROBLEM_ADMIN_ROLE);

		// Delete the role
		try {
			UserRolesDAO::delete($problem_user);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		return array("status" => "ok");
	}

	/**
	 * Removes a tag from a contest
	 *
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	public static function apiRemoveTag(Request $r) {
		// Authenticate logged user
		self::authenticateRequest($r);

		// Check whether problem exists
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		try {
			$problem = ProblemsDAO::getByAlias($r["problem_alias"]);
			$tag = TagsDAO::getByName($r['name']);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($problem)) {
			throw new NotFoundException('problem');
		} else if (is_null($tag)) {
			throw new NotFoundException('tag');
		}
	
		if (!Authorization::IsProblemAdmin($r["current_user_id"], $problem)) {
			throw new ForbiddenAccessException();
		}

		$problem_tag = new ProblemsTags();
		$problem_tag->problem_id = $problem->problem_id;
		$problem_tag->tag_id = $tag->tag_id;

		// Delete the role
		try {
			ProblemsTagsDAO::delete($problem_tag);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		return array("status" => "ok");
	}

	/**
	 * Returns all problem administrators
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiAdmins(Request $r) {
		// Authenticate request
		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		try {
			$problem = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (!Authorization::IsProblemAdmin($r["current_user_id"], $problem)) {
			throw new ForbiddenAccessException();
		}

		$response = array();
		$response["admins"] = UserRolesDAO::getProblemAdmins($problem);
		$response["status"] = "ok";

		return $response;
	}
	
	/**
	 * Returns all problem tags
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiTags(Request $r) {
		// Authenticate request
		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		try {
			$problem = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();
		$response["tags"] = ProblemsTagsDAO::getProblemTags(
			$problem,
			!Authorization::IsProblemAdmin($r["current_user_id"], $problem)
		);

		$response["status"] = "ok";

		return $response;
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

			$guids = array();
			foreach ($runs as $run) {
				$guids[] = $run->guid;
				$run->setStatus('new');
				$run->setVerdict('JE');
				$run->setScore(0);
				$run->setContestScore(0);
				RunsDAO::save($run);

				// Expire details of the run				
				RunController::invalidateCacheOnRejudge($run);				
			}
			self::$grader->Grade($guids, true, false);
		} catch (Exception $e) {
			self::$log->error("Failed to rejudge runs after problem update");
			self::$log->error($e);
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();

		// All clear
		$response["status"] = "ok";				

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

		// Update the Problem object
		$valueProperties = array(
			"public",
			"title",
			"validator"     => array("important" => true), // requires rejudge
			"time_limit"    => array("important" => true), // requires rejudge
			"validator_time_limit"    => array("important" => true), // requires rejudge
			"overall_wall_time_limit" => array("important" => true), // requires rejudge
			"extra_wall_time" => array("important" => true), // requires rejudge
			"memory_limit"  => array("important" => true), // requires rejudge
			"output_limit"  => array("important" => true), // requires rejudge
			"stack_limit"   => array("important" => true), // requires rejudge
			"source",
			"order",
			"languages",
		);
		$problem = $r['problem'];
		$requiresRejudge = self::updateValueProperties($r, $problem, $valueProperties);
		$r['problem'] = $problem;

		$response = array();
		$problemDeployer = new ProblemDeployer($problem->alias, ProblemDeployer::UPDATE_CASES);

		// Insert new problem
		try {
			//Begin transaction
			ProblemsDAO::transBegin();

			if (isset($_FILES['problem_contents']) && FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
				$requiresRejudge = true;

				// DeployProblemZip requires alias => problem_alias
				$r["alias"] = $r["problem_alias"];

				$problemDeployer->deploy();
				if ($problemDeployer->hasValidator) {
					$problem->validator = 'custom';
				} else if ($problem->validator == 'custom') {
					throw new ProblemDeploymentFailedException('problemDeployerValidatorRequired');
				}
				// This must come before the commit in case isSlow throws an exception.
				$problem->slow = $problemDeployer->isSlow($problem);

				// Calculate output limit.
				$output_limit = $problemDeployer->getOutputLimit();

				if ($output_limit != -1) {
					$r['problem']->setOutputLimit($output_limit);
				}

				$response["uploaded_files"] = $problemDeployer->filesToUnzip;
				$problemDeployer->commit("Updated problem contents", $r['current_user']);
			} else {
				$problem->slow = $problemDeployer->isSlow($problem);
			}

			// Save the contest object with data sent by user to the database
			ProblemsDAO::save($problem);

			//End transaction
			ProblemsDAO::transEnd();
		} catch (ApiException $e) {
			// Operation failed in the data layer, rollback transaction 
			ProblemsDAO::transRollback();

			throw $e;
		} catch (Exception $e) {
			// Operation failed in the data layer, rollback transaction 
			ProblemsDAO::transRollback();
			self::$log->error("Failed to update problem");
			self::$log->error($e);

			throw new InvalidDatabaseOperationException($e);
		} finally {
			$problemDeployer->cleanup();
		}

		if (($requiresRejudge === true) && (OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE === true)) {
			self::$log->info("Calling ProblemController::apiRejudge");
			try {
				self::apiRejudge($r);
			} catch (Exception $e) {
				self::$log->error("Best efort ProblemController::apiRejudge failed", $e);
			}
		}

		if ($r["redirect"] === true) {
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		}

		// All clear
		$response["status"] = "ok";

		// Invalidar problem statement cache @todo invalidar todos los lenguajes
		foreach ($problemDeployer->getUpdatedLanguages() as $lang) {
			Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-" . $lang . "html");
			Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-" . $lang . "markdown");
		}
		Cache::deleteFromCache(Cache::PROBLEM_SAMPLE, $r["problem"]->getAlias() . "-sample.in");

		return $response;
	}
		
	/**
	 * Updates problem statement only	 
	 * 
	 * @param Request $r
	 * @return array
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiUpdateStatement(Request $r) {
		self::authenticateRequest($r);
		
		self::validateCreateOrUpdate($r, true);
		
		// Validate statement
		Validators::isStringNonEmpty($r["statement"], "statement");
		
		// Check that lang is in the ISO 639-1 code list, default is "es".
		$iso639_1 = array("ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy",
			"as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi",
			"bs", "br", "bg", "my", "ca", "ch", "ce", "ny", "zh", "cv", "kw", "co",
			"cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee", "fo",
			"fj", "fi", "fr", "ff", "gl", "ka", "de", "el", "gn", "gu", "ht", "ha",
			"he", "hz", "hi", "ho", "hu", "ia", "id", "ie", "ga", "ig", "ik", "io",
			"is", "it", "iu", "ja", "jv", "kl", "kn", "kr", "ks", "kk", "km", "ki",
			"rw", "ky", "kv", "kg", "ko", "ku", "kj", "la", "lb", "lg", "li", "ln",
			"lo", "lt", "lu", "lv", "gv", "mk", "mg", "ms", "ml", "mt", "mi", "mr",
			"mh", "mn", "na", "nv", "nd", "ne", "ng", "nb", "nn", "no", "ii", "nr",
			"oc", "oj", "cu", "om", "or", "os", "pa", "pi", "fa", "pl", "ps", "pt",
			"qu", "rm", "rn", "ro", "ru", "sa", "sc", "sd", "se", "sm", "sg", "sr",
			"gd", "sn", "si", "sk", "sl", "so", "st", "es", "su", "sw", "ss", "sv",
			"ta", "te", "tg", "th", "ti", "bo", "tk", "tl", "tn", "to", "tr", "ts",
			"tt", "tw", "ty", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy",
			"wo", "fy", "xh", "yi", "yo", "za", "zu");
		Validators::isInEnum($r["lang"], "lang", $iso639_1, false /* is_required */);
		if (is_null($r["lang"])) {
			$r["lang"] = "es";
		}

		$problemDeployer = new ProblemDeployer($r['problem_alias'], ProblemDeployer::UPDATE_STATEMENTS);
		try {					
			$problemDeployer->updateStatement($r['lang'], $r['statement']);
			$problemDeployer->commit("Updated statement for {$r['lang']}", $r['current_user']);
			
			// Invalidar problem statement cache
			Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-" . $r["lang"] . "-" . "html");
			Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-" . $r["lang"] . "-" . "markdown");			
			Cache::deleteFromCache(Cache::PROBLEM_SAMPLE, $r["problem"]->getAlias() . "-sample.in");
		} catch (ApiException $e) {
			throw $e;
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		} finally {
			$problemDeployer->cleanup();
		}
		
		// All clear
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
			throw new NotFoundException("problemNotFound");
		}

		if (isset($r["statement_type"]) && !in_array($r["statement_type"], array("html", "markdown"))) {
			throw new NotFoundException("invalidStatementType");
		}

		// If we request a problem inside a contest
		if (!is_null($r["contest_alias"])) {
			// Is the combination contest_id and problem_id valid?
			try {
				$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);

				if (is_null($r["contest"])) {
					throw new NotFoundException("contestNotFound");
				}

				if (is_null(ContestProblemsDAO::getByPK($r["contest"]->getContestId(), $r["problem"]->getProblemId()))) {
					throw new NotFoundException("problemNotFoundInContest");
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
			if (!ContestsDAO::hasStarted($r["contest"]) && !Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
				throw new ForbiddenAccessException("contestNotStarted");
			}
		} else {

			if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
				// If the problem is requested outside a contest, we need to check that it is not private
				if ($r["problem"]->getPublic() == "0") {
					throw new ForbiddenAccessException("problemIsPrivate");
				}
			}
		}
	}

	/**
	 * Gets the problem statement from the filesystem.
	 * 
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 */
	public static function getProblemStatement(Request $r) {
		$statement_type = ProblemController::getStatementType($r);
		$source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["problem"]->getAlias() . DIRECTORY_SEPARATOR . 'statements' . DIRECTORY_SEPARATOR . $r["lang"] . "." . $statement_type;

		try {
			$file_content = FileHandler::ReadFile($source_path);
		} catch (Exception $e) {
			throw new InvalidFilesystemOperationException($e);
		}
		
		return $file_content;
	}

	/**
	 * Gets the sample input from the filesystem.
	 * 
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 */
	public static function getSampleInput(Request $r) {
		$source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["problem"]->getAlias() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'sample.in';

		try {
			$file_content = FileHandler::ReadFile($source_path);
		} catch (Exception $e) {
			// Most problems won't have a sample input.
			$file_content = '';
		}
		
		return $file_content;
	}

	/**
	 * Get the type of statement that was requested.
	 * HTML is the default if statement_type unspecified in the request.
	 * 
	 * @param Request $r
	 */
	private static function getStatementType(Request $r) {
		$type = "html";
		if (isset($r["statement_type"])) {
			$type = $r["statement_type"];
		}
		return $type;
	}

	/**
	 * Entry point for Problem Details API
	 * 
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiDetails(Request $r) {
		
		// Get user.
		// Allow unauthenticated requests if we are not openning a problem 
		// inside a contest.
		try {
			self::authenticateRequest($r);
		} catch(ForbiddenAccessException $e) {
			if (!is_null($r["contest_alias"])) {
				throw $e;
			}
		}

		// Validate request
		self::validateDetails($r);

		$response = array();

		// Create array of relevant columns
		$relevant_columns = array("title", "author_id", "alias", "validator", "time_limit",
			"validator_time_limit", "overall_wall_time_limit", "extra_wall_time",
			"memory_limit", "output_limit", "visits", "submissions", "accepted",
			"difficulty", "creation_date", "source", "order", "points", "public",
			"languages", "slow", "stack_limit");

		// Read the file that contains the source
		if ($r["problem"]->getValidator() != 'remote') {

			$statement_type = ProblemController::getStatementType($r);
			Cache::getFromCacheOrSet(Cache::PROBLEM_STATEMENT, $r["problem"]->getAlias() . "-" . $r["lang"] . "-" . $statement_type,
				$r, 'ProblemController::getProblemStatement', $file_content,
				APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT);

			// Add problem statement to source
			$response["problem_statement"] = $file_content;

		} else if ($r["problem"]->getServer() == 'uva') {
			$response["problem_statement"] = '<iframe src="http://acm.uva.es/p/v' . substr($r["problem"]->getRemoteId(), 0, strlen($r["problem"]->getRemoteId()) - 2) . '/' . $r["problem"]->getRemoteId() . '.html"></iframe>';
		}

		// Add the example input.
		$sample_input = null;
		Cache::getFromCacheOrSet(Cache::PROBLEM_SAMPLE, $r["problem"]->getAlias() . "-sample.in",
			$r, 'ProblemController::getSampleInput', $sample_input,
			APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT);
		if (!empty($sample_input)) {
			$response['sample_input'] = $sample_input;
		}

		// Add the problem the response
		$response = array_merge($response, $r["problem"]->asFilteredArray($relevant_columns));

		if (!is_null($r['current_user_id'])) {
			// Create array of relevant columns for list of runs
			$relevant_columns = array("guid", "language", "status", "verdict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");

			// Search the relevant runs from the DB
			$contest = ContestsDAO::getByAlias($r["contest_alias"]);

			$keyrun = new Runs(array(
				"user_id" => $r["current_user_id"],
				"problem_id" => $r["problem"]->getProblemId(),
				"contest_id" => is_null($r["contest"]) ? null : $r["contest"]->getContestId()
			));

			// Get all the available runs done by the current_user
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

			$response["runs"] = $runs_filtered_array;
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
		} else if (isset($r['show_solvers']) && $r['show_solvers']) {
			$response['solvers'] = RunsDAO::GetBestSolvingRunsForProblem($r['problem']->problem_id);
		}

		if (!is_null($r['current_user_id'])) {
			ProblemViewedDAO::MarkProblemViewed($r['current_user_id'],
				$r['problem']->problem_id);
		}

		$response["score"] = self::bestScore($r);
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

		if ($r['problem'] == null) {
			throw new NotFoundException("problemNotFound");
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

		if ($r['show_all']) {
			if (!Authorization::CanEditProblem($r['current_user_id'], $r['problem'])) {
				throw new ForbiddenAccessException();
			}
			if (!is_null($r['username'])) {
				try {
					$r['user'] = UsersDAO::FindByUsername($r['username']);
				} catch (Exception $e) {
					throw new NotFoundException('userNotFound');
				}
			}
			try {
				$runs = RunsDAO::GetAllRuns(
					null,
					$r["status"],
					$r["verdict"],
					$r["problem"]->problem_id,
					$r["language"],
					!is_null($r["user"]) ? $r["user"]->user_id : null,
					$r["offset"],
					$r["rowcount"]
				);

				$result = array();

				foreach ($runs as $run) {
					$run['time'] = (int)$run['time'];
					$run['score'] = round((float)$run['score'], 4);
					if ($run['contest_score'] != null) {
						$run['contest_score'] = round((float)$run['contest_score'], 2);
					}
					array_push($result, $run);
				}

				$response['runs'] = $result;
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}
		} else {
			$keyrun = new Runs(array(
				"user_id" => $r["current_user_id"],
				"problem_id" => $r["problem"]->getProblemId()
			));

			// Get all the available runs
			try {
				$runs_array = RunsDAO::search($keyrun);

				// Create array of relevant columns for list of runs
				$relevant_columns = array("guid", "language", "status", "verdict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");

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
			} catch (Exception $e) {
				// Operation failed in the data layer
				throw new InvalidDatabaseOperationException($e);
			}
		}

		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Entry point for Problem clarifications API
	 * 
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiClarifications(Request $r) {
		// Get user
		self::authenticateRequest($r);
		self::validateRuns($r);

		$is_problem_admin = Authorization::CanEditProblem($r['current_user_id'], $r['problem']);

		try {
			$clarifications = ClarificationsDAO::GetProblemClarifications(
				$r['problem']->problem_id,
				$is_problem_admin,
				$r['current_user_id'],
				$r['offset'],
				$r['rowcount']
			);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		foreach ($clarifications as &$clar) {
			$clar['time'] = (int)$clar['time'];
		}

		// Add response to array
		$response = array();
		$response['clarifications'] = $clarifications;
		$response['status'] = "ok";

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

			// List of verdicts			
			$verdict_counts = array();

			foreach (self::$verdicts as $verdict) {
				$verdict_counts[$verdict] = RunsDAO::CountTotalRunsOfProblemByVerdict($r["problem"]->getProblemId(), $verdict);
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
				$grade_dir = RunController::getGradePath($run);

				// Skip it if it failed to compile.
				if (file_exists("$grade_dir/compile_error.log")) {
					continue;
				}

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
			"verdict_counts" => $verdict_counts,
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
		if (!isset($r['page'])) {
			if (!isset($r["offset"])) {
				$r["offset"] = 0;
			}
			if (!isset($r["rowcount"])) {
				$r["rowcount"] = 1000;
			}
		}

		Validators::isStringNonEmpty($r["query"], "query", false);
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

		// Sort results
		$order = 'problem_id'; // Order by problem_id by default.
		$sorting_options = array('title', 'submissions', 'accepted', 'ratio', 'points', 'score');
		// "order_by" may be one of the allowed options, otherwise the default ordering will be used.
		if (!is_null($r['order_by']) && in_array($r['order_by'], $sorting_options)) {
			$order = $r['order_by'];
		}

		// "mode" may be a valid one, for compatibility reasons 'descending' is the mode by default.
		if (!is_null($r['mode']) && ($r['mode'] === 'asc' || $r['mode'] === 'desc')) {
			$mode = $r['mode'];
		} else {
			$mode = 'desc';
		}

		$response = array();
		$response["results"] = array();
		$author_id = null;
		// There are basically three types of users:
		// - Non-logged in users: Anonymous
		// - Logged in users with normal permissions: Normal
		// - Logged in users with administrative rights: Admin
		$user_type = USER_ANONYMOUS;
		if (!is_null($r['current_user_id'])) {
			$author_id = intval($r['current_user_id']);
			if (Authorization::IsSystemAdmin($r['current_user_id'])) {
				$user_type = USER_ADMIN;
			} else {
				$user_type = USER_NORMAL;
			}
		}

		// Search for problems whose title has $query as a substring.
		$query = is_null($r['query']) ? null : $r['query'];

		if (!is_null($r['offset']) && !is_null($r['rowcount'])) {
			// Skips the first $offset rows of the result.
			$offset = intval($r['offset']);

			// Specifies the maximum number of rows to return.
			$rowcount = intval($r['rowcount']);
		} else {
			$offset = (is_null($r['page']) ? 0 : intval($r['page']) - 1) *
				PROBLEMS_PER_PAGE;
			$rowcount = PROBLEMS_PER_PAGE;
		}

		$total = 0;
		$response['results'] = ProblemsDAO::byUserType(
			$user_type,
			$order,
			$mode,
			$offset,
			$rowcount,
			$query,
			$author_id,
			$r['tag'],
			$total
		);
		$response['total'] = $total;

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

	/**
	 * Returns the best score for a problem
	 * 
	 * @param Request $r
	 */
	public static function apiBestScore(Request $r) {

		self::authenticateRequest($r);

		// Uses same params as apiDetails, except for lang, which is optional
		self::validateDetails($r);

		// If username is set in the request, we use that user as target.
		// else, we query using current_user
		$user = self::resolveTargetUser($r);

		$response["score"] = self::bestScore($r, $user);
		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Returns the best score of a problem.
	 * Problem must be loadad in $r["problem"]
	 * Contest could be loadad in $r["contest"]. If set, will only look for
	 * runs inside that contest.
	 * 
	 * Authentication is expected to be performed earlier.
	 * 
	 * @param Request $r
	 * @return float
	 * @throws InvalidDatabaseOperationException
	 */
	private static function bestScore(Request $r, Users $user = null) {
		$current_user_id = (is_null($user) ? $r["current_user_id"] : $user->getUserId());

		if (is_null($current_user_id)) {
			return 0;
		}

		$score = 0;
		try {
			// Add best score info
			if (is_null($r["contest"])) {
				$score = RunsDAO::GetBestScore($r["problem"]->getProblemId(), $current_user_id);
			} else {
				$bestRun = RunsDAO::GetBestRun($r["contest"]->getContestId(), $r["problem"]->getProblemId(), $current_user_id, strtotime($r["contest"]->getFinishTime()), false /*showAllRuns*/);
				$score = is_null($bestRun->getContestScore()) ? 0 : $bestRun->getContestScore();
			}
		} catch(Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		return $score;
	}
}
