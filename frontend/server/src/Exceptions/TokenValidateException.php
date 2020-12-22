<?php

namespace OmegaUp\Exceptions;

class TokenValidateException extends \Exception {
    /**
     * @readonly
     * @var array<string, string>
     */
    public $claims;

    /**
     * @param string $message
     * @param array<string, string> $claims
     */
    public function __construct($message = 'token_corrupted', $claims = []) {
        parent::__construct($message);
        $this->claims = $claims;
    }
}
