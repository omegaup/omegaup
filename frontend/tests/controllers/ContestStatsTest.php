<?php

/**
 * Description of ContestStatsTest
 *
 * @author joemmanuel
 */
class ContestStatsTest extends OmegaupTestCase {
    /**
     * Check stats are ok for WA, AC, PA and total counts
     * Also validates the max wait time guid
     */
    public function testGetStats() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run that we will wait to grade it
        $maxWaitRunData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Create some runs to be pending
        $pendingRunsCount = 10;
        $pendingRunsData = [];
        for ($i = 0; $i < $pendingRunsCount; $i++) {
            $pendingRunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);
        }

        $ACRunsCount = 7;
        $ACRunsData = [];
        for ($i = 0; $i < $ACRunsCount; $i++) {
            $ACRunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);

            // Grade the run
            RunsFactory::gradeRun($ACRunsData[$i]);
        }

        $WARunsCount = 5;
        $WARunsData = [];
        for ($i = 0; $i < $WARunsCount; $i++) {
            $WARunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);

            // Grade the run with WA
            RunsFactory::gradeRun($WARunsData[$i], 0, 'WA');
        }

        // Create request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = ContestController::apiStats($r);

        // Check number of pending runs
        $this->assertEquals(count($pendingRunsData) + 1 /* max wait run */, count($response['pending_runs']));
        $this->assertEquals(count($ACRunsData), ($response['verdict_counts']['AC']));
        $this->assertEquals(count($WARunsData), ($response['verdict_counts']['WA']));

        $this->assertEquals($maxWaitRunData['response']['guid'], $response['max_wait_time_guid']);

        $this->assertEquals($pendingRunsCount + $ACRunsCount + $WARunsCount + 1, $response['total_runs']);
        $this->assertEquals(1, $response['distribution'][100]);
    }

    /**
     * Checks that, if there's no wait time, 0 is posted in max_wait_time
     */
    public function testGetStatsNoWaitTime() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        $ACRunsCount = 2;
        $ACRunsData = [];
        for ($i = 0; $i < $ACRunsCount; $i++) {
            $ACRunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);

            // Grade the run
            RunsFactory::gradeRun($ACRunsData[$i]);
        }

        // Create request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = ContestController::apiStats($r);

        // Check number of pending runs
        $this->assertEquals($ACRunsCount, $response['total_runs']);
        $this->assertEquals(0, $response['max_wait_time']);
        $this->assertEquals(0, $response['max_wait_time_guid']);
    }
}
