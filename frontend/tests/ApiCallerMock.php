<?php

namespace OmegaUp\Test;

/**
 * Replaces some logic of ApiCaller to make it phpunit-safe
 */
class ApiCallerMock extends \OmegaUp\ApiCaller {
    /**
     * headers() is not phpunit-safe. This is a no-op for test
     *
     * @param array $response
     * @return void
     */
    public static function setHttpHeaders(array $response) {
        return;
    }
}
