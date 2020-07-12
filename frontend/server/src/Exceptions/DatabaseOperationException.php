<?php

namespace OmegaUp\Exceptions;

class DatabaseOperationException extends \OmegaUp\Exceptions\ApiException {
    /** @var int */
    private $_errno;

    /**
     * @param string $message The error message.
     * @param bool $isDuplicate Whether this was raised from there being a duplicate entry.
     */
    public function __construct(string $message, int $errno) {
        parent::__construct($message, 'HTTP/1.1 400 Bad Request', 400);
        $this->_errno = $errno;
    }

    public function getErrorMessage(): string {
        return \OmegaUp\Translations::getInstance()->get(
            'generalError'
        ) ?: 'generalError';
    }

    public function isDuplicate(): bool {
        return $this->_errno == 1062;
    }
}
