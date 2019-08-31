<?php

namespace OmegaUp;

class CurlSession {
    /** @var resource */
    private $_curl;

    /**
     * @param string $url
     * @param null|string[] $additionalHeaders
     */
    public function __construct(string $url, ?array $additionalHeaders = null) {
        $curl = curl_init();
        if ($curl === false) {
            throw new \Exception('curl_init failed');
        }
        $this->_curl = $curl;

        curl_setopt($this->_curl, CURLOPT_URL, $url);
        // Get response from curl_exec() in string
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);

        $headers = ['Accept: application/json'];
        if (!is_null($additionalHeaders)) {
            $headers = array_merge($headers, $additionalHeaders);
        }
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * @param null|array<string, string> $postFields
     * @return array<string, string>
     */
    public function get(?array $postFields = null) : array {
        if (!is_null($postFields)) {
            curl_setopt($this->_curl, CURLOPT_POSTFIELDS, http_build_query($postFields));
        }
        /** @var false|string */
        $response = curl_exec($this->_curl);
        if ($response === false) {
            $message = 'curl_exec failed: ' . curl_error($this->_curl) . ' ' . curl_errno($this->_curl);
            throw new \Exception($message);
        }

        /** @var null|array<string, string> */
        $jsonResponse = json_decode($response, true);
        if (is_null($jsonResponse)) {
            throw new \Exception('json_decode failed with: ' . json_last_error() . " for :{$response}");
        }

        return $jsonResponse;
    }

    public function __destruct() {
        curl_close($this->_curl);
    }
}
