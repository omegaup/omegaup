<?php

class AdminTest extends \OmegaUp\Test\ControllerTestCase {
    public function testPlatformReportStatsRequiresAdmin() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiPlatformReportStats(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should not have allowed access to report');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }

    public function testPlatformReportStats() {
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiPlatformReportStats(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]));
        $this->assertNotEmpty($response['report']);
    }

    public function testMaintenanceModePersistsOutsideCache() {
        [
            'identity' => $supportIdentity,
        ] = \OmegaUp\Test\Factories\User::createSupportUser();
        $supportLogin = \OmegaUp\Test\ControllerTestCase::login(
            $supportIdentity
        );

        try {
            $response = \OmegaUp\Controllers\Admin::apiSetMaintenanceMode(
                new \OmegaUp\Request([
                    'auth_token' => $supportLogin->auth_token,
                    'enabled' => true,
                    'message_es' => '<b>Mantenimiento editado</b><br>Volvemos pronto.',
                    'message_en' => '<b>Edited maintenance</b><br>Back soon.',
                    'message_pt' => '<b>Manutencao editada</b><br>Voltamos em breve.',
                    'type' => 'warning',
                ])
            );

            $this->assertSame('ok', $response['status']);

            \OmegaUp\Cache::clearCacheForTesting();
            $status = \OmegaUp\Controllers\Admin::getMaintenanceModeStatus();

            $this->assertTrue($status['enabled']);
            $this->assertSame(
                '<b>Mantenimiento editado</b><br>Volvemos pronto.',
                $status['message_es']
            );
            $this->assertSame(
                '<b>Edited maintenance</b><br>Back soon.',
                $status['message_en']
            );
            $this->assertSame(
                '<b>Manutencao editada</b><br>Voltamos em breve.',
                $status['message_pt']
            );
            $this->assertSame('warning', $status['type']);

            $message = \OmegaUp\Controllers\Admin::getMaintenanceMessage('en');
            $this->assertNotNull($message);
            $this->assertSame(
                '<b>Edited maintenance</b><br>Back soon.',
                $message['message']
            );
            $this->assertSame('warning', $message['type']);
        } finally {
            \OmegaUp\Controllers\Admin::apiSetMaintenanceMode(
                new \OmegaUp\Request([
                    'auth_token' => $supportLogin->auth_token,
                    'enabled' => false,
                ])
            );
            \OmegaUp\Cache::clearCacheForTesting();
        }
    }
}
