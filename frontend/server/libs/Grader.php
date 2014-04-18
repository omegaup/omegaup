<?php

class Grader {
	public function Grader() {
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

		if ($curl === FALSE) {
			throw new Exception("curl_init failed: " . curl_error($curl));
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
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));

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

		if ($content === FALSE) {
			$message = "curl_exec failed: " . curl_error($curl) . " " . curl_errno($curl);
			$this->terminateGraderCall($curl);
			throw new Exception($message);
		}
		$this->terminateGraderCall($curl);

		$response_array = json_decode($content, true);
		if ($response_array === FALSE) {
			throw new Exception("json_decode failed with: " . json_last_error() . "for : " . $content);
		} else if ($response_array["status"] !== "ok") {
			throw new Exception("Grader did not return status OK: " . $content);
		}

		return $response_array;
	}

	/**
	 * Call /grade endpoint with run id as paraemeter 
	 * 
	 * @param int $runId
	 * @throws Exception
	 */
	public function Grade($runId) {
		$curl = $this->initGraderCall(OMEGAUP_GRADER_URL);

		// Set curl Post data
		curl_setopt($curl, CURLOPT_POSTFIELDS, "{\"id\":$runId}");

		return $this->executeCurl($curl);
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

	public function broadcast($contest_alias, $message, $broadcast, $user_id = -1, $user_only = false) {
		$curl = $this->initGraderCall(OMEGAUP_GRADER_BROADCAST_URL);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
			'contest' => $contest_alias,
			'message' => $message,
			'broadcast' => $broadcast,
			'targetUser' => (int)$user_id,
			'userOnly' => $user_only
		)));
		return $this->executeCurl($curl);
	}
}
