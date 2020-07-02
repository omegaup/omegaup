<?php

namespace OmegaUp\Exceptions;

class ExitException extends \OmegaUp\Exceptions\ApiException {
    public function __construct() {
        parent::__construct('', 'HTTP/1.1 200 OK', 200);
    }
}
