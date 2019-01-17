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
            OMEGAUP_GRADER_URL,
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
        return $this->curlRequest(OMEGAUP_GRADER_STATUS_URL);
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
            OMEGAUP_GRADER_BROADCAST_URL,
            [
                'contest' => $contestAlias,
                'problemset' => $problemsetId,
                'problem' => $problem_alias,
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

    /**
     * Sends a request to multiple URLs. Only returns the data from the first
     * element in the list.
     */
    private function curlRequest(string $url, array $postFields = null) {
        $curl = curl_init();

        if ($curl === false) {
            throw new Exception('curl_init failed: ' . curl_error($curl));
        }

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
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

            if ($content === false) {
                $message = 'curl_exec failed: ' . curl_error($curl) . ' ' . curl_errno($curl);
                throw new Exception($message);
            }

            $response_array = json_decode($content, true);
            if ($response_array === false) {
                throw new Exception('json_decode failed with: ' . json_last_error() . 'for : ' . $content);
            } elseif ($response_array['status'] !== 'ok') {
                throw new Exception('Grader did not return status OK: ' . $content);
            }

            return $response_array;
        } finally {
            curl_close($curl);
        }
    }
}
