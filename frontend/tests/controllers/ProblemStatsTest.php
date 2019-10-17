<?php

/**
 * Description of ContestStatsTest
 *
 * @author joemmanuel
 */
class ProblemStatsTest extends OmegaupTestCase {
    /**
     * Check stats are ok for WA, AC, PA and total counts
     * Also validates the max wait time guid
     */
    public function testGetStats() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create some runs to be pending
        $pendingRunsCount = 5;
        $pendingRunsData = [];
        for ($i = 0; $i < $pendingRunsCount; $i++) {
            $pendingRunsData[$i] = RunsFactory::createRun(
                $problemData,
                $contestData,
                $contestant
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        $ACRunsCount = 2;
        $ACRunsData = [];
        for ($i = 0; $i < $ACRunsCount; $i++) {
            $ACRunsData[$i] = RunsFactory::createRun(
                $problemData,
                $contestData,
                $contestant
            );

            // Grade the run
            RunsFactory::gradeRun($ACRunsData[$i]);
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        $WARunsCount = 1;
        $WARunsData = [];
        for ($i = 0; $i < $WARunsCount; $i++) {
            $WARunsData[$i] = RunsFactory::createRun(
                $problemData,
                $contestData,
                $contestant
            );

            // Grade the run with WA
            RunsFactory::gradeRun($WARunsData[$i], 0, 'WA');
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
