<?php

/**
 * Tests for the \OmegaUp\DAO\ProblemHealthChecks data access object.
 */
class ProblemHealthChecksDAOTest extends \OmegaUp\Test\ControllerTestCase {
    private function createFinding(
        int $problemId,
        string $checkType,
        string $severity,
        int $firstDetectedAt,
        ?int $resolvedAt = null
    ): void {
        \OmegaUp\DAO\ProblemHealthChecks::create(
            new \OmegaUp\DAO\VO\ProblemHealthChecks([
                'problem_id' => $problemId,
                'check_type' => $checkType,
                'severity' => $severity,
                'detail' => "detail for {$checkType}",
                'first_detected_at' => new \OmegaUp\Timestamp(
                    $firstDetectedAt
                ),
                'last_seen_at' => new \OmegaUp\Timestamp($firstDetectedAt),
                'resolved_at' => is_null(
                    $resolvedAt
                ) ? null : new \OmegaUp\Timestamp(
                    $resolvedAt
                ),
            ])
        );
    }

    public function testGetOpenFindingsReturnsTheProblemAliasAndTitle() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problem = $problemData['problem'];
        $this->createFinding(
            intval($problem->problem_id),
            \OmegaUp\ProblemHealthCheckType::NeverSolved->value,
            \OmegaUp\ProblemHealthSeverity::Warning->value,
            \OmegaUp\Time::get() - 100
        );

        $findings = array_values(array_filter(
            \OmegaUp\DAO\ProblemHealthChecks::getOpenFindings(200),
            fn ($finding) => $finding['problem_id'] === intval(
                $problem->problem_id
            )
        ));

        $this->assertCount(1, $findings);
        $this->assertSame($problem->alias, $findings[0]['alias']);
        $this->assertSame($problem->title, $findings[0]['title']);
        $this->assertSame(
            \OmegaUp\ProblemHealthCheckType::NeverSolved->value,
            $findings[0]['check_type']
        );
        $this->assertSame(
            'detail for never_solved',
            $findings[0]['detail']
        );
    }

    public function testGetOpenFindingsPutsErrorsBeforeWarnings() {
        $warned = \OmegaUp\Test\Factories\Problem::createProblem();
        $broken = \OmegaUp\Test\Factories\Problem::createProblem();
        $now = \OmegaUp\Time::get();
        // The warning is older, so only the severity can put the error first.
        $this->createFinding(
            intval($warned['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::NeverSolved->value,
            \OmegaUp\ProblemHealthSeverity::Warning->value,
            $now - 500
        );
        $this->createFinding(
            intval($broken['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::NoLanguages->value,
            \OmegaUp\ProblemHealthSeverity::Error->value,
            $now - 10
        );

        $problemIds = array_map(
            fn ($finding) => $finding['problem_id'],
            \OmegaUp\DAO\ProblemHealthChecks::getOpenFindings(200)
        );
        $errorPosition = array_search(
            intval($broken['problem']->problem_id),
            $problemIds,
            true
        );
        $warningPosition = array_search(
            intval($warned['problem']->problem_id),
            $problemIds,
            true
        );

        $this->assertNotFalse($errorPosition);
        $this->assertNotFalse($warningPosition);
        $this->assertLessThan($warningPosition, $errorPosition);
    }

    public function testGetOpenFindingsShowsTheNewestOfEqualSeverity() {
        $old = \OmegaUp\Test\Factories\Problem::createProblem();
        $recent = \OmegaUp\Test\Factories\Problem::createProblem();
        $now = \OmegaUp\Time::get();
        $this->createFinding(
            intval($old['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::NeverSolved->value,
            \OmegaUp\ProblemHealthSeverity::Warning->value,
            $now - 5000
        );
        $this->createFinding(
            intval($recent['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::DeprecatedPublic->value,
            \OmegaUp\ProblemHealthSeverity::Warning->value,
            $now - 10
        );

        $problemIds = array_map(
            fn ($finding) => $finding['problem_id'],
            \OmegaUp\DAO\ProblemHealthChecks::getOpenFindings(200)
        );
        $recentPosition = array_search(
            intval($recent['problem']->problem_id),
            $problemIds,
            true
        );
        $oldPosition = array_search(
            intval($old['problem']->problem_id),
            $problemIds,
            true
        );

        $this->assertNotFalse($recentPosition);
        $this->assertNotFalse($oldPosition);
        $this->assertLessThan($oldPosition, $recentPosition);
    }

    public function testGetOpenFindingsDoesNotHideNewFindingsBehindOldOnes() {
        $now = \OmegaUp\Time::get();
        $stale = \OmegaUp\Test\Factories\Problem::createProblem();
        $this->createFinding(
            intval($stale['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::JudgeErrors->value,
            \OmegaUp\ProblemHealthSeverity::Error->value,
            $now - 90000
        );
        $fresh = \OmegaUp\Test\Factories\Problem::createProblem();
        $this->createFinding(
            intval($fresh['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::NoLanguages->value,
            \OmegaUp\ProblemHealthSeverity::Error->value,
            $now - 1
        );

        $findings = \OmegaUp\DAO\ProblemHealthChecks::getOpenFindings(1);

        $this->assertCount(1, $findings);
        $this->assertSame(
            intval($fresh['problem']->problem_id),
            $findings[0]['problem_id']
        );
    }

    public function testGetOpenFindingsSkipsResolvedOnes() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problem = $problemData['problem'];
        $now = \OmegaUp\Time::get();
        $this->createFinding(
            intval($problem->problem_id),
            \OmegaUp\ProblemHealthCheckType::JudgeErrors->value,
            \OmegaUp\ProblemHealthSeverity::Error->value,
            $now - 100,
            $now - 50
        );

        $problemIds = array_map(
            fn ($finding) => $finding['problem_id'],
            \OmegaUp\DAO\ProblemHealthChecks::getOpenFindings(200)
        );

        $this->assertNotContains(intval($problem->problem_id), $problemIds);
    }

    public function testGetOpenFindingsHonorsTheLimit() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $this->createFinding(
            intval($problemData['problem']->problem_id),
            \OmegaUp\ProblemHealthCheckType::DeprecatedPublic->value,
            \OmegaUp\ProblemHealthSeverity::Warning->value,
            \OmegaUp\Time::get() - 100
        );

        $this->assertCount(
            1,
            \OmegaUp\DAO\ProblemHealthChecks::getOpenFindings(1)
        );
    }
}
