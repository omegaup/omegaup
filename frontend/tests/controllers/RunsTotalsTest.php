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

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runDataOld = RunsFactory::createRun($problemData, $contestData, $contestant);

        $run = RunsDAO::getByAlias($runDataOld['response']['guid']);
        $run->setTime(date('Y-m-d H:i:s', strtotime('-72 hours')));
        RunsDAO::save($run);

        $response = RunController::apiCounts(new Request());

        $this->assertGreaterThan(1, count($response));
    }
}
