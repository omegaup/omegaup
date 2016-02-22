<?php

/**
 * Description of ContestScoreboardTest
 *
 * @author joemmanuel
 */

class ContestScoreboardTest extends OmegaupTestCase {
    /**
     * Basic test of scoreboard, shows at least the run
     * just submitted
     */
    public function testBasicScoreboard() {
        // Get two problems
        $problemData = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        // Create our contestants
        $contestant = UserFactory::createUser();
        $contestant2 = UserFactory::createUser();
        $contestant3 = UserFactory::createUser();
        $contestDirector = $contestData['director'];
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        // Create runs
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData1 = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData2 = RunsFactory::createRun($problemData, $contestData, $contestant2);
        $runData3 = RunsFactory::createRun($problemData, $contestData, $contestant3);
        $runData4 = RunsFactory::createRun($problemData2, $contestData, $contestant);
        $runDataDirector = RunsFactory::createRun($problemData, $contestData, $contestDirector);
        $runDataAdmin = RunsFactory::createRun($problemData, $contestData, $contestAdmin);

        // Grade the runs
        RunsFactory::gradeRun($runData, 0, 'CE', 60);
        RunsFactory::gradeRun($runData1, 1, 'AC', 60);
        RunsFactory::gradeRun($runData2, .9, 'PA', 60);
        RunsFactory::gradeRun($runData3, 1, 'AC', 180);
        RunsFactory::gradeRun($runData4, 1, 'AC', 200);
        RunsFactory::gradeRun($runDataDirector, 1, 'AC', 120);
        RunsFactory::gradeRun($runDataAdmin, 1, 'AC', 110);

        // Create request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['auth_token'] = $this->login($contestant);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals($contestant->getUsername(), $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(260, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertEquals(1, $response['ranking'][0]['place']);
        $this->assertEquals(2, $response['ranking'][1]['place']);
        $this->assertEquals(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertEquals(100, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(60, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertEquals(100, $response['ranking'][0]['problems'][1]['points']);
        $this->assertEquals(200, $response['ranking'][0]['problems'][1]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][1]['runs']);

        // Now get the scoreboard as an contest director
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['auth_token'] = $this->login($contestDirector);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals($contestant->getUsername(), $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(260, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertEquals(1, $response['ranking'][0]['place']);
        $this->assertEquals(2, $response['ranking'][1]['place']);
        $this->assertEquals(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertEquals(100, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(60, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertEquals(100, $response['ranking'][0]['problems'][1]['points']);
        $this->assertEquals(200, $response['ranking'][0]['problems'][1]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][1]['runs']);
    }

    /**
     * Basic test of scoreboard with max policy.
     */
    public function testMaxPolicyScoreboard() {
        // Get two problems
        $problemData = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest(null, 1, null, null, null, 'max');

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        // Create our contestants
        $contestant = UserFactory::createUser();

        // Create runs
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData1 = RunsFactory::createRun($problemData2, $contestData, $contestant);

        // Grade the runs
        RunsFactory::gradeRun($runData, 1, 'AC', 60);
        RunsFactory::gradeRun($runData1, 1, 'AC', 200);

        // Create request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['auth_token'] = $this->login($contestant);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(1, count($response['ranking']));
        $this->assertEquals($contestant->getUsername(), $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(200, $response['ranking'][0]['total']['penalty']);
    }

    /**
     * Set 0% of scoreboard for contestants, should show all 0s
     */
    public function testScoreboardPercentajeForContestant() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Set 0% of scoreboard show
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Create request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['auth_token'] = $this->login($contestant);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(1, count($response['ranking']));

        $this->assertEquals($contestant->getUsername(), $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(0, $response['ranking'][0]['total']['points']);
        $this->assertEquals(0, $response['ranking'][0]['total']['penalty']); /* 60 because contest started 60 mins ago in the default factory */

        // Check data per problem
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
    }

    /**
     * Set 0% of scoreboard for admins
     */
    public function testScoreboardPercentajeForContestAdmin() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Set 0% of scoreboard show
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Create request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['auth_token'] = $this->login($contestData['director']);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(1, count($response['ranking']));

        $this->assertEquals($contestant->getUsername(), $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(100, $response['ranking'][0]['total']['points']);
        $this->assertEquals(60, $response['ranking'][0]['total']['penalty']); /* 60 because contest started 60 mins ago in the default factory */

        // Check data per problem
        $this->assertEquals(100, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(60, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
    }

    /**
     * Scoreboard merge basic test
     */
    public function testScoreboardMerge() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get contests
        $contestData = ContestsFactory::createContest();
        $contestData2 = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ContestsFactory::addProblemToContest($problemData, $contestData2);

        // Create our contestants
        $contestant = UserFactory::createUser();
        $contestant2 = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData2 = RunsFactory::createRun($problemData, $contestData, $contestant2);
        $runData3 = RunsFactory::createRun($problemData, $contestData2, $contestant2);

        // Grade the run
        RunsFactory::gradeRun($runData);
        RunsFactory::gradeRun($runData2);
        RunsFactory::gradeRun($runData3);

        // Create request
        $r = new Request();
        $r['contest_aliases'] = $contestData['request']['alias'] . ',' . $contestData2['request']['alias'];
        $r['auth_token'] = $this->login($contestant);

        // Call API
        $response = ContestController::apiScoreboardMerge($r);

        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(100, $response['ranking'][1]['total']['points']);
        $this->assertEquals(0, $response['ranking'][1]['contests'][$contestData2['request']['alias']]['points']);
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrl() {
        // Get a private contest with 0% of scoreboard show percentage
        $contestData = ContestsFactory::createContest(null, 0);
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Create problem
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our user not added to the contest
        $externalUser = UserFactory::createUser();

        // Create our contestant, will submit 1 run
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        RunsFactory::gradeRun($runData);

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $response = ContestController::apiMyList(new Request(array(
            'auth_token' => $this->login($contestData['director'])
        )));

        // Look for our contest from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['results'] as $c) {
            if ($c['alias'] === $contestData['request']['alias']) {
                $scoreboard_url = $c['scoreboard_url'];
                $scoreboard_admin_url = $c['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call scoreboard api from the user
        $scoreboardResponse = ContestController::apiScoreboard(new Request(array(
            'auth_token' => $this->login($externalUser),
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url
        )));

        $this->assertEquals('0', $scoreboardResponse['ranking'][0]['total']['points']);

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = ContestController::apiScoreboard(new Request(array(
            'auth_token' => $this->login($externalUser),
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url
        )));

        $this->assertEquals('100', $scoreboardResponse['ranking'][0]['total']['points']);
    }

    /**
     * Test invalid token
     *
     * @expectedException ForbiddenAccessException
     */
    public function testScoreboardUrlInvalidToken() {
        // Create our user not added to the contest
        $externalUser = UserFactory::createUser();

        // Get a contest with 0% of scoreboard show percentage
        $contestData = ContestsFactory::createContest();

        // Call scoreboard api from the user
        $scoreboardResponse = ContestController::apiScoreboard(new Request(array(
            'auth_token' => $this->login($externalUser),
            'contest_alias' => $contestData['request']['alias'],
            'token' => 'invalid token'
        )));
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrlNoLogin() {
        // Get a private contest with 0% of scoreboard show percentage
        $contestData = ContestsFactory::createContest(null, 0);
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Create problem
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant, will submit 1 run
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        RunsFactory::gradeRun($runData);

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $response = ContestController::apiMyList(new Request(array(
            'auth_token' => $this->login($contestData['director'])
        )));

        // Look for our contest from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['results'] as $c) {
            if ($c['alias'] === $contestData['request']['alias']) {
                $scoreboard_url = $c['scoreboard_url'];
                $scoreboard_admin_url = $c['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call scoreboard api from the user
        $scoreboardResponse = ContestController::apiScoreboard(new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url
        )));

        $this->assertEquals('0', $scoreboardResponse['ranking'][0]['total']['points']);

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = ContestController::apiScoreboard(new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url
        )));

        $this->assertEquals('100', $scoreboardResponse['ranking'][0]['total']['points']);
    }
}
