<?php

namespace OmegaUp;

/**
 * Class to abstract interactions with omegaup-gitserver.
 */
class ProblemDeployer {
    const UPDATE_SETTINGS = 0;
    const UPDATE_CASES = 1;
    const UPDATE_STATEMENTS = 2;
    const CREATE = 3;
    const ZIP_MAX_SIZE = 100 * 1024 * 1024;  // 100 MiB
    /** @var \Monolog\Logger */
    private $log;

    /** @var string */
    private $alias;

    /** @var string */
    private $zipPath;

    /** @var null|string */
    public $privateTreeHash = null;

    /** @var null|string */
    public $publishedCommit = null;

    /** @var list<string> */
    private $updatedLanguages = [];

    /** @var bool */
    private $acceptsSubmissions = true;

    /** @var bool */
    private $updatePublished = true;

    /** The mapping of gitserver errors to translation strings. */
    const ERROR_MAPPING = [
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
        'no-es-statement' => 'problemDeployerEsNoStatement',
        'not-a-review' => 'problemDeployerNotAReview',
        'omegaup-update-problem-old-version' => 'problemDeployerOmegaupUpdateProblemOldVersion',
        'problem-bad-layout' => 'problemDeployerProblemBadLayout',
        'published-must-point-to-commit-in-master' => 'problemDeployerPublishedMustPointToCommitInMaster',
        'review-bad-layout' => 'problemDeployerReviewBadLayout',
        'slow-rejected' => 'problemDeployerSlowRejected',
        'tests-bad-layout' => 'problemDeployerTestsBadLayout',
        'too-many-objects-in-packfile' => 'problemDeployerTooManyObjectsInPackfile',
    ];

    public function __construct(
        string $alias,
        bool $acceptsSubmissions = true,
        bool $updatePublished = true
    ) {
        $this->log = \Monolog\Registry::omegaup()->withName('ProblemDeployer');
        $this->alias = $alias;

        if (
            isset($_FILES['problem_contents'])
            && isset($_FILES['problem_contents']['tmp_name'])
            && \OmegaUp\FileHandler::getFileUploader()->isUploadedFile(
                $_FILES['problem_contents']['tmp_name']
            )
        ) {
            /** @psalm-suppress MixedArrayAccess */
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
        \OmegaUp\DAO\VO\Identities $identity,
        int $operation,
        array $problemSettings
    ): void {
        $mergeStrategy = 'ours';

        switch ($operation) {
            case self::UPDATE_SETTINGS:
                $mergeStrategy = 'ours';
                break;
            case self::CREATE:
                $mergeStrategy = 'theirs';
                break;
            case self::UPDATE_CASES:
                $mergeStrategy = 'theirs';
                break;
            case self::UPDATE_STATEMENTS:
                $mergeStrategy = 'recursive-theirs';
                break;
        }
        $result = $this->execute(
            $this->zipPath,
            strval($identity->username),
            $message,
            $problemSettings,
            null,
            $mergeStrategy,
            $operation == self::CREATE,
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
     * @param array{status: string, error?: string, updated_refs?: array{name: string, from: string, to: string, from_tree: string, to_tree: string}[], updated_files: array{path: string, type: string}[]} $result the JSON from omegaup-gitserver.
     */
    private function processResult(array $result): void {
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
        if (!empty($result['updated_files'])) {
            foreach ($result['updated_files'] as $updatedFile) {
                if (
                    preg_match(
                        '%statements/([a-z]{2})\\.markdown%',
                        $updatedFile['path'],
                        $matches
                    ) === 1
                ) {
                    $this->updatedLanguages[] = $matches[1];
                }
                if (
                    preg_match(
                        '%solutions/([a-z]{2})\\.markdown%',
                        $updatedFile['path'],
                        $matches
                    ) === 1
                ) {
                    $this->updatedLanguages[] = $matches[1];
                }
            }
        }
    }

    /**
     * Generate all possible libinteractive templates.
     *
     * Calling this function is a no-op if the problem turns out to not be an
     * interactive problem.
     */
    public function generateLibinteractiveTemplates(?string $publishedCommit): void {
        if (is_null($publishedCommit)) {
            return;
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $this->alias,
            $publishedCommit
        );
        /** @var null|array{interactive?: array{module_name: string, language: string}, cases: array<string, mixed>} */
        $distribSettings = json_decode(
            $problemArtifacts->get('settings.distrib.json'),
            associative: true
        );
        if (empty($distribSettings['interactive'])) {
            // oops, this was not an interactive problem.
            return;
        }
        $tmpDir = \OmegaUp\FileHandler::tempDir(
            TEMPLATES_PATH,
            'ProblemDeployer',
            0755
        );
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
            /** @var mixed $data */
            foreach ($distribSettings['cases'] as $filename => $data) {
                file_put_contents(
                    "{$tmpDir}/examples/{$filename}.in",
                    $problemArtifacts->get("examples/{$filename}.in")
                );
            }
            $tmpTarget = "{$tmpDir}/target";
            @mkdir($tmpTarget, 0755, true);
            $args = ['/usr/bin/java', '-Xmx64M', '-jar',
                '/usr/share/java/libinteractive.jar', 'generate-all', $idlPath,
                '--package-directory', $tmpTarget, '--package-prefix',
                "{$this->alias}_", '--shift-time-for-zip'];
            $this->executeRaw($args, $tmpTarget);
            $target = TEMPLATES_PATH . "/{$this->alias}/{$publishedCommit}";
            @mkdir(dirname($target), 0755, true);
            rename($tmpTarget, $target);
        } catch (\Exception $e) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'problemDeployerLibinteractiveValidationError',
                $e->getMessage()
            );
        } finally {
            \OmegaUp\FileHandler::deleteDirRecursively($tmpDir);
        }
    }

