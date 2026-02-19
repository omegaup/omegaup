<?php

/**
 * GET method rejection for mutating API endpoints.
 */
class ApiCallerGetRejectionTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST = [];
    }

    public function tearDown(): void {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
        parent::tearDown();
    }

    public function testGetToMutatingEndpointReturns405() {
        $_SERVER['REQUEST_URI'] = '/api/user/update';

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
    }

    public function testGetToReadOnlyEndpointStillAllowed() {
        $_SERVER['REQUEST_URI'] = '/api/time/get';

        $response = json_decode(
            \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
            true
        );

        $this->assertSame('ok', $response['status']);
        $this->assertArrayHasKey('time', $response);
    }

    public function testAllowlistedReadOnlyListAssociatedIdentitiesAllowsGet() {
        $_SERVER['REQUEST_URI'] = '/api/user/listAssociatedIdentities';

        $response = json_decode(
            \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
            true
        );

        $this->assertNotSame(
            405,
            $response['errorcode'] ?? null,
            'listAssociatedIdentities must allow GET'
        );
    }
}
