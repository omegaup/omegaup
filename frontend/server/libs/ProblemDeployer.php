<?php

/**
 * Unzip and deploy a problem
 *
 * @author joemmanuel
 */

class ProblemDeployer {
    const UPDATE_SETTINGS = 0;
    const UPDATE_CASES = 1;
    const UPDATE_STATEMENTS = 2;
    const CREATE = 3;

    private $log;

    private $alias;
    private $zipPath = null;
    public $requiresRejudge = false;
    private $created = false;
    private $committed = false;
    private $operation = null;
    private $updatedStatementLanguages = [];
    private $acceptsSubmissions = true;

    public function __construct($alias, $operation, $acceptsSubmissions = true) {
        $this->log = Logger::getLogger('ProblemDeployer');
        $this->alias = $alias;

        $this->gitDir = PROBLEMS_GIT_PATH . DIRECTORY_SEPARATOR . $this->alias;
        $this->operation = $operation;
        if (isset($_FILES['problem_contents'])
            && isset($_FILES['problem_contents']['tmp_name'])
        ) {
            $this->zipPath = $_FILES['problem_contents']['tmp_name'];
        }

        $this->acceptsSubmissions = $acceptsSubmissions;

        if (!is_writable(PROBLEMS_GIT_PATH)) {
            $this->log->error('path is not writable:' . PROBLEMS_GIT_PATH);
            throw new ProblemDeploymentFailedException();
        }
    }

    public function __destruct() {
        // Something went wrong and the target directory was not committed. Rollback.
        if ($this->created && !$this->committed) {
            FileHandler::DeleteDirRecursive($this->gitDir);
        }
    }

    public function commit($message, $user, $problemSettings = null) {
        $updateCases = false;
        $updateStatements = false;

        switch ($this->operation) {
            case ProblemDeployer::CREATE:
                $updateCases = true;
                $updateStatements = true;
                break;
            case ProblemDeployer::UPDATE_CASES:
                $updateCases = true;
                break;
            case ProblemDeployer::UPDATE_STATEMENTS:
                $updateStatements = true;
                break;
        }
        $result = $this->execute(
            $this->gitDir,
            $this->zipPath,
            $user->username,
            $message,
            $problemSettings,
            null,
            $updateCases,
            $updateStatements,
            $this->acceptsSubmissions
        );

        $this->requiresRejudge = false;
        $this->created = ($this->operation == ProblemDeployer::CREATE);
        if (!empty($result['updated_refs'])) {
            foreach ($result['updated_refs'] as $ref) {
                if ($ref['name'] == 'refs/heads/private') {
                    $this->requiresRejudge = true;
                }
            }
        }
        $updatedInteractiveFiles = false;
        $updatedExamples = false;
        if (!empty($result['updated_files'])) {
            foreach ($result['updated_files'] as $updated_file) {
                if (strpos($updated_file['path'], 'examples/') === 0) {
                    $updatedExamples = true;
                }
                if (preg_match('%statements/([a-z]{2})\\.markdown%', $updated_file['path'], $matches) === 1) {
                    $this->updatedStatementLanguages[] = $matches[1];
                }
                if (preg_match(
                    '%interactive/(Main\\.distrib\\.[a-z0-9]+|[a-z0-9_]+\\.idl)$%',
                    $updated_file['path']
                ) === 1) {
                    $updatedInteractiveFiles = true;
                }
            }
        }
        if ($updatedExamples || $updatedInteractiveFiles) {
            $this->generateLibinteractiveTemplates();
        }
        $this->committed = true;
    }

    /**
     * Generate all possible libinteractive templates.
     *
     * Calling this function is a no-op if the problem turns out to not be an
     * interactive problem.
     *
     * @return void
     */
    private function generateLibinteractiveTemplates() {
        $problemArtifacts = new ProblemArtifacts($this->alias);
        $distribSettings = json_decode(
            $problemArtifacts->get('settings.distrib.json'),
            JSON_OBJECT_AS_ARRAY
        );
        if (empty($distribSettings['interactive'])) {
            // oops, this was not an interactive problem.
            return;
        }
        $tmpDir = FileHandler::TempDir('/tmp', 'ProblemDeployer', 0755);
        try {
            $idlPath = "{$tmpDir}/{$distribSettings['interactive']['module_name']}.idl";
            file_put_contents(
                $idlPath,
                $problemArtifacts->get(
                    "interactive/{$distribSettings['interactive']['module_name']}.idl"
                )
            );
            file_put_contents(
                "{$tmpDir}/Main.{$distribSettings['interactive']['language']}",
                $problemArtifacts->get(
                    "interactive/Main.distrib.{$distribSettings['interactive']['language']}"
                )
            );
            mkdir("{$tmpDir}/examples");
            foreach ($distribSettings['cases'] as $filename => $data) {
                file_put_contents(
                    "{$tmpDir}/examples/{$filename}.in",
                    $problemArtifacts->get("examples/{$filename}.in")
                );
            }
            $target = TEMPLATES_PATH . "/{$this->alias}";
            mkdir($target);
            $args = ['/usr/bin/java', '-Xmx64M', '-jar',
                '/usr/share/java/libinteractive.jar', 'generate-all', $idlPath,
                '--package-directory', $target, '--package-prefix',
                "{$this->alias}_", '--shift-time-for-zip'];
            return $this->executeRaw($args, $target);
        } catch (Exception $e) {
            throw new InvalidParameterException(
                'problemDeployerLibinteractiveValidationError',
                $e->getMessage()
            );
        } finally {
            FileHandler::DeleteDirRecursive($tmpDir);
        }
    }

