<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/** @psalm-suppress MissingDependency we need to add PHPUnit */
class AdminTest extends \OmegaUp\Test\ControllerTestCase {
    public function testPlatformReportStatsRequiresAdmin() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiPlatformReportStats(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should not have allowed access to report');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }

    public function testPlatformReportStats() {
        [
            'user' => $admin,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiPlatformReportStats(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]));
        $this->assertNotEmpty($response['report']);
    }
}
