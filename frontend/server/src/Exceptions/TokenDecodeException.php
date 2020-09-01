<?php

namespace OmegaUp\Exceptions;

class TokenDecodeException extends \OmegaUp\Exceptions\ApiException {
    /**
     * @readonly
     * @var array<string, string>
     */
    public $claims;

    /**
     * @param string $message
     * @param array<string, string> $claims
     */
    public function __construct(
        array $claims = []
    ) {
        parent::__construct(
            'tokenDecodeFailed',
            'HTTP/1.1 400 BAD REQUEST',
            400
        );
        $this->claims = $claims;
    }
}
