<?php

class Grader {
    private $log;

    public function __construct() {
        $this->log = Logger::getLogger('Grader');
    }

    /**
     * Initializes curl with JSON headers to call grader
     *
     * @return curl_session
     * @throws Exception
     */
    private function initGraderCall($url) {
        // Initialize CURL
        $curl = curl_init();

        if ($curl === false) {
            throw new Exception('curl_init failed: ' . curl_error($curl));
        }

        // Set URL
        curl_setopt($curl, CURLOPT_URL, $url);

        // Get response from curl_exec() in string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // Set certificate URL
        curl_setopt($curl, CURLOPT_SSLCERT, OMEGAUP_SSLCERT_URL);

        // Set certifiate to verify peer with
        curl_setopt($curl, CURLOPT_CAINFO, OMEGAUP_CACERT_URL);

        // Set curl HTTP header
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);

        return $curl;
    }

    /**
     * Closes curl session
     *
     * @param curl_session $curl
     */
    private function terminateGraderCall($curl) {
        // Close curl
        curl_close($curl);
    }

    /**
     * Error checking after curl_exec
     *
     * @param curl_session $curl
     * @param array $content
     * @throws Exception
     */
    private function executeCurl($curl) {
        // Execute call
        $content = curl_exec($curl);

        if ($content === false) {
            $message = 'curl_exec failed: ' . curl_error($curl) . ' ' . curl_errno($curl);
            $this->terminateGraderCall($curl);
            throw new Exception($message);
        }
        $this->terminateGraderCall($curl);

        $response_array = json_decode($content, true);
        if ($response_array === false) {
            throw new Exception('json_decode failed with: ' . json_last_error() . 'for : ' . $content);
        } elseif ($response_array['status'] !== 'ok') {
            throw new Exception('Grader did not return status OK: ' . $content);
        }

        return $response_array;
    }

    /**
     * Call /grade endpoint with run id as parameter
     *
     * @param int $runId
     * @throws Exception
     */
    public function Grade($runGuids, $rejudge, $debug) {
        return $this->multiCurlRequest(
            explode(',', OMEGAUP_GRADER_URL),
            [
                'id' => $runGuids,
                'rejudge' => !!$rejudge,
                'debug' => false, // TODO(lhchavez): Reenable with ACLs.
            ]
        );
    }

    /**
     * Call /reload-config endpoint
     *
     * @param array $request
     * @return string
     */
    public function reloadConfig($request) {
        $curl = $this->initGraderCall(OMEGAUP_GRADER_RELOAD_CONFIG_URL);
        // Execute call
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));

        return $this->executeCurl($curl);
    }

    /**
     * Returns the response of the /status entry point
     *
     * @return array json array
     */
    public function status() {
        $curl = $this->initGraderCall(OMEGAUP_GRADER_STATUS_URL);

        return $this->executeCurl($curl);
    }

    public function broadcast($contest_alias, $problem_alias, $message, $public, $username, $user_id = -1, $user_only = false) {
        return $this->multiCurlRequest(
            explode(',', OMEGAUP_GRADER_BROADCAST_URL),
            [
                'contest' => $contest_alias,
                'problem' => $problem_alias,
                'message' => $message,
                'public' => $public,
                'user' => $username,
                // TODO(lhchavez): Remove the backendv1-compat fields.
                'broadcast' => $public,
                'targetUser' => (int)$user_id,
                'userOnly' => $user_only,
            ]
        );
    }

    /**
     * Sends a request to multiple URLs. Only returns the data from the first
     * element in the list.
     */
    private function multiCurlRequest($urls, $postFields) {
        $result = null;
        $primary = true;
        foreach ($urls as $url) {
            $curl = $this->initGraderCall($url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));

            if (!$primary) {
                // For secondary requests, set a really short timeout.
                curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            }

            try {
                $r = $this->executeCurl($curl);
                if ($primary) {
                    $result = $r;
                }
            } catch (Exception $e) {
                if ($primary) {
                    $result = $e;
                } else {
                    // Only log non-primary requests: primary requests will be
                    // logged in some catch block upstream.
                    $this->log->error("Error sending request to $url: " . $e);
                }
            }
            $primary = false;
        }
        if (is_a($result, 'Exception')) {
            throw $result;
        }
        return $result;
    }
}
