<?php

namespace OmegaUp\Exceptions;

class ServiceUnavailableException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'serviceUnavailable',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 503 SERVICE UNAVAILABLE',
            503,
            $previous
        );
    }
}
