<?php

class Grader {
    private static $instance = null;

    const REQUEST_MODE_JSON = 1;
    const REQUEST_MODE_RAW = 2;
    const REQUEST_MODE_PASSTHRU = 3;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new Grader();
        }
        return self::$instance;
    }

    public static function setInstanceForTesting($instance) {
        self::$instance = $instance;
    }

    /**
     * Call /grade endpoint with run id as parameter
     *
     * @param array $runGuids the array of runs to be grader.
     * @param bool  $rejudge  whether this is a rejudge.
     * @param bool  $debug    whether this is a debug-rejudge.
     *
     * @throws Exception
     */
    public function grade(array $runGuids, bool $rejudge, bool $debug) {
        if (OMEGAUP_GRADER_FAKE) {
            return;
        }
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . '/run/grade/',
            self::REQUEST_MODE_JSON,
            [
                'id' => $runGuids,
                'rejudge' => !!$rejudge,
                'debug' => false, // TODO(lhchavez): Reenable with ACLs.
            ]
        );
    }

    /**
     * Returns the response of the /status entry point
     *
     * @return array json array
     */
    public function status() {
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
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . '/grader/status/',
            self::REQUEST_MODE_JSON
        );
    }

    public function broadcast(
        /* string? */ $contestAlias,
        /* string? */ $problemsetId,
        /* string? */ $problemAlias,
        string $message,
        bool $public,
        /* string? */ $username,
        int $userId = -1,
        bool $userOnly = false
    ) {
        if (OMEGAUP_GRADER_FAKE) {
            return;
        }
        return $this->curlRequest(
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
        string $guid,
        string $filename,
        bool $passthru = false,
        bool $missingOk = false
    ) {
        if (OMEGAUP_GRADER_FAKE) {
            return null;
        }
        return $this->curlRequest(
            OMEGAUP_GRADER_URL . '/run/resource/',
            $passthru ? self::REQUEST_MODE_PASSTHRU : self::REQUEST_MODE_RAW,
            [
                'id' => $guid,
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
     * @param array  $postFields Optional key-value pair dictionary with POST
     *                           fields.
     * @param bool   $missingOk  Return null if the resource is not found.
     *
     * @return mixed The result of the request.
     */
    private function curlRequest(
        string $url,
        int $mode,
        array $postFields = null,
        bool $missingOk = false
    ) {
        $curl = false;

        try {
            $curl = curl_init();
            if ($curl === false) {
                throw new Exception('curl_init failed: ' . curl_error($curl));
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
            if (!is_null($postFields)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
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

            if ($response === false || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
                $message = 'curl_exec failed: ' . curl_error($curl) . ' ' .
                    curl_errno($curl) . ' HTTP ' .
                    curl_getinfo($curl, CURLINFO_HTTP_CODE);
                throw new Exception($message);
            }

            if ($mode == self::REQUEST_MODE_JSON) {
                $responseArray = json_decode($response, true);
                if ($responseArray === false) {
                    throw new Exception('json_decode failed with: ' . json_last_error() . 'for : ' . $response);
                } elseif ($responseArray['status'] !== 'ok') {
                    throw new Exception('Grader did not return status OK: ' . $response);
                }

                return $responseArray;
            } else {
                return $response;
            }
        } catch (Exception $e) {
            Logger::getLogger('Grader')->error("curl failed for {$url}: {$e}");
            throw $e;
        } finally {
            if (is_resource($curl)) {
                curl_close($curl);
            }
        }
    }
}
