<?php

require_once 'libs/FileHandler.php';
require_once 'libs/ProblemArtifacts.php';

/**
 * Class to abstract interactions with omegaup-gitserver.
 */
class ProblemDeployer {
    const UPDATE_SETTINGS = 0;
    const UPDATE_CASES = 1;
    const UPDATE_STATEMENTS = 2;
    const CREATE = 3;

    private $log;

    private $alias;
    private $zipPath = null;
    public $privateTreeHash = null;
    public $publishedCommit = null;
    private $updatedLanguages = [];
    private $acceptsSubmissions = true;
    private $updatePublished = true;

    public function __construct(
        string $alias,
        bool $acceptsSubmissions = true,
        bool $updatePublished = true
    ) {
        $this->log = Logger::getLogger('ProblemDeployer');
        $this->alias = $alias;

        if (isset($_FILES['problem_contents'])
            && FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])
        ) {
            $this->zipPath = $_FILES['problem_contents']['tmp_name'];
        } else {
            $this->zipPath = __DIR__ . '/empty.zip';
        }

        $this->acceptsSubmissions = $acceptsSubmissions;
        $this->updatePublished = $updatePublished;
    }

    public function __destruct() {
    }

    public function commit(
        string $message,
        Users $user,
        int $operation,
        array $problemSettings
    ) : void {
        $mergeStrategy = 'ours';

        switch ($operation) {
            case ProblemDeployer::UPDATE_SETTINGS:
                $mergeStrategy = 'ours';
                break;
            case ProblemDeployer::CREATE:
                $mergeStrategy = 'theirs';
                break;
            case ProblemDeployer::UPDATE_CASES:
                $mergeStrategy = 'theirs';
                break;
            case ProblemDeployer::UPDATE_STATEMENTS:
                $mergeStrategy = 'recursive-theirs';
                break;
        }
        $result = $this->execute(
            $this->zipPath,
            $user->username,
            $message,
            $problemSettings,
            null,
            $mergeStrategy,
            $operation == ProblemDeployer::CREATE,
            $this->acceptsSubmissions,
            $this->updatePublished
        );
        $this->processResult($result);
    }

    /**
     * Process the result of the git operation.
     *
     * This sets the privateTreeHash and publishedCommit fields. It also sets
     * the list of updated statement languages, as well as updating the
     * libinteractive template files if needed.
     *
     * @param array $result the JSON from omegaup-gitserver.
     *
     * @return void
     */
    private function processResult($result) {
        if (!empty($result['updated_refs'])) {
            foreach ($result['updated_refs'] as $ref) {
                if ($ref['name'] == 'refs/heads/private') {
                    $this->privateTreeHash = $ref['to_tree'];
                }
                if ($ref['name'] == 'refs/heads/published') {
                    $this->publishedCommit = $ref['to'];
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
                if (preg_match(
                    '%statements/([a-z]{2})\\.markdown%',
                    $updated_file['path'],
                    $matches
                ) === 1) {
                    $this->updatedLanguages[] = $matches[1];
                }
                if (preg_match(
                    '%solutions/([a-z]{2})\\.markdown%',
                    $updated_file['path'],
                    $matches
                ) === 1) {
                    $this->updatedLanguages[] = $matches[1];
                }
                if (preg_match(
                    '%interactive/(Main\\.distrib\\.[a-z0-9]+|[a-z0-9_]+\\.idl)$%',
                    $updated_file['path']
                ) === 1) {
                    $updatedInteractiveFiles = true;
                }
            }
        }
        $this->generateLibinteractiveTemplates($this->publishedCommit);
    }

    /**
     * Generate all possible libinteractive templates.
     *
     * Calling this function is a no-op if the problem turns out to not be an
     * interactive problem.
     *
     * @return void
     */
    public function generateLibinteractiveTemplates(?string $publishedCommit) : void {
        if (is_null($publishedCommit)) {
            return;
        }
        $problemArtifacts = new ProblemArtifacts($this->alias, $publishedCommit);
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
            @mkdir("{$tmpDir}/examples");
            foreach ($distribSettings['cases'] as $filename => $data) {
                file_put_contents(
                    "{$tmpDir}/examples/{$filename}.in",
                    $problemArtifacts->get("examples/{$filename}.in")
                );
            }
            $target = TEMPLATES_PATH . "/{$this->alias}/{$publishedCommit}";
            @mkdir($target, 0755, true);
            $args = ['/usr/bin/java', '-Xmx64M', '-jar',
                '/usr/share/java/libinteractive.jar', 'generate-all', $idlPath,
                '--package-directory', $target, '--package-prefix',
                "{$this->alias}_", '--shift-time-for-zip'];
            $this->executeRaw($args, $target);
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
     * Updates loose files.
     *
     * @param Request $r
     * @throws ProblemDeploymentFailedException
     */
    public function commitLooseFiles($message, $user, $blobUpdate) {
        $tmpfile = tmpfile();
        try {
            $zipPath = stream_get_meta_data($tmpfile)['uri'];
            $zipArchive = new ZipArchive();
            $err = $zipArchive->open(
                $zipPath,
                ZipArchive::OVERWRITE
            );
            if ($err !== true) {
                throw new ProblemDeploymentFailedException(
                    'problemDeployerInternalError',
                    $err
                );
            }
            foreach ($blobUpdate as $path => $contents) {
                $zipArchive->addFromString($path, $contents);
            }
            $zipArchive->close();

            $result = $this->execute(
                $zipPath,
                $user->username,
                $message,
                null,
                $blobUpdate,
                'recursive-theirs',
                false,
                $this->acceptsSubmissions,
                $this->updatePublished
            );
            $this->processResult($result);
        } finally {
            fclose($tmpfile);
        }
    }

    /**
     * Returns the list of languages of updated statement or solution files.
     *
     * @param string The filetype
     * @return array The list of updated languages
     */
    public function getUpdatedLanguages() {
        return $this->updatedLanguages;
    }

    private function executeRaw(array $args, string $cwd) : array {
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
        if ($retval == 0) {
            $this->log->info("$cmd finished: $retval $err");
        } else {
            $this->log->error("$cmd failed: $retval $output $err");
        }
        return [
            'retval' => $retval,
            'output' => $output,
        ];
    }

    /**
     * Performs the operation by calling the omegaup-gitserver API.
     */
    private function execute(
        string $zipPath,
        string $author,
        string $commitMessage,
        $problemSettings,
        $blobUpdate,
        string $mergeStrategy,
        bool $create,
        bool $acceptsSubmissions,
        bool $updatePublished
    ) {
        $curl = curl_init();
        $zipFile = fopen($zipPath, 'r');
        $zipFileSize = fstat($zipFile)['size'];
        try {
            $queryParams = [
                'message' => $commitMessage,
                'acceptsSubmissions' => $acceptsSubmissions ? 'true' : 'false',
                'updatePublished' => $updatePublished ? 'true' : 'false',
                'mergeStrategy' => $mergeStrategy,
            ];
            if ($create) {
                $queryParams['create'] = 'true';
            }
            if (!is_null($problemSettings)) {
                $queryParams['settings'] = json_encode($problemSettings);
            }
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/git-upload-zip?" . http_build_query($queryParams),
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'Content-Type: application/zip',
                        // Unsetting Expect:, since it kind of breaks the gitserver.
                        'Expect: ',
                        "Content-Length: $zipFileSize",
                        SecurityTools::getGitserverAuthorizationHeader($this->alias, $author),
                    ],
                    CURLOPT_INFILE => $zipFile,
                    CURLOPT_INFILESIZE => $zipFileSize,
                    CURLOPT_POST => 1,
                    CURLOPT_RETURNTRANSFER => 1,
                ]
            );
            $output = curl_exec($curl);
            $retval = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $retval = ($output !== false && $retval == 200) ? 0 : 1;
            $result = [
                'retval' => $retval,
                'output' => (string)$output,
            ];
        } finally {
            curl_close($curl);
            fclose($zipFile);
        }

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
                    'tests-bad-layout' => 'problemDeployerTestsBadLayout',
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
            $error = new ProblemDeploymentFailedException($errorMessage, $context);
            $this->log->error(
                'update zip failed: ' . json_encode($result) . ' ' .$error
            );
            throw $error;
        }

        return json_decode($result['output'], JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Updates the published branch.
     */
    public function updatePublished(
        string $oldOid,
        string $newOid,
        Users $user
    ) {
        $curl = curl_init();

        $pktline = "${oldOid} ${newOid} refs/heads/published\n";
        $pktline = sprintf('%04x%s', 4 + strlen($pktline), $pktline);

        $payload = "${pktline}0000";  // flush.
        $payload .= "\x50\x41\x43\x4B";  // PACK
        $payload .= "\x00\x00\x00\x02";  // packfile version (2)
        $payload .= "\x00\x00\x00\x00";  // number of objects (0)
        $payload .= "\x02\x9D\x08\x82\x3B\xD8\xA8\xEA\xB5\x10\xAD\x6A\xC7\x5C\x82\x3C\xFD\x3E\xD3\x1E";  // hash of the packfile.
        try {
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/git-receive-pack",
                    CURLOPT_HTTPHEADER => [
                        SecurityTools::getGitserverAuthorizationHeader($this->alias, $user->username),
                    ],
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_POST => 1,
                    CURLOPT_RETURNTRANSFER => 1,
                ]
            );
            $output = curl_exec($curl);
            $retval = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($output === false || $retval != 200) {
                throw new ProblemDeploymentFailedException(
                    'problemDeployerInternalError',
                    $retval
                );
            }
        } finally {
            curl_close($curl);
        }
    }
}
