<?php

/**
 * Tests for the cron control plane admin endpoints.
 */
class CronControlPlaneAdminTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetCronsRequiresAdmin() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should not have allowed access to non-admin');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testGetCronsReturnsSeededJobs() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $names = array_map(fn ($job) => $job['name'], $response['jobs']);
        $this->assertContains('update_ranks.py', $names);
        $this->assertContains('assign_badges.py', $names);
        $this->assertContains('aggregate_feedback.py', $names);
    }

    public function testGetCronRunReturnsInsertedRun() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $run = new \OmegaUp\DAO\VO\CronRuns([
            'name' => 'update_ranks.py',
            'status' => 'success',
            'started_at' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
        ]);
        \OmegaUp\DAO\CronRuns::create($run);

        $response = \OmegaUp\Controllers\Admin::apiGetCronRun(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_id' => $run->run_id,
        ]));

        $this->assertNotNull($response['run']);
        $this->assertSame('update_ranks.py', $response['run']['name']);
        $this->assertSame('success', $response['run']['status']);
    }

    public function testGetCronRunRequiresAdmin() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiGetCronRun(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_id' => 1,
            ]));
            $this->fail('Should not have allowed access to non-admin');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testGetCronRunDecodesPhases() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $run = new \OmegaUp\DAO\VO\CronRuns([
            'name' => 'update_ranks.py',
            'status' => 'failure',
            'started_at' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
            'phases' => json_encode([
                [
                    'phase' => 'update_users_stats',
                    'status' => 'success',
                    'duration' => 1.25,
                    'error_class' => null,
                ],
                [
                    'phase' => 'update_schools_stats',
                    'status' => 'failure',
                    'duration' => 0.5,
                    'error_class' => 'ValueError',
                ],
            ]),
        ]);
        \OmegaUp\DAO\CronRuns::create($run);

        $response = \OmegaUp\Controllers\Admin::apiGetCronRun(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_id' => $run->run_id,
        ]));

        $phases = $response['run']['phases'];
        $this->assertCount(2, $phases);
        $this->assertSame('update_users_stats', $phases[0]['phase']);
        $this->assertSame('success', $phases[0]['status']);
        $this->assertSame(1.25, $phases[0]['duration']);
        $this->assertNull($phases[0]['error_class']);
        $this->assertSame('failure', $phases[1]['status']);
        $this->assertSame('ValueError', $phases[1]['error_class']);
    }

    public function testGetCronRunReturnsNullForUnknownRun() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiGetCronRun(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_id' => 999999999,
        ]));

        $this->assertNull($response['run']);
    }

    public function testGetCronsForTypeScriptRequiresAdmin() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::getCronsForTypeScript(
                new \OmegaUp\Request(['auth_token' => $login->auth_token])
            );
            $this->fail('Should not have allowed access to non-admin');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testGetCronsForTypeScriptRequiresLogin() {
        try {
            \OmegaUp\Controllers\Admin::getCronsForTypeScript(
                new \OmegaUp\Request([])
            );
            $this->fail('Should not have allowed access to a logged out user');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    public function testGetCronsForTypeScriptReturnsTheJobsAndTheRuns() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $run = new \OmegaUp\DAO\VO\CronRuns([
            'name' => 'update_ranks.py',
            'status' => 'success',
            'started_at' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
            'duration_seconds' => 1.5,
            'rows_affected' => 7,
        ]);
        \OmegaUp\DAO\CronRuns::create($run);

        $response = \OmegaUp\Controllers\Admin::getCronsForTypeScript(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );

        $this->assertSame('admin_crons', $response['entrypoint']);
        $this->assertSame(
            'omegaupTitleAdminCrons',
            $response['templateProperties']['title']->message
        );

        $payload = $response['templateProperties']['payload'];
        $names = array_map(fn ($job) => $job['name'], $payload['jobs']);
        $this->assertContains('update_ranks.py', $names);

        // Other tests share the database, so find the run this one created
        // rather than assuming it is the only one.
        $runs = array_values(array_filter(
            $payload['runs'],
            fn ($row) => $row['run_id'] === intval($run->run_id)
        ));
        $this->assertCount(1, $runs);
        $this->assertSame('update_ranks.py', $runs[0]['name']);
        $this->assertSame('success', $runs[0]['status']);
        $this->assertSame(1.5, $runs[0]['duration_seconds']);
        $this->assertSame(7, $runs[0]['rows_affected']);
        $this->assertSame([], $runs[0]['phases']);
    }
}
