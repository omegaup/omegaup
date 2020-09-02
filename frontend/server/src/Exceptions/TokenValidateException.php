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
    public function __construct($message = 'tokenDecodeCorrupt', $claims = []) {
        parent::__construct($message, 400);
        $this->claims = $claims;
    }
}
