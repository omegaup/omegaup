<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * GET method rejection for mutating API endpoints.
 */
class ApiCallerGetRejectionTest extends \OmegaUp\Test\ControllerTestCase {
    private function cleanupRequestMethod(): void {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
    }

    public function testGetToMutatingEndpointReturns405() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/user/update';
        $_REQUEST = [];

        try {
            $response = json_decode(
                \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
                true
            );

            $this->assertSame('error', $response['status']);
            $this->assertSame(405, $response['errorcode']);
            $this->assertSame('methodNotAllowed', $response['errorname']);
            $this->assertSame(
                'HTTP/1.1 405 Method Not Allowed',
                $response['header']
            );
        } finally {
            $this->cleanupRequestMethod();
        }
    }

    public function testGetToReadOnlyEndpointStillAllowed() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/time/get';
        $_REQUEST = [];

        try {
            $response = json_decode(
                \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
                true
            );

            $this->assertSame('ok', $response['status']);
            $this->assertArrayHasKey('time', $response);
        } finally {
            $this->cleanupRequestMethod();
        }
    }

    public function testAllowlistedReadOnlyListAssociatedIdentitiesAllowsGet() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/user/listAssociatedIdentities';
        $_REQUEST = [];

        try {
            $response = json_decode(
                \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
                true
            );

            $this->assertNotSame(
                405,
                $response['errorcode'] ?? null,
                'listAssociatedIdentities must allow GET'
            );
        } finally {
            $this->cleanupRequestMethod();
        }
    }
}
