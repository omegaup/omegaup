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

    private function createModelRun(
        float $mapScore,
        bool $published,
        int $createdAt,
        ?string $skipReason = null
    ): void {
        \OmegaUp\DAO\RecommendationModelRuns::create(
            new \OmegaUp\DAO\VO\RecommendationModelRuns([
                'map_score' => $mapScore,
                'dataset_size' => 4096,
                'num_followups' => 3,
                'followup_decay' => 0.4,
                'train_fraction' => 0.8,
                'rng_seed' => 42,
                'output_path' => '/tmp/model.db',
                'published' => $published,
                'skip_reason' => $skipReason,
                'created_at' => new \OmegaUp\Timestamp($createdAt),
            ])
        );
    }

    /**
     * @param list<array{map_score: float, dataset_size: int, rng_seed: int|null, published: bool, skip_reason: null|string, created_at: \OmegaUp\Timestamp}> $modelRuns
     * @param list<float> $scores
     *
     * @return list<array{map_score: float, dataset_size: int, rng_seed: int|null, published: bool, skip_reason: null|string, created_at: \OmegaUp\Timestamp}>
     */
    private function onlyTheseRuns(array $modelRuns, array $scores): array {
        // The database is shared, so match only the runs this test created.
        return array_values(array_filter(
            $modelRuns,
            function (array $modelRun) use ($scores): bool {
                foreach ($scores as $score) {
                    if (abs($modelRun['map_score'] - $score) < 1e-9) {
                        return true;
                    }
                }
                return false;
            }
        ));
    }

    public function testGetCronsIncludesTheRecommendationModelRuns() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $now = \OmegaUp\Time::get();
        $this->createModelRun(
            0.111111,
            published: false,
            createdAt: $now - 200,
            skipReason: 'MAP score 0.1111 below minimum 0.3000'
        );
        $this->createModelRun(0.222222, published: true, createdAt: $now - 100);

        $response = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $modelRuns = $this->onlyTheseRuns(
            $response['recommendationModelRuns'],
            [0.222222, 0.111111]
        );
        $this->assertCount(2, $modelRuns);
        // Newest first.
        $this->assertEqualsWithDelta(
            0.222222,
            $modelRuns[0]['map_score'],
            1e-9
        );
        $this->assertTrue($modelRuns[0]['published']);
        $this->assertNull($modelRuns[0]['skip_reason']);
        $this->assertSame(4096, $modelRuns[0]['dataset_size']);
        $this->assertSame(42, $modelRuns[0]['rng_seed']);
        $this->assertSame($now - 100, $modelRuns[0]['created_at']->time);
        $this->assertEqualsWithDelta(
            0.111111,
            $modelRuns[1]['map_score'],
            1e-9
        );
        $this->assertFalse($modelRuns[1]['published']);
        $this->assertSame(
            'MAP score 0.1111 below minimum 0.3000',
            $modelRuns[1]['skip_reason']
        );
    }

    public function testGetCronsListsTheTrainingJob() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        $response = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $names = array_map(fn ($job) => $job['name'], $response['jobs']);
        $this->assertContains(
            \OmegaUp\CronJobName::BuildProblemRecModel->value,
            $names
        );
    }

    public function testGetCronsForTypeScriptCarriesTheSameModelRuns() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $this->createModelRun(
            0.333333,
            published: true,
            createdAt: \OmegaUp\Time::get() - 100
        );

        $page = \OmegaUp\Controllers\Admin::getCronsForTypeScript(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );
        $api = \OmegaUp\Controllers\Admin::apiGetCrons(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $fromPage = $this->onlyTheseRuns(
            $page['templateProperties']['payload']['recommendationModelRuns'],
            [0.333333]
        );
        $this->assertCount(1, $fromPage);
        $this->assertEquals(
            $this->onlyTheseRuns($api['recommendationModelRuns'], [0.333333]),
            $fromPage
        );
    }
}
