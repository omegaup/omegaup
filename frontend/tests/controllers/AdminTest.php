<?php

/** @psalm-suppress MissingDependency we need to add PHPUnit */
class AdminTest extends OmegaupTestCase {
    public function testPlatformReportStatsRequiresAdmin() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = OmegaupTestCase::login($user);

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
        ['user' => $admin, 'identity' => $identity] = UserFactory::createAdminUser();
        $adminLogin = OmegaupTestCase::login($admin);

        \OmegaUp\Controllers\Admin::apiPlatformReportStats(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]));
    }
}
