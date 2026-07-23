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

    public function testGetCronRunReturnsNullForUnknownRun() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiGetCronRun(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_id' => 999999999,
        ]));

        $this->assertNull($response['run']);
    }

    public function testRerunCronRequiresAdmin() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => 'update_ranks.py',
            ]));
            $this->fail('Should not have allowed access to non-admin');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testRerunCronQueuesPendingRequest() {
        [
            'identity' => $identity,
            'user' => $user,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => 'update_ranks.py',
        ]));
        $this->assertSame('ok', $response['status']);

        $request = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            'update_ranks.py'
        );
        $this->assertNotNull($request);
        $this->assertSame('pending', $request->status);
        $this->assertSame($user->user_id, $request->requested_by);
    }

    public function testRerunCronRejectsUnknownJob() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => 'not_a_real_job.py',
            ]));
            $this->fail('Should not have queued an unknown job');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    public function testRerunCronDoesNotQueueDuplicates() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => 'assign_badges.py',
        ]));
        $first = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            'assign_badges.py'
        );

        \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => 'assign_badges.py',
        ]));
        $second = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            'assign_badges.py'
        );

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame($first->request_id, $second->request_id);
    }
}
