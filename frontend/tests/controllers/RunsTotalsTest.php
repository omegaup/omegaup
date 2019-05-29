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
        $contestant = UserFactory::createUser();

        // Create a run. Submission gap must be 60 seconds
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        Time::setTimeForTesting(Time::get() + 60);
        $runDataOld = RunsFactory::createRun($problemData, $contestData, $contestant);

        $submission = SubmissionsDAO::getByGuid($runDataOld['response']['guid']);
        $submission->time = date('Y-m-d H:i:s', strtotime('-72 hours'));
        SubmissionsDAO::update($submission);
        $run = RunsDAO::getByPK($submission->current_run_id);
        $run->time = date('Y-m-d H:i:s', strtotime('-72 hours'));
        RunsDAO::update($run);

        $response = RunController::apiCounts(new Request());

        $this->assertGreaterThan(1, count($response));
    }
}
