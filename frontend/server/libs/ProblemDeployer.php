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
    private $log;

    private $alias;
    private $tmpDir = null;
    private $zipPath = null;
    public $hasValidator = false;
    public $requiresRejudge = false;
    private $isInteractive = false;
    private $idlFile = null;
    private $created = false;
    private $committed = false;
    private $operation = null;
    private $updatedLanguages = [];
    private $acceptsSubmissions = true;

    public function __construct($alias, $operation, $acceptsSubmissions = true) {
        $this->log = Logger::getLogger('ProblemDeployer');
        $this->alias = $alias;

        $this->tmpDir = FileHandler::TempDir('/tmp', 'ProblemDeployer', 0755);
        $this->gitDir = PROBLEMS_GIT_PATH . DIRECTORY_SEPARATOR . $this->alias;
        $this->operation = $operation;
        $this->git = new Git($this->gitDir);

        $this->acceptsSubmissions = $acceptsSubmissions;

        if (!is_writable(PROBLEMS_GIT_PATH)) {
            $this->log->error('path is not writable:' . PROBLEMS_GIT_PATH);
            throw new ProblemDeploymentFailedException();
        }

        if ($this->operation == ProblemDeployer::CREATE) {
            // Atomically try to create the bare repository.
            if (!@mkdir($this->gitDir, 0755)) {
                throw new InvalidParameterException('aliasInUse');
            }
            $this->git->get(['init', '-q', '--bare', '.']);
            $this->created = true;
        }

        // Clone repository into tmp dir
        $this->git->get(
            ['clone', $this->gitDir, $this->tmpDir],
            '/tmp'
        );

        // Ensure .gitattributes flags all inputs/outputs as binaries so it does not
        // take several minutes diffing them to save a little space.
        if (!file_exists("$this->tmpDir/.gitattributes")) {
            FileHandler::CreateFile(
                "$this->tmpDir/.gitattributes",
                "cases/in/* -diff -delta -merge -text -crlf\n" .
                'cases/out/* -diff -delta -merge -text -crlf'
            );
        }

        if ($this->operation == ProblemDeployer::UPDATE_CASES) {
            $dh = opendir($this->tmpDir);
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..' || $file == '.git' ||
                    $file == 'statements' || $file == '.gitattributes') {
                    continue;
                }
                $this->git->get(['rm', '-rf', $file], $this->tmpDir);
            }
            closedir($dh);
        }
    }

    public function __destruct() {
        $this->cleanup();
    }

    private function execute($cmd, $cwd) {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $proc = proc_open($cmd, $descriptorspec, $pipes, $cwd, ['LANG' => 'en_US.UTF-8']);

        if (!is_resource($proc)) {
            $errors = error_get_last();
            $this->log->error("$cmd failed: {$errors['type']} {$errors['message']}");
            throw new Exception($errors['message']);
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $retval = proc_close($proc);
        if ($retval != 0) {
            $this->log->error("$cmd failed: $retval $output $err");
            throw new Exception($err);
        }

        return $output;
    }

    public function commit($message, $user) {
        $this->git->get(['add', '.'], $this->tmpDir);
        $this->requiresRejudge = false;
        $changedFiles = false;
        foreach (explode('\n', $this->git->get(['status', '-s', '--porcelain'], $this->tmpDir)) as $line) {
            if ($line == '') {
                // Happens when the input is empty.
                continue;
            }
            $changedFiles = true;
            $path = substr($line, 3);
            if (strpos($path, '.git') === 0 || strpos($path, 'statements/') === 0 ||
                strpos($path, 'examples/') === 0 ||
                strpos($path, 'interactive/examples/') === 0) {
                continue;
            }
            $this->requiresRejudge = true;
        }
        if (!$changedFiles) {
            // No changes detected. Return happily.
            $this->log->debug('No files changed.');
            return;
        } elseif ($this->requiresRejudge) {
            $this->log->debug('Files changed, rejudge required.');
        } else {
            $this->log->debug('Files changed.');
        }
        $this->git->get(
            ['config', 'user.email', "$user->username@omegaup"],
            $this->tmpDir
        );
        $this->git->get(['config', 'user.name', $user->username], $this->tmpDir);
        $this->git->get(['config', 'push.default', 'matching'], $this->tmpDir);
        $this->git->get(['commit', '-am', $message], $this->tmpDir);
        $this->git->get(['push', 'origin', 'master'], $this->tmpDir);

        // Copy the libinteractive templates to a publically accessible location.
        $publicDestination = TEMPLATES_PATH . "/$this->alias/";
        if (is_dir($publicDestination)) {
            FileHandler::DeleteDirRecursive($publicDestination);
        }

        if (is_dir("$this->tmpDir/interactive/generated")) {
            FileHandler::BackupDir(
                "$this->tmpDir/interactive/generated",
                $publicDestination
            );
        }
        $this->committed = true;
    }

    public function cleanup() {
        if ($this->tmpDir != null && is_dir($this->tmpDir)) {
            FileHandler::DeleteDirRecursive($this->tmpDir);
        }

        // Something went wrong and the target directory was not committed. Rollback.
        if ($this->created && !$this->committed) {
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
                $this->git->get(['rm', '-f', $markdownFile], $this->tmpDir);
            }
            if (file_exists($htmlFile)) {
                $this->git->get(['rm', '-f', $htmlFile], $this->tmpDir);
            }

            if (!is_dir("$this->tmpDir/statements")) {
                mkdir("$this->tmpDir/statements", 0755);
            }

            // Deploy statement
            FileHandler::CreateFile($markdownFile, $statement);
            $this->UTF8izeStatement($this->tmpDir, "$lang.markdown", $statement);
            $this->updatedLanguages[] = $lang;
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
                if (ProblemDeployer::endsWith($file, '.out', true)) {
                    rename("$this->tmpDir/cases/$file", "$this->tmpDir/cases/out/$file");
                } elseif (ProblemDeployer::endsWith($file, '.in', true)) {
                    rename("$this->tmpDir/cases/$file", "$this->tmpDir/cases/in/$file");
                }
            }
            closedir($dh);

            if ($this->isInteractive) {
                $target = "$this->tmpDir/interactive/generated/";
                if (!is_dir($target)) {
                    mkdir($target, 0755);
                }
                $this->handleInteractive("$this->tmpDir/$this->idlFile", $target);
            }

            // Handle statements
            $this->handleStatements($this->filesToUnzip);

            // Verify at least one statement was extracted.
            if (!is_dir("$this->tmpDir/statements")) {
                throw new InvalidParameterException('problemDeployerNoStatements');
            }

            // Handle cases
            $this->handleCases($this->tmpDir);
        } catch (ApiException $e) {
            throw $e;
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
                if (!ProblemDeployer::endsWith($entry, '.out', true)) {
                    continue;
                }

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

        if ($problem->overall_wall_time_limit <=
            ProblemDeployer::SLOW_QUEUE_THRESHOLD * 1000) {
            return 0;
        }

        $problemArtifacts = null;
        if (is_dir("{$this->tmpDir}/cases")) {
            $problemArtifacts = new WorkingDirProblemArtifacts($this->tmpDir);
        } else {
            $problemArtifacts = new ProblemArtifacts($problem->alias);
        }

        foreach ($problemArtifacts->lsTree('') as $entry) {
            if (stripos($entry['name'], 'validator.') === 0) {
                $validator = 1;
                break;
            } elseif (stripos($entry['name'], 'interactive') === 0) {
                $validator = 1;
                break;
            }
        }

        $input_count = 0;
        foreach ($problemArtifacts->lsTree('cases/in/') as $entry) {
            if (!ProblemDeployer::endsWith($entry['name'], '.in', true)) {
                continue;
            }
            $input_count += 1;
        }

        $max_ms_per_run = $problem->time_limit + $problem->extra_wall_time +
            $validator * $problem->validator_time_limit;
        $max_runtime = (int)(($max_ms_per_run + 999) / 1000) *
            $input_count;

        if ($problem->overall_wall_time_limit >= ProblemDeployer::MAX_RUNTIME_HARD_LIMIT * 1000
            && $max_runtime >= ProblemDeployer::MAX_RUNTIME_HARD_LIMIT) {
            throw new ProblemDeploymentFailedException('problemDeployerSlowRejected');
        }

        return $max_runtime >= ProblemDeployer::SLOW_QUEUE_THRESHOLD ? 1 : 0;
    }

    /**
     * Returns the list of updated langauge files.
     *
     * @return array The list of updated languages
     */
    public function getUpdatedLanguages() {
        return $this->updatedLanguages;
    }

    /**
     *
     * @param array $zipFilesArray
     * @param ZipArchive $zip
     * @return boolean
     */
    private function checkProblemStatements(array $zipFilesArray, ZipArchive $zip) {
        $this->log->info('Checking problem statements...');

        // We need at least one statement
        $statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $zipFilesArray);

        if (count($statements) < 1) {
            throw new InvalidParameterException('problemDeployerNoStatements');
        }
        if (!in_array('statements/es.markdown', $statements)) {
            throw new InvalidParameterException('spanishStatementMissing');
        }

        // Add statements to the files to be unzipped
        foreach ($statements as $file) {
            // Revisar que los statements no esten vacíos
            if (strlen($zip->getFromName($file, 1)) < 1) {
                throw new InvalidParameterException(
                    'problemDeployerEmptyStatement',
                    null,
                    ['file' => $file]
                );
            }

            $this->log->info('Adding statements to the files to be unzipped: ' . $file);
            $this->filesToUnzip[] = $file;
        }

        // Also extract any images in the statements directory.
        $images = preg_grep('/^statements\/.*\.(gif|jpg|jpeg|png)$/', $zipFilesArray);

        // Add images to the files to be unzipped.
        foreach ($images as $file) {
            $this->filesToUnzip[] = $file;
            if (file_exists("$this->tmpDir/$file")) {
                $this->git->get(['rm', '-f', $file], $this->tmpDir);
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
        $this->log->info('Validating /cases');

        // Count how many test cases were found
        $cases = 0;

        // Add all files in cases/ that end either in .in or .out
        for ($i = 0; $i < count($zipFilesArray); $i++) {
            $path = $zipFilesArray[$i];

            if (strpos($path, 'cases/') !== 0 ||
                !ProblemDeployer::endsWith($path, '.in', true)) {
                continue;
            }
            // Look for the .out pair
            if (strpos($path, 'cases/in/') === 0) {
                $outPath = 'cases/out/' . substr($path, strlen('cases/in/'));
                $outPath = substr($outPath, 0, strlen($outPath) - 3) . '.out';
            } else {
                $outPath = substr($path, 0, strlen($path) - 3) . '.out';
            }
            $idx = $zip->locateName($outPath, 0);

            if ($idx !== false) {
                $cases++;
                $this->filesToUnzip[] = $path;
                $this->filesToUnzip[] = $zipFilesArray[$idx];
            } else {
                throw new InvalidParameterException('problemDeployerOutMissing', $path);
            }
        }

        // if the problem accepts submissions it must have at least one test case
        if ($this->acceptsSubmissions && $cases === 0) {
            throw new InvalidParameterException('problemDeployerNoCases');
        }

        $this->log->info($cases . ' cases found.');

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
        $testplan = $zip->getFromName('testplan');
        $testplan_array = [];

        // LOL RegEx magic to get test case names from testplan
        preg_match_all('/^\\s*([^# \\t]+)\\s+([0-9.]+)\\s*$/m', $testplan, $testplan_array);

        if (count($testplan_array[1]) == 0) {
                throw new InvalidParameterException('problemDeployerTestplanEmpty', null);
        }

        for ($i = 0; $i < count($testplan_array[1]); $i++) {
            // Check .in file
            $path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.in';
            if ($zip->getFromName($path) === false) {
                $path = 'cases/in/' . $testplan_array[1][$i] . '.in';
                if ($zip->getFromName($path) === false) {
                    throw new InvalidParameterException(
                        'problemDeployerTestplanCaseMissing',
                        null,
                        ['file' => $testplan_array[1][$i]]
                    );
                }
            }

            $this->filesToUnzip[] = $path;

            // Check .out file
            $path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.out';
            if ($zip->getFromName($path) === false) {
                $path = 'cases/out/' . $testplan_array[1][$i] . '.out';
                if ($zip->getFromName($path) === false) {
                    throw new InvalidParameterException(
                        'problemDeployerTestplanCaseMissing',
                        null,
                        ['file' => $testplan_array[1][$i]]
                    );
                }
            }

            $this->filesToUnzip[] = $path;
        }

        // Reverse check: are all cases in the testplan?
        for ($i = 0; $i < count($zipFilesArray); $i++) {
            $path = $zipFilesArray[$i];
            if (strpos($path, 'cases/') !== 0 ||
                !ProblemDeployer::endsWith($path, '.in', true)) {
                continue;
            }
            if (strpos($path, 'cases/in/') === 0) {
                $caseName = substr($path, strlen('cases/in/'));
            } else {
                $caseName = substr($path, strlen('cases/'));
            }
            $caseName = substr($caseName, 0, strlen($caseName) - 3);
            if (!in_array($caseName, $testplan_array[1])) {
                throw new InvalidParameterException(
                    'problemDeployerMissingFromTestplan',
                    null,
                    ['file' => $caseName]
                );
            }
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
        $this->log->info('Validating zip...');

        if (!array_key_exists('problem_contents', $_FILES)) {
            $this->log->error('\$_FILES global does not contain problem_contents.');
            throw new InvalidParameterException('parameterEmpty', 'problem_contents');
        }

        if (isset($_FILES['problem_contents']) &&
                !FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
            $this->log->error("GetFileUploader()->IsUploadedFile() check failed for \$_FILES['problem_contents']['tmp_name'].");
            throw new InvalidParameterException('parameterEmpty', 'problem_contents');
        }

        $this->filesToUnzip = [];

        $this->zipPath = $_FILES['problem_contents']['tmp_name'];

        $this->log->info("Opening $this->zipPath...");
        $zip = new ZipArchive();
        $resource = $zip->open($this->zipPath);

        $size = 0;
        if ($resource !== true) {
            $this->log->error('Unable to open zip file: ' . ZipHandler::ErrorMessage($resource));
            throw new InvalidParameterException('problemDeployerCorruptZip');
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

                $this->log->info('Validator found: ' . $zip->getNameIndex($i));
            }

            // Interactive problems.
            if (stripos($zip->getNameIndex($i), 'interactive/') === 0) {
                $this->filesToUnzip[] = $zip->getNameIndex($i);

                $this->isInteractive = true;
                if (ProblemDeployer::endsWith($zip->getNameIndex($i), '.idl', true)) {
                    $this->log->info('.idl file found: ' . $zip->getNameIndex($i));
                    $this->idlFile = $zip->getNameIndex($i);
                }
            }

            // Example inputs.
            if (stripos($zip->getNameIndex($i), 'examples/') === 0 &&
                ProblemDeployer::endsWith($zip->getNameIndex($i), '.in', true)) {
                    $this->filesToUnzip[] = $zip->getNameIndex($i);
            }
        }

        if ($this->isInteractive) {
            if ($this->idlFile == null) {
                throw new InvalidParameterException('problemDeployerIdlMissing');
            } elseif (!in_array('interactive/examples/sample.in', $this->filesToUnzip)) {
                throw new InvalidParameterException('problemDeployerInteractiveSampleMissing');
            }
        }

        if ($this->isInteractive && $size > ProblemDeployer::MAX_INTERACTIVE_ZIP_FILESIZE) {
            throw new InvalidParameterException(
                'problemDeployerExceededZipSizeLimit',
                null,
                ['size' => $size, 'max_size' => ProblemDeployer::MAX_INTERACTIVE_ZIP_FILESIZE]
            );
        } elseif ($size > ProblemDeployer::MAX_ZIP_FILESIZE) {
            throw new InvalidParameterException(
                'problemDeployerExceededZipSizeLimit',
                null,
                ['size' => $size, 'max_size' => ProblemDeployer::MAX_ZIP_FILESIZE]
            );
        }

        try {
            // Look for testplan
            if (in_array('testplan', $zipFilesArray)) {
                $returnValue = $this->checkCasesWithTestplan($zip, $zipFilesArray);
                $this->log->info('testplan found, checkCasesWithTestPlan=' . $returnValue);
                $this->filesToUnzip[] = 'testplan';
            } else {
                $this->log->info('testplan not found');
                $this->checkCases($zip, $zipFilesArray);
            }

            // Log files to unzip
            $this->log->info('Files to unzip: ');
            foreach ($this->filesToUnzip as $file) {
                $this->log->info($file);
            }

            // Look for statements
            $returnValue = $this->checkProblemStatements($zipFilesArray, $zip);
            $this->log->info('checkProblemStatements=' . $returnValue . '.');
        } finally {
            // Close zip
            $this->log->info('closing zip');
            $zip->close();
        }

        return $returnValue;
    }

    /**
     * Validate libinteractive problems and generate all possible templates.
     *
     * @param string $idlPath
     */
    private function handleInteractive($idlPath, $target) {
        try {
            $cmd = '/usr/bin/java -Xmx64M -jar /usr/share/java/libinteractive.jar generate-all ' .
                escapeshellarg($idlPath) . ' --package-directory ' . escapeshellarg($target) .
                ' --package-prefix ' . escapeshellarg($this->alias . '_') . ' --shift-time-for-zip';
            return $this->execute($cmd, $target);
        } catch (Exception $e) {
            throw new InvalidParameterException(
                'problemDeployerLibinteractiveValidationError',
                $e->getMessage()
            );
        }
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
        $this->log->info('Handling statements...');

        // Transform statements from markdown to HTML
        foreach ($statements as $statement) {
            // Get the path to the markdown unzipped file
            $markdown_filepath = "$this->tmpDir/$statement";
            $this->log->info('Reading file ' . $markdown_filepath);

            // Read the contents of the original markdown file
            $markdownContents = FileHandler::ReadFile($markdown_filepath);

            // Deploy statement raw (.markdown) and transformed (.html)
            $this->UTF8izeStatement($this->tmpDir, basename($statement), $markdownContents);
            $this->updatedLanguages[] = basename($statement, '.markdown');
        }
    }

    /**
     * Given the $lang.markdown contents, deploys the .markdown file and creates the .html file
     *
     * @param string $problemBasePath
     * @param string $statementFileName
     * @param string $markdownContents The contents of the markdown file.
     */
    private function UTF8izeStatement($problemBasePath, $statementFileName, $markdownContents) {
        $this->log->info('HTMLizing statement: ' . $statementFileName);

        // Path used to deploy the raw problem statement (.markdown)
        $markdown_filepath = "$problemBasePath/statements/$statementFileName";

        // Get the language of this statement
        $lang = basename($statementFileName, '.markdown');

        // Fix for Windows Latin-1 statements:
        // For now, assume that if it is not UTF-8, then it is Windows Latin-1 and then convert
        if (mb_check_encoding($markdownContents, 'UTF-8')) {
            $this->log->info('File is UTF-8. Nice :)');
            return;
        }
        $this->log->info('File is not UTF-8.');

        // Convert from ISO-8859-1 (Windows Latin1) to UTF-8
        $this->log->info('Converting encoding from ISO-8859-1 to UTF-8 (Windows Latin1 to UTF-8, fixing accents)');
        $markdownContents = mb_convert_encoding($markdownContents, 'UTF-8', 'ISO-8859-1');
        FileHandler::CreateFile($markdown_filepath, $markdownContents);
    }

    /**
     * Handle unzipped cases
     *
     * @param string $dirpath
     * @throws InvalidFilesystemOperationException
     */
    private function handleCases($dirpath) {
        $this->log->info('Handling cases...');

        // Aplying normalizr to cases
        $output = [];
        $normalizr_cmd = BIN_PATH . "/normalizr $dirpath/cases/in/* $dirpath/cases/out/* 2>&1";
        $this->log->info('Applying normalizr: ' . $normalizr_cmd);
        $return_var = -1;
        exec($normalizr_cmd, $output, $return_var);

        // Log errors
        if ($return_var !== 0) {
            $this->log->warn('normalizr failed with error: ' . $return_var);
        } else {
            $this->log->info('normalizr succeeded');
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
    private static function endsWith($haystack, $needle, $case) {
        $expectedPosition = strlen($haystack) - strlen($needle);

        $ans = false;

        if ($case) {
            return strrpos($haystack, $needle, 0) === $expectedPosition;
        } else {
            return strripos($haystack, $needle, 0) === $expectedPosition;
        }
    }
}
