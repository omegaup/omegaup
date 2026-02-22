<?php

namespace OmegaUp\Exceptions;

class InternalServerErrorException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'generalError',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 500 INTERNAL SERVER ERROR',
            500,
            $previous
        );
    }
}
