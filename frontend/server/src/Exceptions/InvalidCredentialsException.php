<?php

namespace OmegaUp\Exceptions;

class InvalidCredentialsException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(?\Exception $previous = null) {
        parent::__construct('usernameOrPassIsWrong', 'HTTP/1.1 401 UNAUTHORIZED', 401, $previous);
    }
}
