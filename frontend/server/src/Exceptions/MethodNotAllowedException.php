<?php

namespace OmegaUp\Exceptions;

class MethodNotAllowedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'methodNotAllowed',
        ?\Exception $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 405 Method Not Allowed',
            405,
            $previous
        );
    }
}
