<?php

/**
 * Unzip and deploy a problem
 *
 * @author joemmanuel
 */

class ProblemDeployer {
	const MAX_ZIP_FILESIZE = 209715200; // 200 * 1024 * 1024;
	const MAX_INTERACTIVE_ZIP_FILESIZE = 524288000; // 500 * 1024 * 1024;
	const SLOW_QUEUE_THRESHOLD = 30;
	const MAX_RUNTIME_HARD_LIMIT = 300; // 5 * 60

	const CREATE = 0;
	const UPDATE_CASES = 1;
	const UPDATE_STATEMENTS = 2;

	public $filesToUnzip;
	private $imageHashes;
	private $casesFiles;
	private $log;
	private $current_markdown_file_contents;
	private $currentLanguage;

	private $alias;
	private $tmpDir = null;
	private $targetDir = null;
	private $zipPath = null;
	public $hasValidator = false;
	private $isInteractive = false;
	private $created = false;
	private $operation = null;

	public function __construct($alias, $operation) {
		$this->log = Logger::getLogger("ProblemDeployer");
		$this->alias = $alias;

		$this->tmpDir = FileHandler::TempDir("/tmp", "ProblemDeployer", 0755);
		$this->targetDir = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $this->alias;
		$this->gitDir = PROBLEMS_GIT_PATH . DIRECTORY_SEPARATOR . $this->alias;
		$this->operation = $operation;

		if (!is_writable(PROBLEMS_GIT_PATH)) {
			$this->log->error("path is not writable:" . PROBLEMS_GIT_PATH);
			throw new ProblemDeploymentFailedException();
		}

		if ($this->operation == ProblemDeployer::CREATE) {
			// Atomically try to create the bare repository.
			if (!@mkdir($this->gitDir, 0755)) {
				throw new InvalidParameterException("aliasInUse");
			}
			$this->git('init -q --bare ' . escapeshellarg($this->gitDir), PROBLEMS_GIT_PATH);
			$created = true;
		}

		// Clone repository into tmp dir
		$this->git('clone ' . escapeshellarg($this->gitDir) . ' ' .
		           escapeshellarg($this->tmpDir), '/tmp');

		// Ensure .gitattributes flags all inputs/outputs as binaries so it does not
		// take several minutes diffing them to save a little space.
		if (!file_exists("$this->tmpDir/.gitattributes")) {
			FileHandler::CreateFile("$this->tmpDir/.gitattributes",
				"cases/in/* -diff -delta -merge -text -crlf\n" .
				"cases/out/* -diff -delta -merge -text -crlf");
		}

		if ($this->operation == ProblemDeployer::UPDATE_CASES) {
			$dh = opendir($this->tmpDir);
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..' || $file == '.git' ||
				    $file == 'statements' || $file == '.gitattributes') {
					continue;
				}
				$this->git('rm -rf ' . escapeshellarg($file), $this->tmpDir);
			}
			closedir($dh);
		}
	}

	public function __destruct() {
		$this->cleanup();
	}

	private function git($cmd, $cwd) {
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		$proc = proc_open("/usr/bin/git $cmd", $descriptorspec, $pipes, $cwd, array());

		if (!is_resource($proc)) {
			$errors = error_get_last();
			$this->log->error("git $cmd failed: {$errors['type']} {$errors['message']}");
			throw new ProblemDeploymentFailedException();
		}

		fclose($pipes[0]);
		$output = stream_get_contents($pipes[1]);
		$err = stream_get_contents($pipes[2]);

		$retval = proc_close($proc);
		if ($retval != 0) {
			$this->log->error("git $cmd failed: $retval $output $err");
			throw new ProblemDeploymentFailedException();
		}

		return $output;
	}

	public function commit($message, $user) {
		$this->git('add .', $this->tmpDir);
		if ($this->git('status -s --porcelain', $this->tmpDir) == '') {
			// No changes detected. Return happily.
			return;
		}
		$this->git('config user.email ' . escapeshellarg("$user->username@omegaup"),
		           $this->tmpDir);
		$this->git('config user.name ' . escapeshellarg($user->username), $this->tmpDir);
		$this->git('config push.default simple', $this->tmpDir);
		$this->git('commit -am ' . escapeshellarg($message), $this->tmpDir);
		$this->git('push', $this->tmpDir);

		if (!file_exists($this->targetDir . DIRECTORY_SEPARATOR . ".git")) {
			$this->git('clone ' . escapeshellarg($this->gitDir) . ' ' .
			           escapeshellarg($this->targetDir), PROBLEMS_PATH);
		} else {
			$this->git('pull --rebase', $this->targetDir);
		}
	}

	public function cleanup() {
		if ($this->tmpDir != null && is_dir($this->tmpDir)) {
			FileHandler::DeleteDirRecursive($this->tmpDir);
		}

		// Something went wrong and the target directory was not committed. Rollback.
		if ($this->created && !file_exists($this->tagetDir)) {
			FileHandler::DeleteDirRecursive($this->gitDir);
		}
	}

	/**
	 * Updates an statement.
	 * Assumes $r["lang"] and $r["statement"] are set
	 *
	 * @param Request $r
	 * @throws ProblemDeploymentFailedException
	 */
	public function updateStatement($lang, $statement) {
		try {
			$this->log->info("Starting statement update, lang: $lang");

			// Delete statement files
			$markdownFile = "$this->tmpDir/statements/$lang.markdown";
			$htmlFile = "$this->tmpDir/statements/$lang.html";
			if (file_exists($markdownFile)) {
				$this->git('rm -f ' . escapeshellarg($markdownFile), $this->tmpDir);
			}
			if (file_exists($htmlFile)) {
				$this->git('rm -f ' . escapeshellarg($htmlFile), $this->tmpDir);
			}

			if (!is_dir("$this->tmpDir/statements")) {
				mkdir("$this->tmpDir/statements", 0755);
			}

			// Deploy statement
			FileHandler::CreateFile($markdownFile, $statement);
			$this->current_markdown_file_contents = $statement;
			$this->HTMLizeStatement($this->tmpDir, "$lang.markdown");
		} catch (ApiException $e) {
			throw new ProblemDeploymentFailedException($e->getMessage(), $e);
		} catch (Exception $e) {
			$this->log->error("Failed to deploy $e");
			throw new ProblemDeploymentFailedException('problemDeployerFailed', $e);
		}
	}

	/**
	 * Validates zip contents and deploys the problem
	 *
	 * @param Request $r
	 * @param type $isUpdate
	 * @throws InvalidFilesystemOperationException
	 */
	public function deploy() {
		$this->validateZip();

		if (!file_exists("$this->tmpDir/cases/in")) {
			mkdir("$this->tmpDir/cases/in", 0755, true);
		}

		if (!file_exists("$this->tmpDir/cases/out")) {
			mkdir("$this->tmpDir/cases/out", 0755, true);
		}

		try {
			// Unzip the user's zip
			ZipHandler::DeflateZip($this->zipPath, $this->tmpDir, $this->filesToUnzip);

			// Move all .in and .out files to their folder.
			$dh = opendir("$this->tmpDir/cases/");
			while (($file = readdir($dh)) !== false) {
				if ($this->endsWith($file, '.out', true)) {
					rename("$this->tmpDir/cases/$file", "$this->tmpDir/cases/out/$file");
				} else if ($this->endsWith($file, '.in', true)) {
					rename("$this->tmpDir/cases/$file", "$this->tmpDir/cases/in/$file");
				}
			}
			closedir($dh);

			// Handle statements
			$this->handleStatements($this->filesToUnzip);

			// Verify at least one statement was extracted.
			if (!is_dir("$this->tmpDir/statements")) {
				throw new InvalidParameterException('problemDeployerNoStatements');
			}

			// Handle cases
			$this->handleCases($this->tmpDir, $this->casesFiles);
		} catch (ApiException $e) {
			throw new ProblemDeploymentFailedException($e->getMessage(), $e);
		} catch (Exception $e) {
			$this->log->error("Deployment exception $e");
			throw new ProblemDeploymentFailedException('problemDeployerFailed', $e);
		}
	}

	/**
	 * Gets the maximum output file size. Returns -1 if there is a
	 * custom validator.
	 *
	 * @param string $alias
	 * @throws InvalidFilesystemOperationException
	 */
	public function getOutputLimit() {
		if ($this->hasValidator) {
			return -1;
		}

		$dirpath = "$this->tmpDir/cases/out";

		$output_limit = 10240;

		if ($handle = opendir($dirpath)) {
			while (false !== ($entry = readdir($handle))) {
				if (!$this->endsWith($entry, '.out', true)) continue;

				$output_limit = max($output_limit, filesize("$dirpath/$entry"));
			}
			closedir($handle);
		}

		return (int)(($output_limit + 4095) / 4096 + 1) * 4096;
	}

	/**
	 * Calculates if this problem should go into the slow queue.
	 * A slow problem takes 30s or more to judge.
	 *
	 * @param Request $r
	 * @throws InvalidFilesystemOperationException
	 */
	public function isSlow(Problems $problem) {
		$validator = 0;

		$dirpath = $this->tmpDir;

		if ($handle = opendir($dirpath)) {
			while (false !== ($entry = readdir($handle))) {
				if (stripos($entry, 'validator.') === 0) {
					$validator = 1;
					break;
				}
			}
			closedir($handle);
		}

		$dirpath .= '/cases/in';

		$input_count = 0;

		if ($handle = opendir($dirpath)) {
			while (false !== ($entry = readdir($handle))) {
				if (!$this->endsWith($entry, '.in', true)) continue;
				$input_count += 1;
			}
			closedir($handle);
		}

		$max_runtime = (int)(($problem->time_limit + 999) / 1000 + $validator) *
			$input_count;

		if ($max_runtime >= ProblemDeployer::MAX_RUNTIME_HARD_LIMIT) {
			throw new ProblemDeploymentFailedException('problemDeployerSlowRejected');
		}

		return $max_runtime >= ProblemDeployer::SLOW_QUEUE_THRESHOLD ? 1 : 0;
	}

	/**
	 *
	 * @param array $zipFilesArray
	 * @param ZipArchive $zip
	 * @return boolean
	 */
	private function checkProblemStatements(array $zipFilesArray, ZipArchive $zip) {
		$this->log->info("Checking problem statements...");

		// We need at least one statement
		$statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $zipFilesArray);

		if (count($statements) < 1) {
			throw new InvalidParameterException("problemDeployerNoStatements");
		}

		// Add statements to the files to be unzipped
		foreach ($statements as $file) {
			// Revisar que los statements no esten vacíos
			if (strlen($zip->getFromName($file, 1)) < 1) {
				throw new InvalidParameterException("problemDeployerEmptyStatement", NULL,
					array('file' => $file));
			}

			$this->log->info("Adding statements to the files to be unzipped: " . $file);
			$this->filesToUnzip[] = $file;
		}

		// Also extract any images in the statements directory.
		$images = preg_grep('/^statements\/.*\.(gif|jpg|jpeg|png)$/', $zipFilesArray);

		// Add images to the files to be unzipped.
		foreach ($images as $file) {
			$this->filesToUnzip[] = $file;
			$this->imageHashes[substr($file, strlen('statements/'))] = true;
			if (file_exists("$this->tmpDir/$file")) {
				$this->git("rm -f $this->tmpDir/$file", $this->tmpDir);
			}
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
		$this->log->info("Validating /cases");

		// Necesitamos tener al menos 1 caso
		$cases = 0;

		// Add all files in cases/ that end either in .in or .out
		for ($i = 0; $i < count($zipFilesArray); $i++) {
			$path = $zipFilesArray[$i];

			if (strpos($path, "cases/") != 0 || !$this->endsWith($path, ".in", true)) continue;
			// Look for the .out pair
			$outPath = substr($path, 0, strlen($path) - 3) . ".out";
			$idx = $zip->locateName($outPath, ZipArchive::FL_NOCASE);

			if ($idx !== FALSE) {
				$cases++;
				$this->casesFiles[] = $path;
				$this->filesToUnzip[] = $path;
				$this->filesToUnzip[] = $zipFilesArray[$idx];
			} else {
				throw new InvalidParameterException("problemDeployerOutMissing", NULL,
					array('file' => $path));
			}
		}

		if ($cases === 0) {
			throw new InvalidParameterException("problemDeployerNoCases");
		}

		$this->log->info($cases . " cases found.");

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
				throw new InvalidParameterException("problemDeployerMissingFromTestplan", NULL,
					array('file' => $testplan_array[1][$i]));
			}

			$this->filesToUnzip[] = $path;
			$this->casesFiles[] = $path;

			// Check .out file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.out';
			if ($zip->getFromName($path) === FALSE) {
				throw new InvalidParameterException("problemDeployerMissingFromTestplan", NULL,
					array('file' => $testplan_array[1][$i]));
			}

			$this->filesToUnzip[] = $path;
		}

		return true;
	}

	/**
	 * Entry point for zip validation
	 * Determines the type of problem we are deploying
	 *
	 * @return boolean
	 * @throws InvalidParameterException
	 */
	private function validateZip() {
		$this->log->info("Validating zip...");

		if (!array_key_exists("problem_contents", $_FILES)) {
			$this->log->error("\$_FILES global does not contain problem_contents.");
			throw new InvalidParameterException("parameterEmpty", "problem_contents");
		}

		if (isset($_FILES['problem_contents']) &&
				!FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
			$this->log->error("GetFileUploader()->IsUploadedFile() check failed for \$_FILES['problem_contents']['tmp_name'].");
			throw new InvalidParameterException("parameterEmpty", "problem_contents");
		}

		$this->filesToUnzip = array();
		$this->imageHashes = array();
		$this->casesFiles = array();

		$this->zipPath = $_FILES['problem_contents']['tmp_name'];

		$this->log->info("Opening $this->zipPath...");
		$zip = new ZipArchive();
		$resource = $zip->open($this->zipPath);

		$size = 0;
		if ($resource !== TRUE) {
		 	$this->log->error("Unable to open zip file: " . ZipHandler::ErrorMessage($resource));
			throw new InvalidParameterException("problemDeployerCorruptZip");
		}

		// Get list of files
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$this->log->info("Found inside zip: '" . $zip->getNameIndex($i) . "'");
			$zipFilesArray[] = $zip->getNameIndex($i);

			// Sum up the size
			$statI = $zip->statIndex($i);
			$size += $statI['size'];

			// If the file is THE validator for custom outputs...
			if (stripos($zip->getNameIndex($i), 'validator.') === 0) {
				$this->hasValidator = true;
				$this->filesToUnzip[] = $zip->getNameIndex($i);

				$this->log->info("Validator found: " . $zip->getNameIndex($i));
			}

			// Interactive problems.
			if (stripos($zip->getNameIndex($i), 'interactive/') === 0) {
				$this->filesToUnzip[] = $zip->getNameIndex($i);

				$this->isInteractive = true;
				$this->log->info("Interactive folder found: " . $zip->getNameIndex($i));
			}
		}

		if ($this->isInteractive && $size > ProblemDeployer::MAX_INTERACTIVE_ZIP_FILESIZE) {
			throw new InvalidParameterException("problemDeployerExceededZipSizeLimit", NULL,
				array("size" => $size, "max_size" => ProblemDeployer::MAX_INTERACTIVE_ZIP_FILESIZE));
		} else if ($size > ProblemDeployer::MAX_ZIP_FILESIZE) {
			throw new InvalidParameterException("problemDeployerExceededZipSizeLimit", NULL,
				array("size" => $size, "max_size" => ProblemDeployer::MAX_ZIP_FILESIZE));
		}

		try {
			// Look for testplan
			if (in_array("testplan", $zipFilesArray)) {

				$returnValue = $this->checkCasesWithTestplan($zip, $zipFilesArray);
				$this->log->info("testplan found, checkCasesWithTestPlan=" . $returnValue);
				$this->filesToUnzip[] = 'testplan';
			} else {
				$this->log->info("testplan not found");
				$this->checkCases($zip, $zipFilesArray);
			}

			// Log files to unzip
			$this->log->info("Files to unzip: ");
			foreach ($this->filesToUnzip as $file) {
				$this->log->info($file);
			}

			// Look for statements
			$returnValue = $this->checkProblemStatements($zipFilesArray, $zip);
			$this->log->info("checkProblemStatements=" . $returnValue . ".");
		} finally {
			// Close zip
			$this->log->info("closing zip");
			$zip->close();
		}

		return $returnValue;
	}


	/**
	 * Read already deployed statements from filesystem and apply transformations
	 * $lang.markdown => statements/$lang.html as well as encoding checks
	 *
	 * @param string $dirpath
	 * @param array $filesToUnzip
	 */
	private function handleStatements(array $filesToUnzip = null) {
		// Get a list of all available statements.
		// At this point, zip is validated and it has at least 1 statement. No need to check
		$statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $filesToUnzip);
		$this->log->info("Handling statements...");

		// Transform statements from markdown to HTML
		foreach ($statements as $statement) {
			// Get the path to the markdown unzipped file
			$markdown_filepath = "$this->tmpDir/$statement";
			$this->log->info("Reading file " . $markdown_filepath);

			// Read the contents of the original markdown file
			$this->current_markdown_file_contents = FileHandler::ReadFile($markdown_filepath);

			// Deploy statement raw (.markdown) and transformed (.html)
			$this->HTMLizeStatement($this->tmpDir, basename($statement));
		}
	}

	/**
	 * Given the $lang.markdown contents, deploys the .markdown file and creates the .html file
	 *
	 * @param string $problemBasePath
	 * @param string $statementFileName
	 * @param string $lang The 2-letter code for the language
	 */
	private function HTMLizeStatement($problemBasePath, $statementFileName) {
		$this->log->info("HTMLizing statement: " . $statementFileName);

		// Path used to deploy the raw problem statement (.markdown)
		$markdown_filepath = "$problemBasePath/statements/$statementFileName";

		// Get the language of this statement
		$lang = basename($statementFileName, ".markdown");

		// Fix for Windows Latin-1 statements:
		// For now, assume that if it is not UTF-8, then it is Windows Latin-1 and then convert
		if (!mb_check_encoding($this->current_markdown_file_contents, "UTF-8")) {
			$this->log->info("File is not UTF-8.");

			// Convert from ISO-8859-1 (Windows Latin1) to UTF-8
			$this->log->info("Converting encoding from ISO-8859-1 to UTF-8 (Windows Latin1 to UTF-8, fixing accents)");
			$this->current_markdown_file_contents = mb_convert_encoding($this->current_markdown_file_contents, "UTF-8", "ISO-8859-1");
		} else {
			$this->log->info("File is UTF-8. Nice :)");
		}

		// Transform markdown to HTML and sync img paths between Markdown and HTML
		$this->log->info("Transforming markdown to html");
		$this->currentLanguage = $lang;
		$html_file_contents = Markdown(
			$this->current_markdown_file_contents,
			array($this, 'imageMarkdownCallback'),
			array($this, 'translationCallback')
		);

		// Then save the changes to the markdown file
		$this->log->info("Saving markdown after Markdown-HTML img path sync: " . $markdown_filepath);
		FileHandler::CreateFile($markdown_filepath, $this->current_markdown_file_contents);

		// Save the HTML file in the path .../problem_alias/statements/lang.html
		$html_filepath = "$problemBasePath/statements/$lang.html";
		$this->log->info("Saving HTML statement in " . $html_filepath);
		FileHandler::CreateFile($html_filepath, $html_file_contents);
	}


	/**
	 * Deploys the given image when present in the statement contents.
	 * Also, replaces original markdown relative image URL with the absolute URL
	 * generated by this callback
	 *
	 * @param type $imagepath
	 * @return type
	 */
	public function imageMarkdownCallback($imagepath) {
		$replacement = null;

		if (preg_match('%^data:image/([^;]+)%', $imagepath, $matches) === 1) {
			$imagedata = file_get_contents($imagepath);
			$filename = sha1($imagedata) . '.' . $matches[1];
			$localDestination = IMAGES_PATH . $filename;
			$globalDestination = IMAGES_URL_PATH . $filename;

			file_put_contents("$this->tmpDir/statements/$filename", $imagedata);
			$this->log->info("Deploying image: to $localDestination");
			if (!file_exists($localDestination)) {
				file_put_contents($localDestination, $imagedata);
			}

			$replacement = $globalDestination;
		} else if (array_key_exists($imagepath, $this->imageHashes)) {
			if (is_bool($this->imageHashes[$imagepath])) {
				// copy the image to somewhere in IMAGES_PATH, get its SHA-1 sum,
				// and store it in the imageHashes array.

				$source = "$this->tmpDir/statements/$imagepath";
				$hash = sha1_file($source);
				$extension = substr($imagepath, strrpos($imagepath, "."));
				$hashedFilename = $hash . $extension;
				$copyDestination = IMAGES_PATH . $hashedFilename;

				if (!file_exists($copyDestination)) {
					$this->log->info("Deploying image: copying $source to $copyDestination");
					FileHandler::Copy($source, $copyDestination);
				}
				$this->imageHashes[$imagepath] = IMAGES_URL_PATH . $hashedFilename;
			}
			$replacement = $this->imageHashes[$imagepath];
		} else {
			// Also support absolute urls.
			return $imagepath;
		}

		// Replace path in markdown as well
		$this->current_markdown_file_contents =
			str_replace($imagepath, $replacement, $this->current_markdown_file_contents);

		return $replacement;
	}

	public function translationCallback($key) {
		if ($this->currentLanguage == 'en') {
			switch ($key) {
				case 'input': return 'Input';
				case 'output': return 'Output';
				case 'description': return 'Description';
			}
		} else {
			if ($this->currentLanguage != 'es') {
				$this->log->error("Unknown language: $lang");
			}
			switch ($key) {
				case 'input': return 'Entrada';
				case 'output': return 'Salida';
				case 'description': return 'Descripción';
			}
		}
		throw new Exception("Invalid translation key $key");
	}

	/**
	 * Handle unzipped cases
	 *
	 * @param string $dirpath
	 * @param array $casesFiles
	 * @throws InvalidFilesystemOperationException
	 */
	private function handleCases($dirpath, array $casesFiles) {
		$this->log->info("Handling cases...");

		// Aplying normalizr to cases
		$output = array();
		$normalizr_cmd = BIN_PATH . "/normalizr $dirpath/cases/in/* $dirpath/cases/out/* 2>&1";
		$this->log->info("Applying normalizr: " . $normalizr_cmd);
		$return_var = -1;
		exec($normalizr_cmd, $output, $return_var);

		// Log errors
		if ($return_var !== 0) {
			$this->log->warn("normalizr failed with error: " . $return_var);
		} else {
			$this->log->info("normalizr succeeded");
		}
		$this->log->info(implode("\n", $output));
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
}
