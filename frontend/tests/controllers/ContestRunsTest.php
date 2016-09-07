<?php

/**
 * Description of ContestRunsTest
 *
 * @author joemmanuel
 */

class ContestRunsTest extends OmegaupTestCase {
    /**
     * Contestant submits runs and admin is able to get them
     */
    public function testGetRunsForContest() {
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

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Create request
        $login = self::login($contestData['director']);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Call API
        $response = ContestController::apiRuns($r);

        // Assert
        $this->assertEquals(1, count($response['runs']));
        $this->assertEquals($runData['response']['guid'], $response['runs'][0]['guid']);
        $this->assertEquals($contestant->username, $response['runs'][0]['username']);
        $this->assertEquals('J1', $response['runs'][0]['judged_by']);
    }
}
