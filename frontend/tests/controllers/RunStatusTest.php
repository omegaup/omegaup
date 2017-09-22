<?php

/**
 * Description of DetailsRunTest
 *
 * @author joemmanuel
 */

class RunStatusTest extends OmegaupTestCase {
    /**
     * Basic test of viewing run details
     *
     */
    public function testShowRunDetailsValid() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Prepare request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]);

        // Call API
        $response = RunController::apiStatus($r);

        $this->assertEquals($r['run_alias'], $response['guid']);
        $this->assertEquals('JE', $response['verdict']);
        $this->assertEquals('new', $response['status']);
    }
}
