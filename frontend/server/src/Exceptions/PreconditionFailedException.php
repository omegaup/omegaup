<?php

namespace OmegaUp\Exceptions;

class PreconditionFailedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'userNotAllowed',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 412 PRECONDITION FAILED',
            412,
            $previous
        );
    }
}
