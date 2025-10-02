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
        return \OmegaUp\Translations::getInstance()->get('generalError');
    }

    public function isDuplicate(): bool {
        return $this->_errno == 1062;
    }

    public function isGoneAway(): bool {
        return $this->_errno == 2006;
    }

    public function isPacketsOutOfOrder(): bool {
        return $this->_errno == 2014; // CR_COMMANDS_OUT_OF_SYNC
    }

    public function isDeadlock(): bool {
        return in_array($this->_errno, [1205, 1213]); // Lock timeout, Deadlock
    }
}
