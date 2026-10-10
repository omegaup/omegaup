<?php

/**
 * Tests for the \OmegaUp\DAO\RecommendationModelRuns data access object and for
 * the registry entry of the recommendation model training job.
 */
class RecommendationModelRunsDAOTest extends \OmegaUp\Test\ControllerTestCase {
    private function createModelRun(
        float $mapScore,
        bool $published,
        int $createdAt,
        ?string $skipReason = null,
        ?int $rngSeed = null
    ): \OmegaUp\DAO\VO\RecommendationModelRuns {
        $modelRun = new \OmegaUp\DAO\VO\RecommendationModelRuns([
            'map_score' => $mapScore,
            'dataset_size' => 1000,
            'num_followups' => 3,
            'followup_decay' => 0.4,
            'train_fraction' => 0.8,
            'rng_seed' => $rngSeed,
            'output_path' => '/tmp/model.db',
            'published' => $published,
            'skip_reason' => $skipReason,
            'created_at' => new \OmegaUp\Timestamp($createdAt),
        ]);
        \OmegaUp\DAO\RecommendationModelRuns::create($modelRun);
        return $modelRun;
    }

    public function testTrainingJobIsRegistered() {
        $job = \OmegaUp\DAO\CronJobs::getByName(
            \OmegaUp\CronJobName::BuildProblemRecModel->value
        );

        $this->assertNotNull($job);
        $this->assertTrue($job->enabled);
        $this->assertSame('0 4 * * 0', $job->schedule);
    }

    public function testGetRecentReturnsRunsNewestFirst() {
        $now = \OmegaUp\Time::get();
        $this->createModelRun(0.30, published: true, createdAt: $now - 300);
        $this->createModelRun(
            0.35,
            published: false,
            createdAt: $now - 200,
            skipReason: 'regressed'
        );
        $this->createModelRun(0.50, published: true, createdAt: $now - 100);

        $recent = \OmegaUp\DAO\RecommendationModelRuns::getRecent(20);

        $this->assertCount(3, $recent);
        $this->assertEqualsWithDelta(0.50, $recent[0]->map_score, 1e-9);
        $this->assertEqualsWithDelta(0.35, $recent[1]->map_score, 1e-9);
        $this->assertEqualsWithDelta(0.30, $recent[2]->map_score, 1e-9);
        $this->assertSame('regressed', $recent[1]->skip_reason);
        $this->assertNull($recent[0]->skip_reason);
    }

    public function testGetRecentOrdersRunsRecordedInTheSameSecond() {
        $sameSecond = \OmegaUp\Time::get() - 100;
        $older = $this->createModelRun(
            0.10,
            published: false,
            createdAt: $sameSecond,
            skipReason: 'below minimum'
        );
        $newer = $this->createModelRun(
            0.20,
            published: true,
            createdAt: $sameSecond
        );

        $recent = \OmegaUp\DAO\RecommendationModelRuns::getRecent(20);

        $this->assertSame($newer->model_run_id, $recent[0]->model_run_id);
        $this->assertSame($older->model_run_id, $recent[1]->model_run_id);
    }

    public function testGetRecentHonorsTheLimit() {
        $now = \OmegaUp\Time::get();
        $this->createModelRun(0.30, published: true, createdAt: $now - 300);
        $this->createModelRun(0.40, published: true, createdAt: $now - 200);

        $recent = \OmegaUp\DAO\RecommendationModelRuns::getRecent(1);

        $this->assertCount(1, $recent);
        $this->assertEqualsWithDelta(0.40, $recent[0]->map_score, 1e-9);
    }

    public function testGetRecentKeepsAFixedSeedOfZeroApartFromNoSeed() {
        $now = \OmegaUp\Time::get();
        $this->createModelRun(
            0.30,
            published: true,
            createdAt: $now - 200,
            rngSeed: 0
        );
        $this->createModelRun(0.40, published: true, createdAt: $now - 100);

        $recent = \OmegaUp\DAO\RecommendationModelRuns::getRecent(20);

        $this->assertNull($recent[0]->rng_seed);
        $this->assertSame(0, $recent[1]->rng_seed);
    }

    public function testGetRecentKeepsEveryTrainingParameter() {
        $this->createModelRun(
            0.42,
            published: true,
            createdAt: \OmegaUp\Time::get() - 100,
            rngSeed: 7
        );

        $recent = \OmegaUp\DAO\RecommendationModelRuns::getRecent(1);

        $this->assertSame(1000, $recent[0]->dataset_size);
        $this->assertSame(3, $recent[0]->num_followups);
        $this->assertEqualsWithDelta(0.4, $recent[0]->followup_decay, 1e-9);
        $this->assertEqualsWithDelta(0.8, $recent[0]->train_fraction, 1e-9);
        $this->assertSame(7, $recent[0]->rng_seed);
        $this->assertSame('/tmp/model.db', $recent[0]->output_path);
        $this->assertTrue($recent[0]->published);
    }
}
