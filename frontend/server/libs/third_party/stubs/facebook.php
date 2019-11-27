<?php

namespace Facebook {

class Facebook {
    /**
     * @param array{app_id: string, app_secret: string, default_graph_version: string} $config
     */
    function __construct(array $config) {}

    public function getRedirectLoginHelper() : Helpers\FacebookRedirectLoginHelper {}

    public function get(
        string $endpoint,
        ?Authentication\AccessToken $accessToken = null,
        ?string $eTag = null,
        ?string $graphVersion = null
    ) : FacebookResponse {}
}

class FacebookResponse {
    public function getGraphUser() : \Facebook\GraphNodes\GraphUser {}
}

}  // namespace Facebook

namespace Facebook\Authentication {

class AccessToken {
}

}  // namespace Facebook\Authentication

namespace Facebook\Exceptions {

class FacebookSDKException extends \Exception {}

class FacebookResponseException extends FacebookSDKException {}

}  // namespace Facebook\Exceptions

namespace Facebook\GraphNodes {

class GraphUser {
    public function getEmail() : ?string {}
    public function getName() : ?string {}
}

}  // namespace Facebook\GraphNodes

namespace Facebook\Helpers {

class FacebookRedirectLoginHelper {
    /**
     * @param string $redirectUrl
     * @param string[] $scope
     * @param string $separator
     */
    public function getLoginUrl(
        string $redirectUrl,
        array $scope = [],
        string $separator = '&'
    ) : string {}

    public function getAccessToken(
        ?string $redirectUrl = null
    ) : ?\Facebook\Authentication\AccessToken {}

    public function getError() : ?string {}

    public function getErrorDescription() : ?string {}
}

}  // namespace Facebook\Helpers
