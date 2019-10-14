<?php

namespace OmegaUp;

/**
 * Helper class to handle LinkedIn REST-based authentication.
 */
class LinkedIn {
    /** @var string */
    private $_clientId;

    /** @var string */
    private $_secret;

    /** @var string */
    private $_redirectUrl;

    /** @var array{ct: string, rd?: string} **/
    private $_state;

    public function __construct(
        string $clientId,
        string $secret,
        string $redirectUrl,
        ?string $postLoginRedirectUrl
    ) {
        $this->_clientId = $clientId;
        $this->_secret = $secret;
        $this->_redirectUrl = $redirectUrl;
        $this->_state = [
            'ct' => \OmegaUp\SecurityTools::randomString(8) // CSRF Token
        ];
        if (!is_null($postLoginRedirectUrl)) {
            $this->_state['rd'] = $postLoginRedirectUrl;
        }
    }

    public function getLoginUrl(): string {
        $query_string = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUrl,
            'state' => json_encode($this->_state),
        ]);
        $_SESSION['li-state'] = $this->_state['ct'];
        return "https://www.linkedin.com/oauth/v2/authorization?$query_string";
    }

    public function getAuthToken(string $code, string $state): string {
        /** @var null|array{ct: string} */
        $stateArray = json_decode($state, true);
        if (
            !isset($_SESSION['li-state'])
            || empty($stateArray)
            || !isset($stateArray['ct'])
            || $_SESSION['li-state'] != $stateArray['ct']
        ) {
            throw new \OmegaUp\Exceptions\CSRFException('invalidCsrfToken');
        }

        // If we make it here, the CSRF token has been consumed
        unset($_SESSION['li-state']);

        $curl = new \OmegaUp\CurlSession(
            'https://www.linkedin.com/oauth/v2/accessToken'
        );
        $authArray = $curl->get([
            'grant_type' => 'authorization_code',
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUrl,
            'client_secret' =>  $this->_secret,
            'code' => $code,
        ]);
        if (empty($authArray['access_token'])) {
            throw new \Exception('Failed to get auth token');
        }
        return $authArray['access_token'];
    }

    /**
     * @param string $accessToken
     * @return array<string, string>
     */
    public function getProfileInfo(string $accessToken): array {
        $curl = new \OmegaUp\CurlSession(
            'https://api.linkedin.com/v1/people/~:(first-name,last-name,email-address)?format=json',
            ["Authorization: Bearer {$accessToken}"]
        );
        $profile = $curl->get();
        if (empty($profile['emailAddress'])) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'loginLinkedInEmptyEmailError'
            );
        }
        return $profile;
    }

    public function extractRedirect(string $state): ?string {
        /** @var null|array<string, string> */
        $stateArray = json_decode($state, true);
        if (is_null($stateArray) || !isset($stateArray['rd'])) {
            return null;
        }
        return $stateArray['rd'];
    }
}
