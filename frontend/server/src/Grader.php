<?php

namespace OmegaUp;

/**
 * @psalm-type GraderStatus=array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: list<array{name: string, id: int}>, run_queue_length: int, runner_queue_length: int, runners: list<string>}}
 */
class Grader {
    /** @var null|\OmegaUp\Grader */
    private static $_instance = null;

    const REQUEST_MODE_JSON = 1;
    const REQUEST_MODE_RAW = 2;
    const REQUEST_MODE_PASSTHRU = 3;

    /**
     * This is needed to prevent Psalm from complaining every time this shows
     * up, since this can be set to true in tests.
     *
     * @var bool
     */
    private static $OMEGAUP_GRADER_FAKE = OMEGAUP_GRADER_FAKE;

    public static function getInstance(): \OmegaUp\Grader {
        if (is_null(self::$_instance)) {
            self::$_instance = new \OmegaUp\Grader();
        }
        return self::$_instance;
    }

    public static function setInstanceForTesting(?\OmegaUp\Grader $instance): void {
        self::$_instance = $instance;
    }

    /**
     * Call /run/new/ endpoint with run id as parameter.
     *
     * @param \OmegaUp\DAO\VO\Runs $run the run to be graded.
     * @param string $source the source of the submission.
     *
     * @throws \Exception
     */
    public function grade(\OmegaUp\DAO\VO\Runs $run, string $source): void {
        if (self::$OMEGAUP_GRADER_FAKE) {
            if (is_null($run->submission_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
            }
            $submission = \OmegaUp\DAO\Submissions::getByPK(
                $run->submission_id
            );
            if (is_null($submission)) {
                throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
            }
            file_put_contents("/tmp/{$submission->guid}", $source);
            return;
        }
        $this->curlRequest(
            OMEGAUP_GRADER_URL . "/run/new/{$run->run_id}/",
            self::REQUEST_MODE_RAW,
            $source
        );
    }

    /**
     * Call /run/grade/ endpoint in rejudge mode.
     *
     * @param list<\OmegaUp\DAO\VO\Runs> $runs the array of runs to be graded.
     * @param bool  $debug whether this is a debug-rejudge.
     *
     * @throws \Exception
     */
    public function rejudge(array $runs, bool $debug): void {
        if (self::$OMEGAUP_GRADER_FAKE) {
            return;
        }
        $this->curlRequest(
            OMEGAUP_GRADER_URL . '/run/grade/',
            self::REQUEST_MODE_JSON,
            [
                'run_ids' => array_map(
                    fn (\OmegaUp\DAO\VO\Runs $r) => intval($r->run_id),
                    $runs
                ),
                'rejudge' => true,
                'debug' => false, // TODO(lhchavez): Re-enable with ACLs.
            ]
        );
    }

    /**
     * Call /submission/source/ endpoint with submission guid as parameter
     *
     * @param string $guid the submission guid.
     *
     * @throws \Exception
     */
    public function getSource(string $guid): string {
        if (self::$OMEGAUP_GRADER_FAKE) {
            return file_get_contents("/tmp/{$guid}");
        }
        /** @var string */
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . "/submission/source/{$guid}/",
            self::REQUEST_MODE_RAW
        );
    }

