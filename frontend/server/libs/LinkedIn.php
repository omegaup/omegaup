<?php

class CurlSession {
    public function __construct($url, $additional_headers = null) {
        $this->curl = curl_init();
        if ($this->curl === false) {
            throw new Exception('curl_init failed: ' . curl_error($curl));
        }

        curl_setopt($this->curl, CURLOPT_URL, $url);
        // Get response from curl_exec() in string
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);

        $headers = ['Accept: application/json'];
        if (!is_null($additional_headers)) {
            $headers = array_merge($headers, $additional_headers);
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
    }

    public function get($post_fields = null) {
        if (!is_null($post_fields)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($post_fields));
        }
        $response = curl_exec($this->curl);
        if ($response === false) {
            $message = 'curl_exec failed: ' . curl_error($curl) . ' ' . curl_errno($curl);
            throw new Exception($message);
        }

        curl_close($this->curl);
        $this->curl = null;

        $json_response = json_decode($response, true);
        if ($json_response === false) {
            throw new Exception('json_decode failed with: ' . json_last_error() . 'for : ' . $response);
        }

        return $json_response;
    }

    public function __destruct() {
        if (!is_null($this->curl)) {
            curl_close($this->curl);
        }
    }
}

/**
 * Helper class to handle LinkedIn REST-based authentication.
 */
class LinkedIn {
    public function __construct($client_id, $secret, $redirect_url, $post_login_redirect) {
        $this->client_id = $client_id;
        $this->secret = $secret;
        $this->redirect_url = $redirect_url;
        $this->state = [
            'ct' => SecurityTools::randomString(8) // CSRF Token
        ];
        if (!is_null($post_login_redirect)) {
            $this->state['rd'] = $post_login_redirect;
        }
    }

    public function getLoginUrl() {
        $query_string = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_url,
            'state' => json_encode($this->state)
        ]);
        $_SESSION['li-state'] = $this->state['ct'];
        return "https://www.linkedin.com/oauth/v2/authorization?$query_string";
    }

    public function getAuthToken($code, $state) {
        $state_array = json_decode($state, true);
        if (!isset($_SESSION['li-state']) ||
            !isset($state_array['ct']) ||
            $_SESSION['li-state'] != $state_array['ct']) {
            throw new Exception('Invalid CSRF token');
        }

        // If we make it here, the CSRF token has been consumed
        unset($_SESSION['li-state']);

        $curl = new CurlSession('https://www.linkedin.com/oauth/v2/accessToken');
        $auth_array = $curl->get([
            'grant_type' => 'authorization_code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_url,
            'client_secret' =>  $this->secret,
            'code' => $_GET['code'],
        ]);
        if (empty($auth_array['access_token'])) {
            throw new Exception('Failed to get auth token');
        }
        return $auth_array['access_token'];
    }

    public function getProfileInfo($access_token) {
        $curl = new CurlSession(
            'https://api.linkedin.com/v1/people/~:(first-name,last-name,email-address)?format=json',
            ['Authorization: Bearer ' . $access_token]
        );

        $profile = $curl->get();
        if (empty($profile['emailAddress'])) {
            throw new Exception('e-mail not provided');
        }
        return $profile;
    }

    public function maybeResetRedirect($state) {
        $state_array = json_decode($state, true);
        if (isset($state_array['rd'])) {
            // Reset the original redirect.
            $_GET['redirect'] = $state_array['rd'];
        }
    }
}
