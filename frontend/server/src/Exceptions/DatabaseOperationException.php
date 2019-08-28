<?php

namespace OmegaUp\Exceptions;

class DatabaseOperationException extends \OmegaUp\Exceptions\ApiException {
    /** @var string */
    private $_message;

    /** @var int */
    private $_errno;

    /**
     * @param string $message The error message.
     * @param bool $isDuplicate Whether this was raised from there being a duplicate entry.
     */
    public function __construct(string $message, int $errno) {
        parent::__construct('generalError', 'HTTP/1.1 400 Bad Request', 400);
        $this->_message = $message;
        $this->_errno = $errno;
    }

    public function __toString() : string {
        return "{$this->_message}: " . parent::__toString();
    }

    public function isDuplicate() : bool {
        return $this->_errno == 1062;
    }
}
