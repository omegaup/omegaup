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

    public function testGetCronsForTypeScriptReturnsTheOpenHealthFindings() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
        $now = \OmegaUp\Time::get();
        \OmegaUp\DAO\ProblemHealthChecks::create(
            new \OmegaUp\DAO\VO\ProblemHealthChecks([
                'problem_id' => $problem->problem_id,
                'check_type' => \OmegaUp\ProblemHealthCheckType::NoLanguages->value,
                'severity' => \OmegaUp\ProblemHealthSeverity::Error->value,
                'detail' => 'the problem has no enabled language',
                'first_detected_at' => new \OmegaUp\Timestamp($now),
                'last_seen_at' => new \OmegaUp\Timestamp($now),
            ])
        );

        $response = \OmegaUp\Controllers\Admin::getCronsForTypeScript(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );

        $findings = array_values(array_filter(
            $response['templateProperties']['payload']['problemHealthFindings'],
            fn ($finding) => $finding['problem_id'] === intval(
                $problem->problem_id
            )
        ));
        $this->assertCount(1, $findings);
        $this->assertSame($problem->alias, $findings[0]['alias']);
        $this->assertSame(
            \OmegaUp\ProblemHealthCheckType::NoLanguages->value,
            $findings[0]['check_type']
        );
        $this->assertSame(
            \OmegaUp\ProblemHealthSeverity::Error->value,
            $findings[0]['severity']
        );
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

        $response = \OmegaUp\Controllers\Admin::apiRerunCron(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\CronJobName::UpdateRanks->value,
            ])
        );

        $this->assertTrue($response['queued']);
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

    public function testRerunCronRequiresAJobName() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should not have queued a request without a job');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }
    }

    public function testRerunCronRejectsADisabledJob() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $name = \OmegaUp\CronJobName::UpdateRanks->value;
        $job = \OmegaUp\DAO\CronJobs::getByName($name);
        $job->enabled = false;
        \OmegaUp\DAO\CronJobs::update($job);

        try {
            \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ]));
            $this->fail('Should not have queued a disabled job');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            $this->assertSame('cronControlPlaneJobDisabled', $e->getMessage());
        }
        $this->assertNull(
            \OmegaUp\DAO\CronRunRequests::getActiveByName($name)
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

        $response = \OmegaUp\Controllers\Admin::apiRerunCron(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\CronJobName::AssignBadges->value,
            ])
        );
        $second = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::AssignBadges->value
        );

        $this->assertFalse($response['queued']);
        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame($first->request_id, $second->request_id);
    }

    public function testRerunCronSeesARequestQueuedByAnotherConnection() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $name = \OmegaUp\CronJobName::AssignBadges->value;
        // The other connection commits a row, so the table needs cleaning.
        \OmegaUp\Test\Utils::commit();
        // A plain read here would fix the snapshot the rest of the request sees.
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'SELECT COUNT(*) FROM Cron_Run_Requests;'
        );

        $other = self::otherConnection();
        try {
            $other->query(
                "INSERT INTO Cron_Run_Requests (name, status)
                 VALUES ('{$name}', 'pending');"
            );
        } finally {
            $other->close();
        }

        $response = \OmegaUp\Controllers\Admin::apiRerunCron(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ])
        );

        $this->assertFalse($response['queued']);
    }

    public function testRerunCronWaitsForTheJobLock() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $name = \OmegaUp\CronJobName::AssignBadges->value;
        $connection = \OmegaUp\MySQLConnection::getInstance();

        // Another request for the same job is still holding the job row.
        $other = self::otherConnection();
        $other->begin_transaction();
        $other->query(
            "SELECT job_id FROM Cron_Jobs WHERE name = '{$name}' FOR UPDATE;"
        );
        /** @var int|null */
        $timeout = $connection->GetOne(
            'SELECT @@SESSION.innodb_lock_wait_timeout;'
        );
        $connection->Execute('SET SESSION innodb_lock_wait_timeout = 1;');
        try {
            \OmegaUp\Controllers\Admin::apiRerunCron(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ]));
            $this->fail('Should have waited for the other request');
        } catch (\OmegaUp\Exceptions\DatabaseOperationException $e) {
            $this->assertStringContainsString(
                'Lock wait timeout',
                $e->getMessage()
            );
        } finally {
            $connection->Execute(
                'SET SESSION innodb_lock_wait_timeout = ' . intval($timeout) . ';'
            );
            $other->rollback();
            $other->close();
        }
        $this->assertNull(
            \OmegaUp\DAO\CronRunRequests::getActiveByName($name)
        );
    }

    private static function otherConnection(): \mysqli {
        return new \mysqli(
            OMEGAUP_DB_HOST,
            OMEGAUP_DB_USER,
            OMEGAUP_DB_PASS,
            OMEGAUP_DB_NAME
        );
    }

    public function testEveryJobNameTheApiAcceptsIsLaunchable() {
        // The dispatcher needs the name in Cron_Jobs and a script on disk,
        // so a case missing either would be queued and then rejected.
        $registered = array_map(
            fn ($job) => $job->name,
            \OmegaUp\DAO\CronJobs::getAllOrdered()
        );
        foreach (\OmegaUp\CronJobName::cases() as $case) {
            $this->assertContains($case->value, $registered);
            $this->assertFileExists(
                __DIR__ . '/../../../stuff/cron/' . $case->value
            );
        }
    }

    private function createModelRun(
        float $mapScore,
        bool $published,
        int $createdAt,
        ?string $skipReason = null,
        ?int $rngSeed = 42,
        string $outputPath = '/var/lib/omegaup/problem_rec_model.db'
    ): void {
        \OmegaUp\DAO\RecommendationModelRuns::create(
            new \OmegaUp\DAO\VO\RecommendationModelRuns([
                'map_score' => $mapScore,
                'dataset_size' => 4096,
                'num_followups' => 3,
                'followup_decay' => 0.4,
                'train_fraction' => 0.8,
                'rng_seed' => $rngSeed,
                'output_path' => $outputPath,
                'published' => $published,
                'skip_reason' => $skipReason,
                'created_at' => new \OmegaUp\Timestamp($createdAt),
            ])
        );
    }

    public function testGetCronsIncludesTheRecommendationModelRuns() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $now = \OmegaUp\Time::get();
        $this->createModelRun(
            0.0312,
            published: false,
            createdAt: $now - 200,
            skipReason: 'MAP score 0.0312 below minimum 0.0500'
        );
        $this->createModelRun(0.1934, published: true, createdAt: $now - 100);

        $response = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $modelRuns = $response['recommendationModelRuns'];
        $this->assertCount(2, $modelRuns);
        // Newest first.
        $this->assertGreaterThan(
            $modelRuns[1]['model_run_id'],
            $modelRuns[0]['model_run_id']
        );
        $this->assertEqualsWithDelta(0.1934, $modelRuns[0]['map_score'], 1e-9);
        $this->assertTrue($modelRuns[0]['published']);
        $this->assertNull($modelRuns[0]['skip_reason']);
        $this->assertSame(4096, $modelRuns[0]['dataset_size']);
        $this->assertSame(42, $modelRuns[0]['rng_seed']);
        $this->assertSame(
            '/var/lib/omegaup/problem_rec_model.db',
            $modelRuns[0]['output_path']
        );
        $this->assertSame($now - 100, $modelRuns[0]['created_at']->time);
        $this->assertEqualsWithDelta(0.0312, $modelRuns[1]['map_score'], 1e-9);
        $this->assertFalse($modelRuns[1]['published']);
        $this->assertSame(
            'MAP score 0.0312 below minimum 0.0500',
            $modelRuns[1]['skip_reason']
        );
    }

    public function testGetCronsTellsAManualRunWithoutASeedApart() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        // A run by hand against a scratch path, as the runbook describes.
        $this->createModelRun(
            0.1934,
            published: true,
            createdAt: \OmegaUp\Time::get(),
            rngSeed: null,
            outputPath: '/tmp/model.db'
        );

        $response = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $modelRuns = $response['recommendationModelRuns'];
        $this->assertCount(1, $modelRuns);
        $this->assertNull($modelRuns[0]['rng_seed']);
        $this->assertSame('/tmp/model.db', $modelRuns[0]['output_path']);
    }

    public function testGetCronsCapsTheRecommendationModelRuns() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $limit = \OmegaUp\Controllers\Admin::RECOMMENDATION_MODEL_RUNS_LIMIT;
        $now = \OmegaUp\Time::get();
        for ($i = 0; $i <= $limit; $i++) {
            $this->createModelRun(0.1, published: true, createdAt: $now - $i);
        }

        $response = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertCount($limit, $response['recommendationModelRuns']);
    }

    public function testGetCronsForTypeScriptCarriesTheSameModelRuns() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $this->createModelRun(
            0.1934,
            published: true,
            createdAt: \OmegaUp\Time::get() - 100
        );

        $page = \OmegaUp\Controllers\Admin::getCronsForTypeScript(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );
        $api = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $fromPage = $page['templateProperties']['payload']['recommendationModelRuns'];
        $this->assertCount(1, $fromPage);
        $this->assertEquals($api['recommendationModelRuns'], $fromPage);
    }
}
