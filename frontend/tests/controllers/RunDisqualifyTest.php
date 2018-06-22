<?php

/**
 * Unittest for disqualifying run
 *
 * @author SpaceWhite
 */
class RunDisqualifyTest extends OmegaupTestCase {
    public function testDisqualifyByAdmin() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a new run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        RunsFactory::gradeRun($runData);

        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid']
        ]);

        // Call API
        $response = RunController::apiDisqualify($r);

        $this->assertEquals('ok', $response['status']);
    }

    public function testDisqualifyScoreboard() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a new run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid']
        ]);

        // Call API
        RunController::apiDisqualify($r);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        $response = ContestController::apiScoreboard($r);

        $this->assertEquals($contestant->username, $response['ranking'][0]['username']);
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['runs']);
    }
}
