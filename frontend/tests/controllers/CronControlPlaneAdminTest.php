<?php

/**
 * Tests for the cron control plane admin endpoints.
 */
class CronControlPlaneAdminTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        // The table survives the shared cleanup, so each test starts empty.
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'DELETE FROM `Cron_Run_Requests`;'
        );
    }

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
        $this->assertContains(\OmegaUp\CronJobName::UpdateRanks->value, $names);
        $this->assertContains(
            \OmegaUp\CronJobName::AssignBadges->value,
            $names
        );
        $this->assertContains(
            \OmegaUp\CronJobName::AggregateFeedback->value,
            $names
        );
    }

    public function testGetCronRunReturnsInsertedRun() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $run = new \OmegaUp\DAO\VO\CronRuns([
            'name' => \OmegaUp\CronJobName::UpdateRanks->value,
            'status' => \OmegaUp\CronRunStatus::Success->value,
            'started_at' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
        ]);
        \OmegaUp\DAO\CronRuns::create($run);

        $response = \OmegaUp\Controllers\Admin::apiGetCronRun(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_id' => $run->run_id,
        ]));

        $this->assertNotNull($response['run']);
        $this->assertSame(
            \OmegaUp\CronJobName::UpdateRanks->value,
            $response['run']['name']
        );
        $this->assertSame(
            \OmegaUp\CronRunStatus::Success->value,
            $response['run']['status']
        );
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
            'name' => \OmegaUp\CronJobName::UpdateRanks->value,
            'status' => \OmegaUp\CronRunStatus::Failure->value,
            'started_at' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
            'phases' => json_encode([
                [
                    'phase' => 'update_users_stats',
                    'status' => \OmegaUp\CronRunStatus::Success->value,
                    'duration' => 1.25,
                    'error_class' => null,
                ],
                [
                    'phase' => 'update_schools_stats',
                    'status' => \OmegaUp\CronRunStatus::Failure->value,
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
        $this->assertSame(
            \OmegaUp\CronRunStatus::Success->value,
            $phases[0]['status']
        );
        $this->assertSame(1.25, $phases[0]['duration']);
        $this->assertNull($phases[0]['error_class']);
        $this->assertSame(
            \OmegaUp\CronRunStatus::Failure->value,
            $phases[1]['status']
        );
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
            'name' => \OmegaUp\CronJobName::UpdateRanks->value,
            'status' => \OmegaUp\CronRunStatus::Success->value,
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
        $jobs = array_values(array_filter(
            $payload['jobs'],
            fn ($job) => $job['name'] === \OmegaUp\CronJobName::UpdateRanks->value
        ));
        $this->assertCount(1, $jobs);
        $this->assertSame('19 8 * * *', $jobs[0]['schedule']);

        // The database is shared, so match only the run this test created.
        $runs = array_values(array_filter(
            $payload['runs'],
            fn ($row) => $row['run_id'] === intval($run->run_id)
        ));
        $this->assertCount(1, $runs);
        $this->assertSame(
            \OmegaUp\CronJobName::UpdateRanks->value,
            $runs[0]['name']
        );
        $this->assertSame(
            \OmegaUp\CronRunStatus::Success->value,
            $runs[0]['status']
        );
        $this->assertSame(1.5, $runs[0]['duration_seconds']);
        $this->assertSame(7, $runs[0]['rows_affected']);
        $this->assertSame([], $runs[0]['phases']);
    }

    public function testRerunCronRequiresAdmin() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\CronJobName::UpdateRanks->value,
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

        \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\CronJobName::UpdateRanks->value,
        ]));

        $request = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::UpdateRanks->value
        );
        $this->assertNotNull($request);
        $this->assertSame(
            \OmegaUp\CronRunRequestStatus::Pending->value,
            $request->status
        );
        $this->assertSame($user->user_id, $request->requested_by);
    }

    public function testRerunCronRejectsAJobThatIsNotInTheRegistry() {
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
        $this->assertNull(
            \OmegaUp\DAO\CronRunRequests::getActiveByName('not_a_real_job.py')
        );
    }

    public function testRerunCronDoesNotQueueDuplicates() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\CronJobName::AssignBadges->value,
        ]));
        $first = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::AssignBadges->value
        );

        \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\CronJobName::AssignBadges->value,
        ]));
        $second = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::AssignBadges->value
        );

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame($first->request_id, $second->request_id);
    }

    public function testEveryJobNameTheApiAcceptsIsInTheRegistry() {
        // apiRerunCron validates against CronJobName and the dispatcher only
        // launches what Cron_Jobs lists, so the two have to agree.
        $registered = array_map(
            fn ($job) => $job->name,
            \OmegaUp\DAO\CronJobs::getAllOrdered()
        );

        foreach (\OmegaUp\CronJobName::cases() as $case) {
            $this->assertContains($case->value, $registered);
        }
    }
}
