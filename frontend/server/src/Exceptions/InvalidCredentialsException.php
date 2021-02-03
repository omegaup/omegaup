<?php

namespace OmegaUp\Exceptions;

class InvalidCredentialsException extends \OmegaUp\Exceptions\ApiException {
    public function __construct() {
        parent::__construct(
            'usernameOrPassIsWrong',
            'HTTP/1.1 401 UNAUTHORIZED',
            401
        );
    }
}
