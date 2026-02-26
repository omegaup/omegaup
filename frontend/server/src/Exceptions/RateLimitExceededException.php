<?php

namespace OmegaUp\Exceptions;

class RateLimitExceededException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'apiTokenRateLimitExceeded',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 429 Too Many Requests',
            429,
            $previous,
        );
    }
}
