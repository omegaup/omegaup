<?php
class Grader {
	private $graderUrl;

	public function Grader($graderUrl = NULL) {
		if ($graderUrl === NULL) {
			$graderUrl = OMEGAUP_GRADER_URL;
		}

		$this->graderUrl = $graderUrl;
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

		// Don't check the common name (CN) attribute
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

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
	
	private function verifyResponse($curl, $content) {
		
		$errorMsg = NULL;
		if (!$content) {
			$errorMsg = "curl_exec failed: " . curl_error($curl) . " " . curl_errno($curl);
		} else if ($content !== '{"status":"ok"}') {
			$errorMsg = "Call to grader failed: '$content'";
		}
		
		if ($errorMsg !== NULL) {
			$this->terminateGraderCall($curl);
			throw new Exception($errorMsg);
		}
	}
	
	/**
	 * Call /grade endpoint with run id as paraemeter 
	 * 
	 * @param int $runId
	 * @throws Exception
	 */
	public function Grade($runId) {
		
		$curl = $this->initGraderCall($this->graderUrl);
		
		// Set curl Post data
		curl_setopt($curl, CURLOPT_POSTFIELDS, "{\"id\":$runId}");

		// Execute call
		$content = curl_exec($curl);
				
		$this->verifyResponse($curl, $content);			
		
		$this->terminateGraderCall($curl);
	}
	
	
	/**
	 * Call /reload-config endpoint
	 * 
	 * @return string
	 */
	public function reloadConfig() {
		
		$curl = $this->initGraderCall(OMEGAUP_GRADER_CONFIG_PATH);
		
		// Execute call
		$content = curl_exec($curl);
		
		$this->verifyResponse($content);
		
		$this->terminateGraderCall($curl);	
		
		return $content;
	}
}
