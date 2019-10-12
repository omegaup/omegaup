<?php

namespace OmegaUp;

class Grader {
    /** @var null|\OmegaUp\Grader */
    private static $_instance = null;

    const REQUEST_MODE_JSON = 1;
    const REQUEST_MODE_RAW = 2;
    const REQUEST_MODE_PASSTHRU = 3;

    public static function getInstance() : \OmegaUp\Grader {
        if (is_null(self::$_instance)) {
            self::$_instance = new \OmegaUp\Grader();
        }
        return self::$_instance;
    }

    public static function setInstanceForTesting(?\OmegaUp\Grader $instance) : void {
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
    public function grade(\OmegaUp\DAO\VO\Runs $run, string $source) : void {
        if (OMEGAUP_GRADER_FAKE) {
            if (is_null($run->submission_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
            }
            $submission = \OmegaUp\DAO\Submissions::getByPK($run->submission_id);
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
     * @param array $runs  the array of runs to be graded.
     * @param bool  $debug whether this is a debug-rejudge.
     *
     * @throws \Exception
     */
    public function rejudge(array $runs, bool $debug) : void {
        if (OMEGAUP_GRADER_FAKE) {
            return;
        }
        $this->curlRequest(
            OMEGAUP_GRADER_URL . '/run/grade/',
            self::REQUEST_MODE_JSON,
            [
                'run_ids' => array_map(function (\OmegaUp\DAO\VO\Runs $r) {
                    return intval($r->run_id);
                }, $runs),
                'rejudge' => true,
                'debug' => false, // TODO(lhchavez): Reenable with ACLs.
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
    public function getSource(string $guid) : string {
        if (OMEGAUP_GRADER_FAKE) {
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
     * @return array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: array{name: string, id: int}[], run_queue_length: int, runner_queue_length: 0, runners: string[]}} json array
     */
    public function status() : array {
        if (OMEGAUP_GRADER_FAKE) {
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
        /** @var array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: array{name: string, id: int}[], run_queue_length: int, runner_queue_length: 0, runners: string[]}} */
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
    ) : void {
        if (OMEGAUP_GRADER_FAKE) {
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
    ) : ?string {
        if (OMEGAUP_GRADER_FAKE) {
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

    public function getGraderResourcePassthru(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false
    ) : ?bool {
        if (OMEGAUP_GRADER_FAKE) {
            return null;
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

    /**
     * Sends a request to the grader.
     *
     * @param string $url        The URL to request
     * @param int    $mode       How to return the result.
     * @param mixed  $postData   Optional POST data. Will convert key-value
     *                           pair dictionaries into JSON.
     * @param bool   $missingOk  Return null if the resource is not found.
     *
     * @return mixed The result of the request.
     */
    private function curlRequest(
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
                    new \RuntimeException('curl_init failed')
                );
            }

            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => ($mode != self::REQUEST_MODE_PASSTHRU),
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_SSLCERT => OMEGAUP_SSLCERT_URL,
                    CURLOPT_CAINFO => OMEGAUP_CACERT_URL,
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
            /** @var bool|string */
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
            \Logger::getLogger('Grader')->error("curl failed for {$url}", $e);
            throw $e;
        } finally {
            if (is_resource($curl)) {
                curl_close($curl);
            }
        }
    }
}
