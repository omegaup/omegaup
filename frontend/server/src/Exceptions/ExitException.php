<?php

namespace OmegaUp\Exceptions;

class ExitException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(string $message = '') {
        parent::__construct($message, 'HTTP/1.1 200 OK', 200);
    }
}
