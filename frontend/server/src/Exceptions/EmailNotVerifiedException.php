<?php

namespace OmegaUp\Exceptions;

class EmailNotVerifiedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(?\Exception $previous = null) {
        parent::__construct(
            'emailNotVerified',
            'HTTP/1.1 403 FORBIDDEN',
            403,
            $previous
        );
    }
}
