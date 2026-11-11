<?php

/**
 * Tests for the \OmegaUp\DAO\CronRunRequests data access object.
 */
class CronRunRequestsDAOTest extends \OmegaUp\Test\ControllerTestCase {
    private function createRequest(
        string $name,
        \OmegaUp\CronRunRequestStatus $status,
        int $requestedAt
    ): int {
        $request = new \OmegaUp\DAO\VO\CronRunRequests([
            'name' => $name,
            'status' => $status->value,
            'requested_at' => new \OmegaUp\Timestamp($requestedAt),
        ]);
        \OmegaUp\DAO\CronRunRequests::create($request);
        return intval($request->request_id);
    }

    public function testGetActiveByNameFindsAPendingRequest() {
        $this->createRequest(
            \OmegaUp\CronJobName::UpdateRanks->value,
            \OmegaUp\CronRunRequestStatus::Pending,
            \OmegaUp\Time::get() - 100
        );

        $request = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::UpdateRanks->value
        );

        $this->assertNotNull($request);
        $this->assertSame(
            \OmegaUp\CronRunRequestStatus::Pending->value,
            $request->status
        );
    }

    public function testGetActiveByNameFindsARequestThatIsAlreadyRunning() {
        $this->createRequest(
            \OmegaUp\CronJobName::AssignBadges->value,
            \OmegaUp\CronRunRequestStatus::Picked,
            \OmegaUp\Time::get() - 100
        );

        $request = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::AssignBadges->value
        );

        $this->assertNotNull($request);
        $this->assertSame(
            \OmegaUp\CronRunRequestStatus::Picked->value,
            $request->status
        );
    }

    public function testGetActiveByNameIgnoresFinishedRequests() {
        $now = \OmegaUp\Time::get();
        $this->createRequest(
            \OmegaUp\CronJobName::AggregateFeedback->value,
            \OmegaUp\CronRunRequestStatus::Done,
            $now - 200
        );
        $this->createRequest(
            \OmegaUp\CronJobName::AggregateFeedback->value,
            \OmegaUp\CronRunRequestStatus::Failed,
            $now - 100
        );

        $this->assertNull(
            \OmegaUp\DAO\CronRunRequests::getActiveByName(
                \OmegaUp\CronJobName::AggregateFeedback->value
            )
        );
    }

    public function testGetActiveByNameIgnoresOtherJobs() {
        $this->createRequest(
            \OmegaUp\CronJobName::UpdateRanks->value,
            \OmegaUp\CronRunRequestStatus::Pending,
            \OmegaUp\Time::get() - 100
        );

        $this->assertNull(
            \OmegaUp\DAO\CronRunRequests::getActiveByName(
                \OmegaUp\CronJobName::AssignBadges->value
            )
        );
    }

    public function testGetActiveByNameSkipsAFinishedRequestOfTheSameJob() {
        $now = \OmegaUp\Time::get();
        $this->createRequest(
            \OmegaUp\CronJobName::UpdateRanks->value,
            \OmegaUp\CronRunRequestStatus::Done,
            $now - 500
        );
        $active = $this->createRequest(
            \OmegaUp\CronJobName::UpdateRanks->value,
            \OmegaUp\CronRunRequestStatus::Pending,
            $now - 10
        );

        $request = \OmegaUp\DAO\CronRunRequests::getActiveByName(
            \OmegaUp\CronJobName::UpdateRanks->value
        );

        $this->assertNotNull($request);
        $this->assertSame($active, $request->request_id);
    }

    public function testTheColumnAcceptsEveryStatusOfTheEnum() {
        foreach (\OmegaUp\CronRunRequestStatus::cases() as $case) {
            $requestId = $this->createRequest(
                \OmegaUp\CronJobName::UpdateRanks->value,
                $case,
                \OmegaUp\Time::get() - 100
            );
            $request = \OmegaUp\DAO\CronRunRequests::getByPK($requestId);
            $this->assertNotNull($request);
            $this->assertSame($case->value, $request->status);
        }
    }
}
