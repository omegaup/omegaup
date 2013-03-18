<?php

require_once 'libs/FileHandler.php';
require_once 'libs/ZipHandler.php';
require_once 'libs/Markdown/markdown.php';

/**
 * ProblemsController
 */
class ProblemController extends Controller {

	private static $contest;
	private static $author;
	private static $problem;

	const MAX_ZIP_FILESIZE = 104857600; //100 * 1024 * 1024;

	private static $hasValidator = false;
	private static $filesToUnzip;
	private static $casesFiles;
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
				throw new NotFoundException();
			}

			// We need to check that the user can actually edit the problem
			if (!Authorization::CanEditProblem($r["current_user_id"], $r["problem"])) {
				throw new ForbiddenAccessException();
			}
		}

		Validators::isStringNonEmpty($r["author_username"], "author_username", $is_required);

		if (!$is_update) {
			// Check if author_username actually exists
			$u = new Users();
			$u->setUsername($r["author_username"]);
			$users = UsersDAO::search($u);
			if (count($users) !== 1) {
				throw new NotFoundException("author_username not found");
			}

			$r["author"] = $users[0];
		}

		Validators::isStringNonEmpty($r["title"], "title", $is_required);
		Validators::isStringNonEmpty($r["source"], "source", $is_required);
		Validators::isStringNonEmpty($r["alias"], "alias", $is_required);
		Validators::isInEnum($r["public"], "public", array("0", "1"), $is_required);
		Validators::isInEnum($r["validator"], "validator", array("remote", "literal", "token", "token-caseless", "token-numeric"), $is_required);
		Validators::isNumberInRange($r["time_limit"], "time_limit", 0, INF, $is_required);
		Validators::isNumberInRange($r["memory_limit"], "memory_limit", 0, INF, $is_required);
		Validators::isInEnum($r["order"], "order", array("normal", "inverse"), $is_required);

		// If create, we require the zip validation. Otherwise it is optional
		if (!$is_update || isset($_FILES['problem_contents'])) {
			self::validateZip();
		}
	}

	/**
	 * Validates problem zip given that a problem zip containts a testplan file 
	 * 
	 * @param ZipArchive $zip
	 * @param array $zipFilesArray
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private static function checkCasesWithTestplan(ZipArchive $zip, array $zipFilesArray) {

		// Get testplan contents into an array
		$testplan = $zip->getFromName("testplan");
		$testplan_array = array();

		// LOL RegEx magic to get test case names from testplan
		preg_match_all('/^\\s*([^#]+?)\\s+(\\d+)\\s*$/m', $testplan, $testplan_array);

		for ($i = 0; $i < count($testplan_array[1]); $i++) {
			// Check .in file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.in';
			if (!$zip->getFromName($path)) {
				throw new InvalidParameterException("Not able to find " . $testplan_array[1][$i] . " in testplan.");
			}

			self::$filesToUnzip[] = $path;
			self::$casesFiles[] = $path;

			// Check .out file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.out';
			if (!$zip->getFromName($path)) {
				throw new InvalidParameterException("Not able to find " . $testplan_array[1][$i] . " in testplan.");
			}

			self::$filesToUnzip[] = $path;
			self::$casesFiles[] = $path;
		}

		return true;
	}

	/**
	 * Helper function to check whether a string ends with $needle
	 * 
	 * @param string $haystack
	 * @param string $needle
	 * @param boolean $case
	 * @return boolean
	 */
	private static function endsWith($haystack, $needle, $case) {
		$expectedPosition = strlen($haystack) - strlen($needle);

		$ans = false;

		if ($case) {
			return strrpos($haystack, $needle, 0) === $expectedPosition;
		} else {
			return strripos($haystack, $needle, 0) === $expectedPosition;
		}
	}

	/**
	 * Validates the cases of a problem zip without testplan
	 * 
	 * @param ZipArchive $zip
	 * @param array $zipFilesArray
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private static function checkCases(ZipArchive $zip, array $zipFilesArray) {
		// Necesitamos tener al menos 1 input
		$inputs = 0;
		$outputs = 0;

		// Add all files in cases/ that end either in .in or .out        
		for ($i = 0; $i < count($zipFilesArray); $i++) {
			$path = $zipFilesArray[$i];

			if (strpos($path, "cases/") == 0) {
				$isInput = self::endsWith($path, ".in", true);
				$isOutput = self::endsWith($path, ".out", true);

				if ($isInput || $isOutput) {
					self::$filesToUnzip[] = $path;
					self::$casesFiles[] = $path;
				}

				if ($isInput) {
					$inputs++;
				} else if ($isOutput) {
					$outputs++;
				}
			}
		}

		if ($inputs < 1) {
			throw new InvalidParameterException("0 inputs found. At least 1 input is needed.");
		}

		Logger::log($inputs . " found, " . $outputs . "found ");

		if (self::$hasValidator === false && $inputs != $outputs) {
			throw new InvalidParameterException("Inputs/Outputs mistmatch: " . $inputs . " found, " . $outputs . "found ");
		}

		return true;
	}

	/**
	 * 
	 * @param array $zipFilesArray
	 * @param ZipArchive $zip
	 * @return boolean
	 */
	private static function checkProblemStatements(array $zipFilesArray, ZipArchive $zip) {
		Logger::log("Checking problem statements...");

		// We need at least one statement
		$statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $zipFilesArray);

		if (count($statements) < 1) {
			throw new InvalidParameterException("No statements found");
		}

		// Add statements to the files to be unzipped
		foreach ($statements as $file) {
			// Revisar que los statements no esten vacíos                    
			if (strlen($zip->getFromName($file, 1)) < 1) {
				throw new InvalidParameterException("Statement {$file} is empty.");
			}

			Logger::log("Adding statements to the files to be unzipped: " . $file);
			self::$filesToUnzip[] = $file;
		}

		return true;
	}

	/**
	 * Entry point for zip validation
	 * 
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private static function validateZip() {

		Logger::log("Validating zip...");

		if(!array_key_exists("problem_contents", $_FILES)) {
			throw new InvalidParameterException("problem_contents is invalid.");
		}

		if (isset($_FILES['problem_contents']) &&
				!FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
			throw new InvalidParameterException("problem_contents is invalid.");
		}


		self::$filesToUnzip = array();
		self::$casesFiles = array();

		$value = $_FILES['problem_contents']['tmp_name'];


		Logger::log("Opening $value...");
		$zip = new ZipArchive();
		$resource = $zip->open($value);

		$size = 0;
		if ($resource === TRUE) {
			// Get list of files
			for ($i = 0; $i < $zip->numFiles; $i++) {
				Logger::log("Found inside zip: '" . $zip->getNameIndex($i) . "'");
				$zipFilesArray[] = $zip->getNameIndex($i);

				// Sum up the size
				$statI = $zip->statIndex($i);
				$size += $statI['size'];

				// If the file is THE validator for custom outputs...
				if (stripos($zip->getNameIndex($i), 'validator.') === 0) {
					self::$hasValidator = true;
					self::$filesToUnzip[] = $zip->getNameIndex($i);
					Logger::log("Validator found: " . $zip->getNameIndex($i));
				}
			}

			if ($size > self::MAX_ZIP_FILESIZE) {
				throw new InvalidParameterException("Extracted zip size ($size) over {$maximumSize}MB. Rejecting.");
			}

			try {

				// Look for testplan
				if (in_array("testplan", $zipFilesArray)) {

					self::checkCasesWithTestplan($zip, $zipFilesArray);
					Logger::log("testplan found, checkCasesWithTestPlan=" . $returnValue);
					self::$filesToUnzip[] = 'testplan';
				} else {
					Logger::log("testplan not found");
					self::checkCases($zip, $zipFilesArray);
				}

				// Log files to unzip
				Logger::log("Files to unzip: ");
				foreach (self::$filesToUnzip as $file) {
					Logger::log($file);
				}

				// Look for statements
				$returnValue = self::checkProblemStatements($zipFilesArray, $zip);
				Logger::log("checkProblemStatements=" . $returnValue . ".");
			} catch (ApiException $e) {
				// Close zip
				Logger::log("Validation Failed. Closing zip");
				$zip->close();

				throw $e;
			}

			// Close zip
			Logger::log("closing zip");
			$zip->close();

			return $returnValue;
		} else {
			throw new InvalidParameterException("Unable to open zip." . ZipHandler::zipFileErrMsg($resource));
		}

		return true;
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
		$problem->setPublic($r["public"]);
		$problem->setTitle($r["title"]);
		$problem->setAlias($r["alias"]);
		$problem->setValidator($r["validator"]);
		$problem->setTimeLimit($r["time_limit"]);
		$problem->setMemoryLimit($r["memory_limit"]);
		$problem->setVisits(0);
		$problem->setSubmissions(0);
		$problem->setAccepted(0);
		$problem->setDifficulty(0);
		$problem->setSource($r["source"]);
		$problem->setOrder($r["order"]);
		$problem->setAuthorId($r["author"]->getUserId());

		// Insert new problem
		try {
			
			ProblemsDAO::transBegin();

			// Save the contest object with data sent by user to the database
			ProblemsDAO::save($problem);

			// Create file after we know that alias is unique
			self::deployProblemZip(self::$filesToUnzip, self::$casesFiles, $r);
			
			ProblemsDAO::transEnd();
			
		} catch (ApiException $e) {

			// Operation failed in the data layer, rollback transaction 
			ProblemsDAO::transRollback();

			// Rollback the problem if deployed partially
			self::deleteProblemFromFilesystem(self::getDirpath($r));

			throw $e;
		} catch (Exception $e) {

			// Operation failed in the data layer, rollback transaction 
			ProblemsDAO::transRollback();

			// Rollback the problem if deployed partially
			self::deleteProblemFromFilesystem(self::getDirpath($r));

			// Alias may be duplicated, 1062 error indicates that
			if (strpos($e->getMessage(), "1062") !== FALSE) {
				throw new DuplicatedEntryInDatabaseException("contest_alias already exists.", $e);
			} else {
				throw new InvalidDatabaseOperationException($e);
			}
		}

		// Adding unzipped files to response
		$result["uploaded_files"] = self::$filesToUnzip;
		$result["status"] = "ok";

		// Invalidar cache
		$contestCache = new Cache(Cache::CONTEST_INFO, $r["contest_alias"]);
		$contestCache->delete();		

		return $result;
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

		self::validateCreateOrUpdate($r, true /*is update*/);

		// Update the Problem object        
		if (!is_null($r["public"])) {
			$r["problem"]->setPublic($r["public"]);
		}

		if (!is_null($r["title"])) {
			$r["problem"]->setTitle($r["title"]);
		}

		if (!is_null($r["validator"])) {
			$r["problem"]->setValidator($r["validator"]);
		}

		if (!is_null($r["time_limit"])) {
			$r["problem"]->setTimeLimit($r["time_limit"]);
		}

		if (!is_null($r["memory_limit"])) {
			$r["problem"]->setMemoryLimit($r["memory_limit"]);
		}

		if (!is_null($r["source"])) {
			$r["problem"]->setSource($r["source"]);
		}

		if (!is_null($r["order"])) {
			$r["problem"]->setOrder($r["order"]);
		}

		$response = array();

		// Insert new problem
		try {
			//Begin transaction
			ProblemsDAO::transBegin();

			// Save the contest object with data sent by user to the database
			ProblemsDAO::save($r["problem"]);

			if (isset($_FILES['problem_contents'])) {

				// DeployProblemZip requires alias => problem_alias
				$r["alias"] = $r["problem_alias"];

				self::DeployProblemZip(self::$filesToUnzip, self::$casesFiles, $r, true /* is update */);
				$response["uploaded_files"] = self::$filesToUnzip;
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
	 * 
	 * @param string $dirpath
	 * @param array $filesToUnzip
	 */
	private static function handleStatements($dirpath, array $filesToUnzip = null) {

		// Get a list of all available statements.
		// At this point, zip is validated and it has at least 1 statement. No need to check
		$statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $filesToUnzip);
		Logger::log("Handling statements...");

		// Transform statements from markdown to HTML  
		foreach ($statements as $statement) {

			// Get the path to the markdown unzipped file
			$markdown_filepath = $dirpath . DIRECTORY_SEPARATOR . $statement;
			Logger::log("Reading file " . $markdown_filepath);

			// Read the contents of the original markdown file
			$markdown_file_contents = FileHandler::ReadFile($markdown_filepath);

			// Fix for Windows Latin-1 statements:
			// For now, assume that if it is not UTF-8, then it is Windows Latin-1 and then convert
			if (!mb_check_encoding($markdown_file_contents, "UTF-8")) {
				Logger::log("File is not UTF-8.");

				// Convert from ISO-8859-1 (Windows Latin1) to UTF-8
				Logger::log("Converting encoding from ISO-8859-1 to UTF-8 (Windows Latin1 to UTF-8, fixing accents)");
				$markdown_file_contents = mb_convert_encoding($markdown_file_contents, "UTF-8", "ISO-8859-1");

				// Then overwrite it into the statement file
				Logger::log("Overwriting file after encoding conversion: " . $markdown_filepath);
				FileHandler::CreateFile($markdown_filepath, $markdown_file_contents);
			} else {
				Logger::log("File is UTF-8. Nice :)");
			}

			// Transform markdown to HTML
			Logger::log("Transforming markdown to html");
			$html_file_contents = markdown($markdown_file_contents);

			// Get the language of this statement            
			$lang = basename($statement, ".markdown");

			$html_filepath = $dirpath . DIRECTORY_SEPARATOR . "statements" . DIRECTORY_SEPARATOR . $lang . ".html";

			// Save the HTML file in the path .../problem_alias/statements/lang.html            
			Logger::log("Saving HTML statement in " . $html_filepath);
			FileHandler::CreateFile($html_filepath, $html_file_contents);
		}
	}

	/**
	 * Handle unzipped cases
	 * 
	 * @param string $dirpath
	 * @param array $casesFiles
	 * @throws InvalidFilesystemOperationException
	 */
	private static function handleCases($dirpath, array $casesFiles) {

		Logger::log("Handling cases...");

		// Aplying dos2unix to cases
		$return_var = 0;
		$output = array();
		$dos2unix_cmd = "dos2unix " . $dirpath . DIRECTORY_SEPARATOR . "cases/* 2>&1";
		Logger::log("Applying dos2unix: " . $dos2unix_cmd);
		exec($dos2unix_cmd, $output, $return_var);

		// Log errors
		if ($return_var !== 0) {
			Logger::warn("dos2unix failed with error: " . $return_var);
		} else {
			Logger::log("dos2unix succeeded");
		}
		Logger::log(implode("\n", $output));


		// After dos2unixfication, we need to generate a zip file that will be
		// passed between grader and runners with the INPUT files...                
		// Create path to cases.zip and proper cmds
		$cases_zip_path = $dirpath . DIRECTORY_SEPARATOR . 'cases.zip';
		$cases_to_be_zipped = $dirpath . DIRECTORY_SEPARATOR . "cases/*.in";

		// cmd to be executed in console
		$zip_cmd = "zip -j " . $cases_zip_path . " " . $cases_to_be_zipped . " 2>&1";

		// Execute zip command
		$output = array();
		Logger::log("Zipping input cases using: " . $zip_cmd);
		exec($zip_cmd, $output, $return_var);

		// Check zip cmd return value
		if ($return_var !== 0) {
			// D:
			Logger::error("zipping cases failed with error: " . $return_var);
			throw new InvalidFilesystemOperationException("Error creating cases.zip. Please check log for details");
		} else {
			// :D
			Logger::log("zipping cases succeeded:");
			Logger::log(implode("\n", $output));
		}

		// Generate sha1sum for cases.zip distribution from grader to runners
		Logger::log("Writing to : " . $dirpath . DIRECTORY_SEPARATOR . "inputname");
		file_put_contents($dirpath . DIRECTORY_SEPARATOR . "inputname", sha1_file($cases_zip_path));
	}

	/**
	 * 
	 * @param string $dirpath
	 * @param string $path_to_contents_zip
	 * @return type
	 */
	private static function updateContentsDotZip($dirpath, $path_to_contents_zip) {

		// Delete whathever the user sent us
		if (!unlink($path_to_contents_zip)) {
			Logger::warn("Unable to delete contents.zip to replace with original contents!: " . $path_to_contents_zip);
			return;
		}

		// Set directory to the one where contents.zip is to handle paths inside
		// the zip correcly 
		$original_dir = getcwd();
		chdir($dirpath);

		// cmd to be executed in console
		// cases/*
		$output = array();

		$zip_cmd = "zip -r " . $path_to_contents_zip . " cases/* 2>&1";
		Logger::log("Zipping contents.zip cases using: " . $zip_cmd);
		exec($zip_cmd, $output, $return_var);

		// Check zip cmd return value
		if ($return_var !== 0) {
			// D:
			Logger::error("zipping cases/* contents.zip failed with error: " . $return_var);
		} else {
			// :D
			Logger::log("zipping cases contents.zip succeeded:");
			Logger::log(implode("\n", $output));
		}

		// 
		// statements/*
		$output = array();

		$zip_cmd = "zip -r " . $path_to_contents_zip . " statements/* 2>&1";
		Logger::log("Zipping contents.zip statements using: " . $zip_cmd);
		exec($zip_cmd, $output, $return_var);


		// Check zip cmd return value
		if ($return_var !== 0) {
			// D:
			Logger::error("zipping statements/* contents.zip failed with error: " . $return_var);
		} else {
			// :D
			Logger::log("zipping statements contents.zip succeeded:");
			Logger::log(implode("\n", $output));
		}

		// get back to original dir
		chdir($original_dir);
	}

	/**
	 * Returns the path where the problem contents will be placed
	 * 
	 * @param Request $r
	 * @return string
	 */
	private static function getDirpath(Request $r) {
		return PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["alias"];
	}

	/**
	 * 
	 * @param string $dirpath
	 * @return string
	 */
	private static function getFilepath($dirpath) {
		return $dirpath . DIRECTORY_SEPARATOR . 'contents.zip';
	}

	private static function deleteProblemFromFilesystem($dirpath) {
		// Drop contents into path required
		FileHandler::DeleteDirRecursive($dirpath);
	}

	/**
	 * 
	 * @param array $filesToUnzip
	 * @param array $casesFiles
	 * @param boolean $isUpdate
	 * @throws ApiException
	 */
	private static function deployProblemZip($filesToUnzip, $casesFiles, Request $r, $isUpdate = false) {

		try {
			// Create paths
			$dirpath = self::getDirpath($r);
			$filepath = self::getFilepath($dirpath);

			if ($isUpdate === true) {
				self::deleteProblemFromFilesystem($dirpath);
			}

			// Making target directory
			FileHandler::MakeDir($dirpath);

			// Move stuff uploaded by user from PHP realm to our directory
			FileHandler::MoveFileFromRequestTo('problem_contents', $filepath);

			// Unzip the user's zip
			ZipHandler::DeflateZip($filepath, $dirpath, $filesToUnzip);

			// Handle statements
			self::handleStatements($dirpath, $filesToUnzip);

			// Handle cases
			self::handleCases($dirpath, $casesFiles);

			// Update contents.zip
			self::updateContentsDotZip($dirpath, $filepath);
		} catch (Exception $e) {
			throw new InvalidFilesystemOperationException("Unable to process problem_contents given. Please check the format. ", $e);
		}
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

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");

		// Lang is optional. Default is ES
		if (!is_null($r["lang"])) {
			Validators::isStringOfMaxLength($r["lang"], "lang", 2);
		} else {
			$r["lang"] = "es";
		}

		// Is the combination contest_id and problem_id valid?        
		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
			self::$problem = ProblemsDAO::getByAlias($r["problem_alias"]);

			if (is_null(ContestProblemsDAO::getByPK(self::$contest->getContestId(), self::$problem->getProblemId()))) {
				throw new NotFoundException();
			}
		} catch (ApiException $apiException) {
			throw $apiException;
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}


		// If the contest is private, verify that our user is invited                        
		if (self::$contest->getPublic() == 0) {
			if (is_null(ContestsUsersDAO::getByPK($r["current_user_id"], self::$contest->getContestId())) && !Authorization::IsContestAdmin($r["current_user_id"], self::$contest)) {
				throw new ForbiddenAccessException();
			}
		}

		// If the contest has not started, user should not see it, unless it is admin
		if (!self::$contest->hasStarted($r["current_user_id"]) && !Authorization::IsContestAdmin($r["current_user_id"], self::$contest)) {
			throw new ForbiddenAccessException("Contest has not started yet.");
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
		$relevant_columns = array("title", "author_id", "alias", "validator", "time_limit", "memory_limit", "visits", "submissions", "accepted", "difficulty", "creation_date", "source", "order", "points");

		// Read the file that contains the source
		if (self::$problem->getValidator() != 'remote') {
			$statementCache = new Cache(Cache::PROBLEM_STATEMENT, self::$problem->getAlias() . "-" . $r["lang"]);
			$file_content = null;

			// check cache
			$file_content = $statementCache->get();

			if (is_null($file_content)) {

				$source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . self::$problem->getAlias() . DIRECTORY_SEPARATOR . 'statements' . DIRECTORY_SEPARATOR . $r["lang"] . ".html";

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
		} else if (self::$problem->getServer() == 'uva') {
			$response["problem_statement"] = '<iframe src="http://acm.uva.es/p/v' . substr(self::$problem->getRemoteId(), 0, strlen(self::$problem->getRemoteId()) - 2) . '/' . self::$problem->getRemoteId() . '.html"></iframe>';
		}

		// Add the problem the response
		$response = array_merge($response, self::$problem->asFilteredArray($relevant_columns));

		// Create array of relevant columns for list of runs
		$relevant_columns = array("guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay");

		// Search the relevant runs from the DB
		$contest = ContestsDAO::getByAlias($r["contest_alias"]);

		$keyrun = new Runs(array(
					"user_id" => $r["current_user_id"],
					"problem_id" => self::$problem->getProblemId(),
					"contest_id" => self::$contest->getContestId()
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

		// At this point, contestant_user relationship should be established.        
		try {
			$contest_user = ContestsUsersDAO::CheckAndSaveFirstTimeAccess(
							$r["current_user_id"], self::$contest->getContestId());
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		// As last step, register the problem as opened                
		if (!ContestProblemOpenedDAO::getByPK(
						self::$contest->getContestId(), self::$problem->getProblemId(), $r["current_user_id"])) {
			//Create temp object
			$keyContestProblemOpened = new ContestProblemOpened(array(
						"contest_id" => self::$contest->getContestId(),
						"problem_id" => self::$problem->getProblemId(),
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

		// Add the procesed runs to the request
		$response["runs"] = $runs_filtered_array;
		$response["status"] = "ok";
		return $response;
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
		self::validateDetails($r);

		$response = array();

		$keyrun = new Runs(array(
					"user_id" => $r["current_user_id"],
					"problem_id" => self::$problem->getProblemId()
				));

		// Get all the available runs
		try {
			$runs_array = RunsDAO::search($keyrun);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		// Add the procesed runs to the request
		$response["runs"] = $runs_array;
		$response["status"] = "ok";
		return $response;
	}

}
