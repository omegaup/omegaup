<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Description of ContestStatsTest
 */
class ContestStatsTest extends \OmegaUp\Test\ControllerTestCase {
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

        // Create a run that we will wait to grade it
        $maxWaitRunData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);

        // Create some runs to be pending
        $pendingRunsCount = 10;
        $pendingRunsData = [];
        for ($i = 0; $i < $pendingRunsCount; $i++) {
            $pendingRunsData[$i] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        $ACRunsCount = 7;
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

        $WARunsCount = 5;
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
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiStats($r);

        // Check number of pending runs
        $this->assertSame(
            count(
                $pendingRunsData
            ) + 1 /* max wait run */,
            count(
                $response['pending_runs']
            )
        );
        $this->assertSame(
            count(
                $ACRunsData
            ),
            ($response['verdict_counts']['AC'])
        );
        $this->assertSame(
            count(
                $WARunsData
            ),
            ($response['verdict_counts']['WA'])
        );

        $this->assertSame(
            $maxWaitRunData['response']['guid'],
            $response['max_wait_time_guid']
        );

        $this->assertSame(
            $pendingRunsCount + $ACRunsCount + $WARunsCount + 1,
            $response['total_runs']
        );
        $this->assertSame(1, $response['distribution'][100]);
    }

    /**
     * Checks that, if there's no wait time, 0 is posted in max_wait_time
     */
    public function testGetStatsNoWaitTime() {
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

        // Create request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiStats($r);

        // Check number of pending runs
        $this->assertSame($ACRunsCount, $response['total_runs']);
        $this->assertNull($response['max_wait_time']);
        $this->assertNull($response['max_wait_time_guid']);
    }
}