    /**
     * Updates statements.
     *
     * @param Request $r
     * @throws ProblemDeploymentFailedException
     */
    public function commitStatements($message, $user, $blobUpdate) {
        $updateCases = false;
        $updateStatements = true;
        $result = $this->execute(
            $this->gitDir,
            null,
            $user->username,
            $message,
            null,
            $blobUpdate,
            $updateCases,
            $updateStatements,
            $this->acceptsSubmissions
        );

        $this->requiresRejudge = false;
        $this->committed = true;
    }

    /**
     * Returns the list of languages of updated statement files.
     *
     * @return array The list of updated languages
     */
    public function getUpdatedStatementLanguages() {
        return $this->updatedStatementLanguages;
    }

    private function executeRaw($args, $cwd = null, $quiet = false) {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $cmd = join(' ', array_map('escapeshellarg', $args));
        $proc = proc_open(
            $cmd,
            $descriptorspec,
            $pipes,
            $cwd,
            ['LANG' => 'en_US.UTF-8']
        );

        if (!is_resource($proc)) {
            $errors = error_get_last();
            $this->log->error(
                "$cmd failed: {$errors['type']} {$errors['message']}"
            );
            throw new Exception($errors['message']);
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $retval = proc_close($proc);
        if (!$quiet) {
            if ($retval == 0) {
                $this->log->info("$cmd finished: $retval $err");
            } else {
                $this->log->error("$cmd failed: $retval $output $err");
            }
        }
        return [
            'retval' => $retval,
            'output' => $output,
        ];
    }

    /**
     * Performs the operation by calling the omegaup-update-problem binary.
     */
    private function execute(
        $repositoryPath,
        $zipPath,
        $author,
        $commitMessage,
        $problemSettings,
        $blobUpdate,
        $updateCases,
        $updateStatements,
        $acceptsSubmissions,
        $quiet = false
    ) {
        $args = [
            OMEGAUP_UPDATE_PROBLEM,
            "-repository-path=$repositoryPath",
            "-author=$author",
            "-commit-message=$commitMessage",
        ];
        if (!is_null($zipPath)) {
            $args[] = "-zip-path=$zipPath";
        }
        if (!is_null($blobUpdate)) {
            $args[] = '-blob-update=' . json_encode($blobUpdate);
        }
        if (!is_null($problemSettings)) {
            $args[] = '-problem-settings=' . json_encode($problemSettings);
        }
        if ($updateCases) {
            $args[] = '-update-cases=true';
        }
        if ($updateStatements) {
            $args[] = '-update-statements=true';
        }
        if (!$acceptsSubmissions) {
            $args[] = '-accepts-submissions=false';
        }
        $result = $this->executeRaw($args, null /* cwd */, $quiet);

        if ($result['retval'] != 0) {
            $errorMessage = 'problemDeployerInternalError';
            $context = null;
            if (!empty($result['output'])) {
                $output = json_decode($result['output']);
                $errorMapping = [
                    'change-missing-settings-json' => 'problemDeployerChangeMissingSettingsJson',
                    'config-bad-layout' => 'problemDeployerConfigBadLayout',
                    'config-invalid-publishing-mode' => 'problemDeployerConfigInvalidPublishingMode',
                    'config-repository-not-absolute-url' => 'problemDeployerConfigRepositoryNotAbsoluteUrl',
                    'config-subdirectory-missing-target' => 'problemDeployerConfigSubdirectoryMissingTarget',
                    'interactive-bad-layout' => 'problemDeployerInteractiveBadLayout',
                    'internal-error' => 'problemDeployerInternalError',
                    'internal-git-error' => 'problemDeployerInternalGitError',
                    'invalid-zip-filename' => 'problemDeployerInvalidZipFilename',
                    'json-parse-error' => 'problemDeployerJsonParseError',
                    'mismatched-input-file' => 'problemDeployerMismatchedInputFile',
                    'no-statements' => 'problemDeployerNoStatements',
                    'not-a-review' => 'problemDeployerNotAReview',
                    'omegaup-update-problem-old-version' => 'problemDeployerOmegaupUpdateProblemOldVersion',
                    'problem-bad-layout' => 'problemDeployerProblemBadLayout',
                    'published-must-point-to-commit-in-master' => 'problemDeployerPublishedMustPointToCommitInMaster',
                    'review-bad-layout' => 'problemDeployerReviewBadLayout',
                    'slow-rejected' => 'problemDeployerSlowRejected',
                    'too-many-objects-in-packfile' => 'problemDeployerTooManyObjectsInPackfile',
                ];
                $tokens = explode(': ', $output->error, 2);
                if (array_key_exists($tokens[0], $errorMapping)) {
                    $errorMessage = $errorMapping[$tokens[0]];
                    if (count($tokens) == 2) {
                        $context = $tokens[1];
                    }
                } else {
                    $context = $output->error;
                }
            }
            throw new ProblemDeploymentFailedException($errorMessage, $context);
        }

        return json_decode($result['output'], JSON_OBJECT_AS_ARRAY);
    }
}