    /**
     * Updates loose files.
     *
     * @param string $message
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param array<string, string> $blobUpdate
     *
     * @throws \OmegaUp\Exceptions\ProblemDeploymentFailedException
     */
    public function commitLooseFiles(
        string $message,
        \OmegaUp\DAO\VO\Identities $identity,
        array $blobUpdate
    ): void {
        $tmpfile = tmpfile();
        try {
            $zipPath = stream_get_meta_data($tmpfile)['uri'];
            $zipArchive = new \ZipArchive();
            /** @var true|int */
            $err = $zipArchive->open(
                $zipPath,
                \ZipArchive::OVERWRITE
            );
            if ($err !== true) {
                $error = new \OmegaUp\Exceptions\ProblemDeploymentFailedException(
                    'problemDeployerInternalError',
                    $err
                );
                $this->log->error("commit loose files failed: {$error}");
                throw $error;
            }
            foreach ($blobUpdate as $path => $contents) {
                $zipArchive->addFromString($path, $contents);
            }
            $zipArchive->close();

            $result = $this->execute(
                $zipPath,
                strval($identity->username),
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
     * @return list<string>
     */
    public function getUpdatedLanguages() {
        return $this->updatedLanguages;
    }

    /**
     * @param list<string> $args
     * @return array{output: null|string, retval: int}
     */
    private function executeRaw(array $args, string $cwd) {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $cmd = join(' ', array_map('escapeshellarg', $args));
        $pipes = [];
        $proc = proc_open(
            $cmd,
            $descriptorspec,
            $pipes,
            $cwd,
            ['LANG' => 'en_US.UTF-8']
        );

        if (!is_resource($proc)) {
            $errors = error_get_last();
            if (is_null($errors)) {
                $this->log->error("$cmd failed");
                throw new \RuntimeException("$cmd failed");
            } else {
                $this->log->error(
                    "$cmd failed: {$errors['type']} {$errors['message']}"
                );
                throw new \RuntimeException($errors['message']);
            }
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
            'output' => $output ?: null,
        ];
    }

    /**
     * Performs the operation by calling the omegaup-gitserver API.
     *
     * @param string $zipPath,
     * @param string $author,
     * @param string $commitMessage,
     * @param null|array $problemSettings,
     * @param null|array<string, string> $blobUpdate,
     * @param string $mergeStrategy,
     * @param bool $create,
     * @param bool $acceptsSubmissions,
     * @param bool $updatePublished
     *
     * @return array{status: string, error?: string, updated_refs?: array{name: string, from: string, to: string, from_tree: string, to_tree: string}[], updated_files: array{path: string, type: string}[]}
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
    ): array {
        $curl = curl_init();
        $zipFile = fopen($zipPath, 'r');
        /** @var int */
        $zipFileSize = fstat($zipFile)['size'];
        if ($zipFileSize > self::ZIP_MAX_SIZE) {
            $exception = new \OmegaUp\Exceptions\ProblemDeploymentFailedException(
                'problemDeployerExceededZipSizeLimit'
            );
            $exception->addCustomMessageToArray('size', strval(
                $zipFileSize / 1024 / 1024
            ));
            $exception->addCustomMessageToArray('max_size', strval(
                self::ZIP_MAX_SIZE / 1024 / 1024
            ));
            throw $exception;
        }
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
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/git-upload-zip?" . http_build_query(
                        $queryParams
                    ),
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'Content-Type: application/zip',
                        // Unsetting Expect:, since it kind of breaks the gitserver.
                        'Expect: ',
                        "Content-Length: $zipFileSize",
                        \OmegaUp\SecurityTools::getGitserverAuthorizationHeader(
                            $this->alias,
                            $author
                        ),
                    ],
                    CURLOPT_INFILE => $zipFile,
                    CURLOPT_INFILESIZE => $zipFileSize,
                    CURLOPT_POST => 1,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_TIMEOUT => 120,
                ]
            );
            $output = curl_exec($curl);
            /** @var int */
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $retval = ($output !== false && $statusCode == 200) ? 0 : 1;
            $result = [
                'retval' => $retval,
                'statusCode' => $statusCode,
                'output' => strval($output),
            ];
        } finally {
            curl_close($curl);
            fclose($zipFile);
        }

        if ($result['statusCode'] == 409) {
            $exception = new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'problemAliasExists'
            );
            $exception->addCustomMessageToArray('parameter', 'problem_alias');
            throw $exception;
        }
        if ($result['retval'] != 0) {
            $errorMessage = 'problemDeployerInternalError';
            $context = null;
            if (!empty($result['output'])) {
                /** @var null|array{error: string} */
                $output = json_decode($result['output'], associative: true);
                if (is_null($output)) {
                    $context = $result['output'];
                } else {
                    $tokens = explode(': ', $output['error'], 2);
                    if (array_key_exists($tokens[0], self::ERROR_MAPPING)) {
                        $errorMessage = self::ERROR_MAPPING[$tokens[0]];
                        if (count($tokens) == 2) {
                            $context = $tokens[1];
                        }
                    } else {
                        $context = $output['error'];
                    }
                }
            }
            /** @psalm-suppress TranslationStringNotALiteralString This is handled in TranslationStringChecker */
            $error = new \OmegaUp\Exceptions\ProblemDeploymentFailedException(
                $errorMessage,
                $context
            );
            $this->log->error(
                'update zip failed: ' . json_encode($result) . " {$error}"
            );
            throw $error;
        }

        /** @var array{status: string, error?: string, updated_refs?: array{name: string, from: string, to: string, from_tree: string, to_tree: string}[], updated_files: array{path: string, type: string}[]} */
        return json_decode($result['output'], associative: true);
    }

    /**
     * Updates the published branch.
     */
    public function updatePublished(
        string $oldOid,
        string $newOid,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        $curl = curl_init();

        $pktline = "{$oldOid} {$newOid} refs/heads/published\n";
        $pktline = sprintf('%04x%s', 4 + strlen($pktline), $pktline);

        $payload = "{$pktline}0000";  // flush.
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
                        \OmegaUp\SecurityTools::getGitserverAuthorizationHeader(
                            $this->alias,
                            strval($identity->username)
                        ),
                    ],
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_POST => 1,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_TIMEOUT => 120,
                ]
            );
            $output = curl_exec($curl);
            /** @var int */
            $retval = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($output === false || $retval != 200) {
                $error = new \OmegaUp\Exceptions\ProblemDeploymentFailedException(
                    'problemDeployerInternalError',
                    $retval
                );
                $this->log->error(
                    "update published failed: HTTP/{$retval}: {$output}: {$error}"
                );
                throw $error;
            }
        } finally {
            curl_close($curl);
        }
    }

    public function renameRepository(
        string $targetAlias
    ): void {
        $curl = curl_init();
        try {
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/rename-repository/{$targetAlias}",
                    CURLOPT_HTTPHEADER => [
                        // Unsetting Expect:, since it kind of breaks the gitserver.
                        'Expect: ',
                        \OmegaUp\SecurityTools::getGitserverAuthorizationHeader(
                            $this->alias,
                            'omegaup:system'
                        ),
                    ],
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_TIMEOUT => 10,
                ]
            );
            $output = curl_exec($curl);
            /** @var int */
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $retval = ($output !== false && $statusCode == 200) ? 0 : 1;
        } finally {
            curl_close($curl);
        }

        if ($statusCode == 409) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'problemAliasExists'
            );
        }
        if ($retval != 0) {
            $error = new \OmegaUp\Exceptions\ProblemDeploymentFailedException(
                'problemDeployerInternalError',
                context: null
            );
            $this->log->error(
                "rename problem failed: HTTP/{$statusCode}: {$error}"
            );
            throw $error;
        }
    }
}
