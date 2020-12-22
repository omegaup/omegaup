<?php

namespace OmegaUp\Exceptions;

class CaptchaVerificationFailedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct() {
        parent::__construct(
            'unableToVerifyCaptcha',
            'HTTP/1.1 500 INTERNAL SERVER ERROR',
            500
        );
    }
}
