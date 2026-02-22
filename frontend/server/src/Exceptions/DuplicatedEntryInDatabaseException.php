<?php

namespace OmegaUp\Exceptions;

class DuplicatedEntryInDatabaseException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(string $message, ?\Throwable $previous = null) {
        parent::__construct(
            $message,
            'HTTP/1.1 400 BAD REQUEST',
            400,
            $previous
        );
    }
}
