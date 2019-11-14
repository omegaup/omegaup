<?php

/**
 * Description of ContestStatsTest
 *
 * @author joemmanuel
 */
class ProblemStatsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Check stats are ok for WA, AC, PA and total counts
     * Also validates the max wait time guid
     */
    public function testGetStats() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create some runs to be pending
        $pendingRunsCount = 5;
        $pendingRunsData = [];
        for ($i = 0; $i < $pendingRunsCount; $i++) {
            $pendingRunsData[$i] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        $ACRunsCount = 2;
        $ACRunsData = [];
        for ($i = 0; $i < $ACRunsCount; $i++) {
            $ACRunsData[$i] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );

            // Grade the run
            \OmegaUp\Test\Factories\Run::gradeRun($ACRunsData[$i]);
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        $WARunsCount = 1;
        $WARunsData = [];
        for ($i = 0; $i < $WARunsCount; $i++) {
            $WARunsData[$i] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );

            // Grade the run with WA
            \OmegaUp\Test\Factories\Run::gradeRun($WARunsData[$i], 0, 'WA');
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        // Create request
        $login = self::login($problemData['author']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Problem::apiStats($r);

        // Check number of pending runs
        $this->assertEquals(
            count(
                $pendingRunsData
            ),
            count(
                $response['pending_runs']
            )
        );
        $this->assertEquals(
            count(
                $ACRunsData
            ),
            ($response['verdict_counts']['AC'])
        );
        $this->assertEquals(
            count(
                $WARunsData
            ),
            ($response['verdict_counts']['WA'])
        );

        $this->assertEquals(
            $pendingRunsCount + $ACRunsCount + $WARunsCount,
            $response['total_runs']
        );
    }
}
