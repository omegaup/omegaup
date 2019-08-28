<?php

namespace OmegaUp\Exceptions;

class NotAllowedToSubmitException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(string $message = 'unableToSubmit', ?\Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 403 FORBIDDEN', 403, $previous);
    }
}
