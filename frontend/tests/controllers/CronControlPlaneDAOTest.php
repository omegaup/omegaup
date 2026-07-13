<?php

/**
 * Tests for the \OmegaUp\DAO\CronJobs and \OmegaUp\DAO\CronRuns data access
 * objects.
 */
class CronControlPlaneDAOTest extends \OmegaUp\Test\ControllerTestCase {
    private function createRun(
        string $name,
        string $status,
        int $startedAt
    ): void {
        \OmegaUp\DAO\CronRuns::create(new \OmegaUp\DAO\VO\CronRuns([
            'name' => $name,
            'status' => $status,
            'started_at' => new \OmegaUp\Timestamp($startedAt),
        ]));
    }

    public function testGetByNameReturnsSeededJob() {
        $job = \OmegaUp\DAO\CronJobs::getByName('update_ranks.py');

        $this->assertNotNull($job);
        $this->assertSame('update_ranks.py', $job->name);
        $this->assertTrue($job->enabled);
    }

    public function testGetByNameReturnsNullForUnknownJob() {
        $this->assertNull(
            \OmegaUp\DAO\CronJobs::getByName(
                \OmegaUp\Test\Utils::createRandomString()
            )
        );
    }

    public function testGetAllOrderedReturnsSeededJobsSortedByName() {
        $jobs = \OmegaUp\DAO\CronJobs::getAllOrdered();
        $names = array_map(fn ($job) => $job->name, $jobs);

        $this->assertContains('update_ranks.py', $names);
        $this->assertContains('assign_badges.py', $names);
        $this->assertContains('aggregate_feedback.py', $names);

        $sorted = $names;
        sort($sorted);
        $this->assertSame($sorted, $names);
    }

    public function testGetLatestByNameReturnsMostRecentRun() {
        $name = \OmegaUp\Test\Utils::createRandomString();
        $now = \OmegaUp\Time::get();
        $this->createRun($name, 'success', $now - 100);
        $this->createRun($name, 'failure', $now - 10);

        $latest = \OmegaUp\DAO\CronRuns::getLatestByName($name);

        $this->assertNotNull($latest);
        $this->assertSame('failure', $latest->status);
    }

    public function testGetLatestSuccessfulByNameSkipsFailures() {
        $name = \OmegaUp\Test\Utils::createRandomString();
        $now = \OmegaUp\Time::get();
        $this->createRun($name, 'success', $now - 100);
        $this->createRun($name, 'failure', $now - 10);

        $latest = \OmegaUp\DAO\CronRuns::getLatestSuccessfulByName($name);

        $this->assertNotNull($latest);
        $this->assertSame('success', $latest->status);
    }

    public function testGetLatestByNameReturnsNullWhenNoRuns() {
        $this->assertNull(
            \OmegaUp\DAO\CronRuns::getLatestByName(
                \OmegaUp\Test\Utils::createRandomString()
            )
        );
    }

    public function testGetRecentByNameReturnsOnlyThatJob() {
        $name = \OmegaUp\Test\Utils::createRandomString();
        $other = \OmegaUp\Test\Utils::createRandomString();
        $now = \OmegaUp\Time::get();
        $this->createRun($name, 'success', $now - 50);
        $this->createRun($other, 'success', $now - 40);

        $runs = \OmegaUp\DAO\CronRuns::getRecentByName($name, 10);

        $this->assertNotEmpty($runs);
        foreach ($runs as $run) {
            $this->assertSame($name, $run->name);
        }
    }

    public function testGetRecentReturnsRunsAcrossJobsMostRecentFirst() {
        $olderName = \OmegaUp\Test\Utils::createRandomString();
        $newerName = \OmegaUp\Test\Utils::createRandomString();
        $now = \OmegaUp\Time::get();
        $this->createRun($olderName, 'success', $now - 5);
        $this->createRun($newerName, 'success', $now - 3);

        $names = array_map(
            fn ($run) => $run->name,
            \OmegaUp\DAO\CronRuns::getRecent(100)
        );
        $olderPosition = array_search($olderName, $names, true);
        $newerPosition = array_search($newerName, $names, true);

        $this->assertNotFalse($olderPosition);
        $this->assertNotFalse($newerPosition);
        // Ordered by started_at descending, so the newer run comes first.
        $this->assertLessThan($olderPosition, $newerPosition);
    }
}
