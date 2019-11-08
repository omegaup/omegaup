<?php

namespace OmegaUp\Exceptions;

class UnimplementedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(?\Exception $previous = null) {
        parent::__construct(
            'wordsUnimplemented',
            'HTTP/1.1 405 Method Not Allowed',
            405,
            $previous
        );
    }
}
