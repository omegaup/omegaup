<?php

namespace OmegaUp;

class ScopedFacebook {
    /** @var \OmegaUp\ScopedSession */
    public $scopedSession;
    /** @var \Facebook\Facebook */
    public $facebook;

    public function __construct() {
        require_once 'libs/third_party/facebook-php-graph-sdk/src/Facebook/autoload.php';

        $this->scopedSession = new \OmegaUp\ScopedSession();
        $this->facebook = new \Facebook\Facebook([
            'app_id' => OMEGAUP_FB_APPID,
            'app_secret' => OMEGAUP_FB_SECRET,
            'default_graph_version' => 'v2.5',
        ]);
    }
}
