<?php

class Grader {
    private static $instance = null;

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
        return $this->curlRequest(OMEGAUP_GRADER_URL . '/grader/status/');
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
        return $this->curlRequestRaw(
            OMEGAUP_GRADER_URL . '/run/resource/',
            [
                'id' => $guid,
                'filename' => $filename,
            ],
            $passthru,
            $missingOk
        );
    }

    /**
     * Sends a request to the grader and returns the data as a JSON array.
     */
    private function curlRequest(
        string $url,
        array $postFields = null
    ) {
        $curl = curl_init();

        if ($curl === false) {
            throw new Exception('curl_init failed: ' . curl_error($curl));
        }

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_SSLCERT => OMEGAUP_SSLCERT_URL,
                CURLOPT_CAINFO => OMEGAUP_CACERT_URL,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
            ]
        );
        if (!is_null($postFields)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
        }
        try {
            // Execute call
            $content = curl_exec($curl);

            if ($content === false || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
                $message = 'curl_exec failed: ' . curl_error($curl) . ' ' .
                    curl_errno($curl) . ' HTTP ' .
                    curl_getinfo($curl, CURLINFO_HTTP_CODE);
                throw new Exception($message);
            }

            $responseArray = json_decode($content, true);
            if ($responseArray === false) {
                throw new Exception('json_decode failed with: ' . json_last_error() . 'for : ' . $content);
            } elseif ($responseArray['status'] !== 'ok') {
                throw new Exception('Grader did not return status OK: ' . $content);
            }

            return $responseArray;
        } finally {
            curl_close($curl);
        }
    }

    /**
     * Sends a request to the grader and returns the data as a raw string.
     */
    private function curlRequestRaw(
        string $url,
        array $postFields,
        bool $passthru,
        bool $missingOk
    ) {
        $curl = curl_init();

        if ($curl === false) {
            throw new Exception('curl_init failed: ' . curl_error($curl));
        }

        Logger::getLogger('Grader')->info("curl {$url}");

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => !$passthru,
                CURLOPT_SSLCERT => OMEGAUP_SSLCERT_URL,
                CURLOPT_CAINFO => OMEGAUP_CACERT_URL,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/octet-stream',
                    'Content-Type: application/json',
                ],
            ]
        );
        if (!is_null($postFields)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
        }
        try {
            // Execute call
            $response = curl_exec($curl);

            if ($response === false || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
                if ($missingOk) {
                    return null;
                }
                $message = 'curl_exec failed: ' . curl_error($curl) . ' ' . curl_errno($curl);
                throw new Exception($message);
            }

            return $response;
        } finally {
            curl_close($curl);
        }
    }
}
