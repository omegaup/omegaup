<?php

namespace OmegaUp\Exceptions;

class CSRFException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(
        string $message = 'csrfException',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 400 BAD REQUEST',
            400,
            $previous
        );
    }
}
