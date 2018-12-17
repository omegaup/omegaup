<?php

/**
 * Unzip and deploy a problem
 *
 * @author joemmanuel
 */

class ProblemDeployer {
    const CREATE = 0;
    const UPDATE_CASES = 1;
    const UPDATE_STATEMENTS = 2;

    private $log;

    private $alias;
    private $zipPath = null;
    public $requiresRejudge = false;
    private $created = false;
    private $committed = false;
    private $operation = null;
    private $updatedLanguages = [];
    private $acceptsSubmissions = true;

    public function __construct($alias, $operation, $acceptsSubmissions = true) {
        $this->log = Logger::getLogger('ProblemDeployer');
        $this->alias = $alias;

        $this->gitDir = PROBLEMS_GIT_PATH . DIRECTORY_SEPARATOR . $this->alias;
        $this->operation = $operation;
        $this->zipPath = $_FILES['problem_contents']['tmp_name'];

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
            $updateStatements
        );

        $this->requiresRejudge = false;
        $this->created = ($this->operation == ProblemDeployer::CREATE);
        if (property_exists($result, 'updated_refs')) {
            $masterRef = null;
            foreach ($result->updated_refs as $ref) {
                if ($ref->name == 'refs/heads/private') {
                    $this->requiresRejudge = true;
                } elseif ($ref->name == 'refs/heads/master') {
                    $masterRef = $ref;
                }
            }
            if (!is_null($masterRef) && $masterRef->from != '0000000000000000000000000000000000000000') {
                $result = $this->executeRaw(
                    ['/usr/bin/git', 'diff', '--name-only', $masterRef->from, $masterRef->to],
                    $this->gitDir
                );
                foreach (explode('\n', $result['output']) as $filename) {
                    if (preg_match('%statements/([a-z]{2})\\.markdown%', $filename, $matches) !== 1) {
                        continue;
                    }
                    $this->updatedLanguages[] = $matches[1];
                }
            }
        }
        $this->committed = true;
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
            $updateStatements
        );

        $this->requiresRejudge = false;
        $this->committed = true;
    }

    /**
     * Returns the list of updated langauge files.
     *
     * @return array The list of updated languages
     */
    public function getUpdatedLanguages() {
        return $this->updatedLanguages;
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
        $quiet = false
    ) {
        $args = [
            '/usr/bin/omegaup-update-problem',
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

        return json_decode($result['output']);
    }
}
