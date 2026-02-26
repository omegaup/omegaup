<?php

namespace OmegaUp\Exceptions;

class NotFoundException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'resourceNotFound',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'HTTP/1.1 404 NOT FOUND', 404, $previous);
    }
}