    /**
     * Returns the response of the /status entry point
     *
     * @return GraderStatus
     */
    public function status(): array {
        if (self::$OMEGAUP_GRADER_FAKE) {
            return [
                'status' => 'ok',
                'broadcaster_sockets' => 0,
                'embedded_runner' => false,
                'queue' => [
                    'running' => [],
                    'run_queue_length' => 0,
                    'runner_queue_length' => 0,
                    'runners' => []
                ],
            ];
        }
        /** @var GraderStatus */
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . '/grader/status/',
            self::REQUEST_MODE_JSON
        );
    }

    public function broadcast(
        ?string $contestAlias,
        ?int $problemsetId,
        ?string $problemAlias,
        string $message,
        bool $public,
        ?string $username,
        int $userId = -1,
        bool $userOnly = false
    ): void {
        if (self::$OMEGAUP_GRADER_FAKE) {
            return;
        }
        $this->curlRequest(
            OMEGAUP_GRADER_URL . '/broadcast/',
            self::REQUEST_MODE_JSON,
            [
                'contest' => $contestAlias,
                'problemset' => $problemsetId,
                'problem' => $problemAlias,
                'message' => $message,
                'public' => $public,
                'user' => $username,
                // TODO(lhchavez): Remove the backendv1-compat fields.
                'broadcast' => $public,
                'targetUser' => $userId,
                'userOnly' => $userOnly,
            ]
        );
    }

    public function getGraderResource(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false
    ): ?string {
        if (self::$OMEGAUP_GRADER_FAKE) {
            return null;
        }
        /** @var null|string */
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . '/run/resource/',
            self::REQUEST_MODE_RAW,
            [
                'run_id' => intval($run->run_id),
                'filename' => $filename,
            ],
            $missingOk
        );
    }

    /**
     * @param list<string> $fileHeaders
     */
    public function getGraderResourcePassthru(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false,
        array $fileHeaders = []
    ): ?bool {
        if (self::$OMEGAUP_GRADER_FAKE) {
            return null;
        }
        foreach ($fileHeaders as $header) {
            header($header);
        }
        /** @var null|bool */
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . '/run/resource/',
            self::REQUEST_MODE_PASSTHRU,
            [
                'run_id' => intval($run->run_id),
                'filename' => $filename,
            ],
            $missingOk
        );
    }

    public function setGraderResourceForTesting(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        string $contents
    ): void {
        // Not implemented.
        throw new \BadMethodCallException();
    }

    /**
     * Sends a request to the grader.
     *
     * @param string $url        The URL to request
     * @param int    $mode       How to return the result.
     * @param string|array<string, mixed>|null $postData Optional POST data. Will convert key-value
     *                           pair dictionaries into JSON.
     * @param bool   $missingOk  Return null if the resource is not found.
     *
     * @return array{status: string}|null|string|bool The result of the request.
     */
    private function curlRequest(
        string $url,
        int $mode,
        $postData = null,
        bool $missingOk = false
    ) {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                return $this->curlRequestSingle(
                    $url,
                    $mode,
                    $postData,
                    $missingOk
                );
            } catch (\RuntimeException $e) {
                $retryCount++;

                // Check if this is a retryable error
                $errorMessage = $e->getMessage();
                $isRetryable = $this->isRetryableError($errorMessage);

                if (!$isRetryable || $retryCount >= $maxRetries) {
                    throw $e;
                }

                // Wait before retry (exponential backoff)
                $waitTime = max(1, intval(min(pow(2, $retryCount - 1), 5))); // Ensure positive int
                sleep($waitTime);

                self::$log->warning(
                    "Retrying grader request ($retryCount/$maxRetries) after error: {$errorMessage}"
                );
            }
        }

        throw new \RuntimeException('Maximum retry attempts exceeded');
    }

    /**
     * Check if a cURL error is retryable
     */
    private function isRetryableError(string $errorMessage): bool {
        $retryableErrors = [
            'SSL connection timeout',
            'HTTP/2 stream',
            'SSL routines::unexpected eof',
            'INTERNAL_ERROR',
            'Connection timed out',
            'Operation timed out',
        ];

        foreach ($retryableErrors as $retryableError) {
            if (strpos($errorMessage, $retryableError) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Single cURL request without retry logic
     *
     * @param string|array<string, mixed>|null $postData Optional POST data.
     * Will convert key-value pair dictionaries into JSON.
     * @return array{status: string}|null|string|bool
     */
    private function curlRequestSingle(
        string $url,
        int $mode,
        $postData = null,
        bool $missingOk = false
    ) {
        $curl = false;

        try {
            $curl = curl_init();
            if ($curl === false) {
                throw new \OmegaUp\Exceptions\InternalServerErrorException(
                    'generalError',
                    new \RuntimeException('curl_init failed')
                );
            }

            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => ($mode != self::REQUEST_MODE_PASSTHRU),
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_SSLKEY => '/etc/omegaup/frontend/key.pem',
                    CURLOPT_SSLCERT => '/etc/omegaup/frontend/certificate.pem',
                    CURLOPT_CAINFO => '/etc/omegaup/frontend/certificate.pem',
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_TCP_KEEPALIVE => 1,
                    CURLOPT_TCP_KEEPIDLE => 30,
                    CURLOPT_TCP_KEEPINTVL => 15,
                ]
            );
            if (!is_null($postData)) {
                curl_setopt(
                    $curl,
                    CURLOPT_POSTFIELDS,
                    is_string($postData) ? $postData : json_encode($postData)
                );
            }
            if ($mode == self::REQUEST_MODE_JSON) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ]);
            } else {
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Accept: application/octet-stream',
                    'Content-Type: application/json',
                ]);
            }

            // Execute call
            $response = curl_exec($curl);
            /** @var int */
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($response === false || $httpStatus != 200) {
                if ($httpStatus == 404 && $missingOk) {
                    return null;
                }
                $message = 'curl_exec failed: ' . curl_error($curl) . ' ' .
                    curl_errno($curl) . " HTTP {$httpStatus}";
                throw new \RuntimeException($message);
            }

            if ($mode == self::REQUEST_MODE_JSON && is_string($response)) {
                /** @var null|false|array{status: string} */
                $responseArray = json_decode($response, true);
                if (!is_array($responseArray)) {
                    throw new \RuntimeException(
                        'json_decode failed with: ' . json_last_error() . "for : {$response}"
                    );
                } elseif ($responseArray['status'] !== 'ok') {
                    throw new \RuntimeException(
                        "Grader did not return status OK: {$response}"
                    );
                }

                return $responseArray;
            } else {
                return $response;
            }
        } catch (\Exception $e) {
            \Monolog\Registry::omegaup()->withName('Grader')->error(
                'curl failed',
                ['url' => $url, 'exception' => $e],
            );
            throw $e;
        } finally {
            if (is_object($curl)) {
                curl_close($curl);
            }
        }
    }

    /** @var \Monolog\Logger */
    public static $log;
}

Grader::$log = \Monolog\Registry::omegaup()->withName('grader');
