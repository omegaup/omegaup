<?php

namespace OmegaUp\Exceptions;

class UnauthorizedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'loginRequired',
        ?\Exception $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 401 UNAUTHORIZED',
            401,
            $previous
        );
    }
}
