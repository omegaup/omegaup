<?php

namespace OmegaUp\Exceptions;

class EmailVerificationSendException extends \OmegaUp\Exceptions\ApiException {
    public function __construct() {
        parent::__construct(
            'errorWhileSendingMail',
            'HTTP/1.1 500 INTERNAL SERVER ERROR',
            500
        );
    }
}
