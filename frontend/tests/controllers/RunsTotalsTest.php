<?php

/**
 * Description of RunsTotalsTest
 */
class RunsTotalsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testRunTotals() {
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run. Submission gap must be 60 seconds
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runDataOld = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runDataOld['response']['guid']
        );
        $submission->time = date('Y-m-d H:i:s', strtotime('-72 hours'));
        \OmegaUp\DAO\Submissions::update($submission);
        $run = \OmegaUp\DAO\Runs::getByPK($submission->current_run_id);
        $run->time = date('Y-m-d H:i:s', strtotime('-72 hours'));
        \OmegaUp\DAO\Runs::update($run);

        $response = \OmegaUp\Controllers\Run::apiCounts(new \OmegaUp\Request());

        $this->assertGreaterThan(1, count($response));
    }

    // Simple test with NO-AC runs filter
    public function testNoAcRunsFilter() {
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run. Submission gap must be 60 seconds
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'AC', 65);

        // Get admin
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($admin);
        $runsList = \OmegaUp\Controllers\Run::apiList(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]))['runs'];
        $this->assertCount(2, $runsList);

        $runsList = \OmegaUp\Controllers\Run::apiList(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'verdict' => 'NO-AC',
        ]))['runs'];
        $this->assertCount(1, $runsList);
    }
}
