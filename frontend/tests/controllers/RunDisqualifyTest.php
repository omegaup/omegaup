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
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create a new run
        $runData = RunsFactory::createRun(
            $problemData,
            $contestData,
            $identity
        );

        RunsFactory::gradeRun($runData);

        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid']
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiDisqualify($r);

        $this->assertEquals('ok', $response['status']);
    }

    public function testDisqualifyScoreboard() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestants
        ['user' => $contestant1, 'identity' => $identity1] = UserFactory::createUser();
        ['user' => $contestant2, 'identity' => $identity2] = UserFactory::createUser();

        // Create new runs
        $runData1 = RunsFactory::createRun(
            $problemData,
            $contestData,
            $identity1
        );
        $runData2 = RunsFactory::createRun(
            $problemData,
            $contestData,
            $identity2
        );

        RunsFactory::gradeRun($runData1);
        RunsFactory::gradeRun($runData2);

        // Disqualify run by contestant1
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData1['response']['guid']
        ]);
        \OmegaUp\Controllers\Run::apiDisqualify($r);

        // Check scoreboard
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = \OmegaUp\Controllers\Contest::apiScoreboard($r);

        // Contestant 2 should not be changed
        $this->assertEquals(
            $contestant2->username,
            $response['ranking'][0]['username']
        );
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        // Contestant 1 should be changed
        $this->assertEquals(
            $contestant1->username,
            $response['ranking'][1]['username']
        );
        $this->assertEquals(
            0,
            $response['ranking'][1]['problems'][0]['points']
        );
        $this->assertEquals(0, $response['ranking'][1]['problems'][0]['runs']);
    }
}
