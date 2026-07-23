<?php

/**
 * Tests for the \OmegaUp\DAO\RecommendationModelRuns data access object and the
 * registry seed for the recommendation model training job.
 */
class RecommendationModelRunsDAOTest extends \OmegaUp\Test\ControllerTestCase {
    private function createModelRun(
        float $mapScore,
        bool $published,
        int $createdAt,
        ?string $skipReason = null
    ): void {
        \OmegaUp\DAO\RecommendationModelRuns::create(
            new \OmegaUp\DAO\VO\RecommendationModelRuns([
                'map_score' => $mapScore,
                'dataset_size' => 1000,
                'num_followups' => 3,
                'followup_decay' => 0.4,
                'train_fraction' => 0.8,
                'output_path' => '/tmp/model.db',
                'published' => $published,
                'skip_reason' => $skipReason,
                'created_at' => new \OmegaUp\Timestamp($createdAt),
            ])
        );
    }

    public function testRecommendationJobIsRegistered() {
        $job = \OmegaUp\DAO\CronJobs::getByName('build_problem_rec_model.py');

        $this->assertNotNull($job);
        $this->assertSame('build_problem_rec_model.py', $job->name);
        $this->assertTrue($job->enabled);
    }

    public function testGetLatestPublishedReturnsMostRecentPublished() {
        $now = \OmegaUp\Time::get();
        $this->createModelRun(0.40, true, $now - 300);
        $this->createModelRun(0.20, false, $now - 200, 'below threshold');
        $this->createModelRun(0.45, true, $now - 100);

        $latest = \OmegaUp\DAO\RecommendationModelRuns::getLatestPublished();

        $this->assertNotNull($latest);
        $this->assertEqualsWithDelta(0.45, $latest->map_score, 1e-9);
        $this->assertTrue($latest->published);
    }

    public function testGetLatestPublishedSkipsUnpublishedRuns() {
        $now = \OmegaUp\Time::get();
        $this->createModelRun(0.40, true, $now - 200);
        $this->createModelRun(0.10, false, $now - 10, 'accuracy too low');

        $latest = \OmegaUp\DAO\RecommendationModelRuns::getLatestPublished();

        $this->assertNotNull($latest);
        $this->assertEqualsWithDelta(0.40, $latest->map_score, 1e-9);
    }

    public function testGetRecentReturnsRunsNewestFirst() {
        $now = \OmegaUp\Time::get();
        $this->createModelRun(0.30, true, $now - 300);
        $this->createModelRun(0.35, false, $now - 200, 'regressed');
        $this->createModelRun(0.50, true, $now - 100);

        $recent = \OmegaUp\DAO\RecommendationModelRuns::getRecent(2);

        $this->assertCount(2, $recent);
        $this->assertEqualsWithDelta(0.50, $recent[0]->map_score, 1e-9);
        $this->assertEqualsWithDelta(0.35, $recent[1]->map_score, 1e-9);
        $this->assertSame('regressed', $recent[1]->skip_reason);
    }
}
