<?php

/**
 * Tests for the system settings admin endpoints in the Admin controller.
 */
class AdminSystemSettingsTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        \OmegaUp\DAO\SystemSettings::invalidateCache(
            'ephemeral_grader_enabled'
        );
    }

    public function testGetSystemSettingsRequiresAdmin() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiGetSystemSettings(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Non-admin should not read system settings');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testGetSystemSettingsReturnsDefault() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiGetSystemSettings(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertSame('ok', $response['status']);
        $this->assertTrue($response['settings']['ephemeralGraderEnabled']);
    }

    public function testUpdateSystemSettingsRequiresAdmin() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiUpdateSystemSettings(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'ephemeral_grader_enabled' => false,
            ]));
            $this->fail('Non-admin should not update system settings');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testUpdateSystemSettingsTogglesValue() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        \OmegaUp\Controllers\Admin::apiUpdateSystemSettings(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'ephemeral_grader_enabled' => false,
        ]));
        $response = \OmegaUp\Controllers\Admin::apiGetSystemSettings(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertFalse($response['settings']['ephemeralGraderEnabled']);

        \OmegaUp\Controllers\Admin::apiUpdateSystemSettings(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'ephemeral_grader_enabled' => true,
        ]));
        $response = \OmegaUp\Controllers\Admin::apiGetSystemSettings(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertTrue($response['settings']['ephemeralGraderEnabled']);
    }

    public function testUpdateSystemSettingsWithoutParamIsNoOp() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiUpdateSystemSettings(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertSame('ok', $response['status']);
    }
}
