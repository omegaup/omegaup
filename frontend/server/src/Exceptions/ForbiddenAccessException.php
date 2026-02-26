<?php

namespace OmegaUp\Exceptions;

class ForbiddenAccessException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'userNotAllowed',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'HTTP/1.1 403 FORBIDDEN', 403, $previous);
    }
}
