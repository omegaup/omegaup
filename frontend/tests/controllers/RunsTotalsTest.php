<?php

/**
 * Description of RunsTotalsTest
 *
 * @author joemmanuel
 */
class RunsTotalsTest extends OmegaupTestCase {
    public function testRunTotals() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create a run. Submission gap must be 60 seconds
        $runData = RunsFactory::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runDataOld = RunsFactory::createRun(
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
}
