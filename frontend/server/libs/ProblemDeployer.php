<?php

/**
 * Unzip and deploy a problem
 *
 * @author joemmanuel
 */
class ProblemDeployer {
	
	const MAX_ZIP_FILESIZE = 209715200; //200 * 1024 * 1024;
	
	public $filesToUnzip;
	private $imageHashes;
	private $casesFiles;
	private $hasValidator = false;	
	
	/**
	 * 
	 * @param array $zipFilesArray
	 * @param ZipArchive $zip
	 * @return boolean
	 */
	private function checkProblemStatements(array $zipFilesArray, ZipArchive $zip) {
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
			$this->filesToUnzip[] = $file;
		}

		// Also extract any images in the statements directory.
		$images = preg_grep('/^statements\/.*\.(gif|jpg|jpeg)$/', $zipFilesArray);

		// Add images to the files to be unzipped.
		foreach ($images as $file) {
			$this->filesToUnzip[] = $file;
			$this->imageHashes[substr($file, strlen('statements/'))] = true;
		}

		return true;
	}
	
	/**
	 * Validates the cases of a problem zip without testplan
	 * 
	 * @param ZipArchive $zip
	 * @param array $zipFilesArray
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private function checkCases(ZipArchive $zip, array $zipFilesArray) {
		// Necesitamos tener al menos 1 input
		$inputs = 0;
		$outputs = 0;

		// Add all files in cases/ that end either in .in or .out        
		for ($i = 0; $i < count($zipFilesArray); $i++) {
			$path = $zipFilesArray[$i];

			if (strpos($path, "cases/") == 0) {
				$isInput = $this->endsWith($path, ".in", true);
				$isOutput = $this->endsWith($path, ".out", true);

				if ($isInput || $isOutput) {
					$this->filesToUnzip[] = $path;
					$this->casesFiles[] = $path;
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

		if ($this->hasValidator === false && $inputs != $outputs) {
			throw new InvalidParameterException("Inputs/Outputs mistmatch: " . $inputs . " found, " . $outputs . "found ");
		}

		return true;
	}
	
	/**
	 * Validates problem zip given that a problem zip containts a testplan file 
	 * 
	 * @param ZipArchive $zip
	 * @param array $zipFilesArray
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private function checkCasesWithTestplan(ZipArchive $zip, array $zipFilesArray) {

		// Get testplan contents into an array
		$testplan = $zip->getFromName("testplan");
		$testplan_array = array();

		// LOL RegEx magic to get test case names from testplan
		preg_match_all('/^\\s*([^#]+?)\\s+(\\d+)\\s*$/m', $testplan, $testplan_array);

		for ($i = 0; $i < count($testplan_array[1]); $i++) {
			// Check .in file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.in';
			if ($zip->getFromName($path) === FALSE) {
				throw new InvalidParameterException("Not able to find " . $testplan_array[1][$i] . " input in testplan.");
			}

			$this->filesToUnzip[] = $path;
			$this->casesFiles[] = $path;

			// Check .out file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.out';
			if ($zip->getFromName($path) === FALSE) {
				throw new InvalidParameterException("Not able to find " . $testplan_array[1][$i] . " output in testplan.");
			}

			$this->filesToUnzip[] = $path;
			$this->casesFiles[] = $path;
		}

		return true;
	}

	/**
	 * Entry point for zip validation
	 * 
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private function validateZip() {

		Logger::log("Validating zip...");

		if (!array_key_exists("problem_contents", $_FILES)) {
			throw new InvalidParameterException("problem_contents is invalid.");
		}

		if (isset($_FILES['problem_contents']) &&
				!FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
			throw new InvalidParameterException("problem_contents is invalid.");
		}

		$this->filesToUnzip = array();
		$this->imageHashes = array();
		$this->casesFiles = array();

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
					$this->hasValidator = true;
					$this->filesToUnzip[] = $zip->getNameIndex($i);
					Logger::log("Validator found: " . $zip->getNameIndex($i));
				}
			}

			if ($size > ProblemDeployer::MAX_ZIP_FILESIZE) {
				throw new InvalidParameterException("Extracted zip size ($size) over max allowed MB. Rejecting.");
			}

			try {

				// Look for testplan
				if (in_array("testplan", $zipFilesArray)) {

					$returnValue = $this->checkCasesWithTestplan($zip, $zipFilesArray);
					Logger::log("testplan found, checkCasesWithTestPlan=" . $returnValue);
					$this->filesToUnzip[] = 'testplan';
				} else {
					Logger::log("testplan not found");
					$this->checkCases($zip, $zipFilesArray);
				}

				// Log files to unzip
				Logger::log("Files to unzip: ");
				foreach ($this->filesToUnzip as $file) {
					Logger::log($file);
				}

				// Look for statements
				$returnValue = $this->checkProblemStatements($zipFilesArray, $zip);
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

		return;
	}

	
	/**
	 * 
	 * @param string $dirpath
	 * @param array $filesToUnzip
	 */
	private function handleStatements($dirpath, array $filesToUnzip = null) {

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
			$html_file_contents = Markdown($markdown_file_contents, array($this, 'imageMarkdownCallback'));

			// Get the language of this statement            
			$lang = basename($statement, ".markdown");

			$html_filepath = $dirpath . DIRECTORY_SEPARATOR . "statements" . DIRECTORY_SEPARATOR . $lang . ".html";

			// Save the HTML file in the path .../problem_alias/statements/lang.html            
			Logger::log("Saving HTML statement in " . $html_filepath);
			FileHandler::CreateFile($html_filepath, $html_file_contents);
		}
	}

	public function imageMarkdownCallback($imagepath) {
		if (array_key_exists($imagepath, $this->imageHashes)) {
			if (is_bool($this->imageHashes[$imagepath])) {
				// TODO: copy the image to somewhere in /var/www, get its SHA-1 sum,
				// and store it in the imageHashes array.
				$this->imageHashes[$imagepath] = "27938919b32434b39486d04db57d5b8dccbe881b.jpg";
			}
			return $this->imageHashes[$imagepath];
		} else {
			// Also support absolute urls.
			return $imagepath;
		}
	}

	/**
	 * Handle unzipped cases
	 * 
	 * @param string $dirpath
	 * @param array $casesFiles
	 * @throws InvalidFilesystemOperationException
	 */
	private function handleCases($dirpath, array $casesFiles) {

		Logger::log("Handling cases...");

		// Aplying normalizr to cases
		$return_var = 0;
		$output = array();
		$normalizr_cmd = BIN_PATH . "/normalizr " . $dirpath . DIRECTORY_SEPARATOR . "cases/* 2>&1";
		Logger::log("Applying normalizr: " . $normalizr_cmd);
		exec($normalizr_cmd, $output, $return_var);

		// Log errors
		if ($return_var !== 0) {
			Logger::warn("normalizr failed with error: " . $return_var);
		} else {
			Logger::log("normalizr succeeded");
		}
		Logger::log(implode("\n", $output));

		// After normalizrfication, we need to generate a zip file that will be
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
	private function updateContentsDotZip($dirpath, $path_to_contents_zip) {

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
	private function getDirpath(Request $r) {
		return PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["alias"];
	}

	/**
	 * 
	 * @param string $dirpath
	 * @return string
	 */
	private function getFilepath($dirpath) {
		return $dirpath . DIRECTORY_SEPARATOR . 'contents.zip';
	}

	/**
	 * Removes a problem from the filesystem
	 * 
	 * @param string $dirpath
	 */
	public function deleteProblemFromFilesystem(Request $r) {
		// Drop contents into path required
		FileHandler::DeleteDirRecursive($this->getDirpath($r));
	}
	
	/**
	 * Helper function to check whether a string ends with $needle
	 * 
	 * @param string $haystack
	 * @param string $needle
	 * @param boolean $case
	 * @return boolean
	 */
	private function endsWith($haystack, $needle, $case) {
		$expectedPosition = strlen($haystack) - strlen($needle);

		$ans = false;

		if ($case) {
			return strrpos($haystack, $needle, 0) === $expectedPosition;
		} else {
			return strripos($haystack, $needle, 0) === $expectedPosition;
		}
	}

	/**
	 * Validates zip contents and deploys the problem
	 * 
	 * @param Request $r
	 * @param type $isUpdate
	 * @throws InvalidFilesystemOperationException
	 */
	public function deploy(Request $r, $isUpdate = false) {

		$this->validateZip();
		
		try {
			// Create paths
			$dirpath = $this->getDirpath($r);
			$filepath = $this->getFilepath($dirpath);

			if ($isUpdate === true) {
				$this->deleteProblemFromFilesystem($r);
			}

			// Making target directory
			FileHandler::MakeDir($dirpath);

			// Move stuff uploaded by user from PHP realm to our directory
			FileHandler::MoveFileFromRequestTo('problem_contents', $filepath);

			// Unzip the user's zip
			ZipHandler::DeflateZip($filepath, $dirpath, $this->filesToUnzip);

			// Handle statements
			$this->handleStatements($dirpath, $this->filesToUnzip);

			// Handle cases
			$this->handleCases($dirpath, $this->casesFiles);

			// Update contents.zip
			$this->updateContentsDotZip($dirpath, $filepath);
		} catch (Exception $e) {
			throw new InvalidFilesystemOperationException("Unable to process problem_contents given. Please check the format. ", $e);
		}
	}

}

