<?php

/**
 * Description of DetailsContest
 *
 * @author joemmanuel
 */

class ContestDetailsTest extends OmegaupTestCase {
    /**
     * Insert problems in a contest
     *
     * @param type $contestData
     * @param type $numOfProblems
     * @return array array of problemData
     */
    private function insertProblemsInContest($contestData, $numOfProblems = 3) {
        // Create problems
        $problems = array();
        for ($i = 0; $i < $numOfProblems; $i++) {
            $problems[$i] = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problems[$i], $contestData);
        }

        return $problems;
    }

    /**
     * Checks the contest details response
     *
     * @param type $contestData
     * @param type $problems
     * @param type $response
     */
    private function assertContestDetails($contestData, $problems, $response) {
        // To validate, grab the contest object directly from the DB
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        // Assert we are getting correct data
        $this->assertEquals($contest->description, $response['description']);
        $this->assertEquals(Utils::GetPhpUnixTimestamp($contest->start_time), $response['start_time']);
        $this->assertEquals(Utils::GetPhpUnixTimestamp($contest->finish_time), $response['finish_time']);
        $this->assertEquals($contest->window_length, $response['window_length']);
        $this->assertEquals($contest->alias, $response['alias']);
        $this->assertEquals($contest->points_decay_factor, $response['points_decay_factor']);
        $this->assertEquals($contest->partial_score, $response['partial_score']);
        $this->assertEquals($contest->submissions_gap, $response['submissions_gap']);
        $this->assertEquals($contest->feedback, $response['feedback']);
        $this->assertEquals($contest->penalty, $response['penalty']);
        $this->assertEquals($contest->scoreboard, $response['scoreboard']);
        $this->assertEquals($contest->penalty_type, $response['penalty_type']);
        $this->assertEquals($contest->penalty_calc_policy, $response['penalty_calc_policy']);

        // Assert we have our problems
        $numOfProblems = count($problems);
        $this->assertEquals($numOfProblems, count($response['problems']));

        // Assert problem data
        $i = 0;
        foreach ($response['problems'] as $problem_array) {
            // Get problem from DB
            $problem = ProblemsDAO::getByAlias($problems[$i]['request']['alias']);

            // Assert data in DB
            $this->assertEquals($problem->title, $problem_array['title']);
            $this->assertEquals($problem->alias, $problem_array['alias']);
            $this->assertEquals($problem->validator, $problem_array['validator']);
            $this->assertEquals($problem->time_limit, $problem_array['time_limit']);
            $this->assertEquals($problem->memory_limit, $problem_array['memory_limit']);
            $this->assertEquals($problem->visits, $problem_array['visits']);
            $this->assertEquals($problem->submissions, $problem_array['submissions']);
            $this->assertEquals($problem->accepted, $problem_array['accepted']);
            $this->assertEquals($problem->order, $problem_array['order']);

            // Get points of problem from Contest-Problem relationship
            $problemInContest = ProblemsetProblemsDAO::getByPK($contest->problemset_id, $problem->problem_id);
            $this->assertEquals($problemInContest->points, $problem_array['points']);

            $i++;
        }
    }

    /**
     * Get contest details for a public contest
     */
    public function testGetContestDetailsValid() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get some problems into the contest
        $numOfProblems = 3;
        $problems = $this->insertProblemsInContest($contestData, $numOfProblems);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Assert the log is empty.
        $this->assertEquals(0, count(ProblemsetAccessLogDAO::search(array(
            'problemset_id' => $contestData['contest']->problemset_id,
            'user_id' => $contestant->user_id,
        ))));

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Explicitly join contest
        ContestController::apiOpen($r);

        // Call api
        $response = ContestController::apiDetails($r);

        $this->assertContestDetails($contestData, $problems, $response);

        // Assert the log is not empty.
        $this->assertEquals(1, count(ProblemsetAccessLogDAO::search(array(
            'problemset_id' => $contestData['contest']->problemset_id,
            'user_id' => $contestant->user_id,
        ))));
    }

    /**
     * Language filter works.
     */
    public function testGetContestDetailsWithLanguageFilter() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 1, null, 'c,cpp,java');

        // Get some problems into the contest
        $problemData = ProblemsFactory::createProblem(null, null, 1, null, 'cpp,java,py');
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Explicitly join contest
        ContestController::apiOpen($r);

        // Call api
        $response = ContestController::apiDetails($r);

        $this->assertEquals(1, count($response['problems']));
        // Verify that the allowed languages for the problem are the intersection of
        // the allowed languages.
        $this->assertEquals('cpp,java', $response['problems'][0]['languages']);
    }

    /**
     * Check that user in private list can view private contest
     */
    public function testShowValidPrivateContest() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get some problems into the contest
        $numOfProblems = 3;
        $problems = $this->insertProblemsInContest($contestData, $numOfProblems);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Call api
        $response = ContestController::apiDetails($r);

        $this->assertContestDetails($contestData, $problems, $response);
    }

    /**
     * Dont show private contests for users that are not in the private list
     *
     * @expectedException ForbiddenAccessException
     */
    public function testDontShowPrivateContestForAnyUser() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get some problems into the contest
        $numOfProblems = 3;
        $problems = $this->insertProblemsInContest($contestData, $numOfProblems);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Call api
        $response = ContestController::apiDetails($r);
    }

    /**
     * First access time should not change for Window Length contests
     */
    public function testAccessTimeIsAlwaysFirstAccessForWindowLength() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Convert contest into WindowLength one
        ContestsFactory::makeContestWindowLength($contestData, 20);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Explicitly join contest
        ContestController::apiOpen($r);

        // Call api
        $response = ContestController::apiDetails($r);

        // We need to grab the access time from the ContestUsers table
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $problemset_user = ProblemsetUsersDAO::getByPK($contestant->user_id, $contest->problemset_id);
        $firstAccessTime = $problemset_user->access_time;

        // Call API again, access time should not change
        $response = ContestController::apiDetails($r);

        $problemset_user = ProblemsetUsersDAO::getByPK($contestant->user_id, $contest->problemset_id);
        $this->assertEquals($firstAccessTime, $problemset_user->access_time);
    }

    /**
     * First access time should not change
     */
    public function testAccessTimeIsAlwaysFirstAccess() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Explicitly join contest
        ContestController::apiOpen($r);

        // Call api
        $response = ContestController::apiDetails($r);

        // We need to grab the access time from the ContestUsers table
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $problemset_user = ProblemsetUsersDAO::getByPK($contestant->user_id, $contest->problemset_id);
        $firstAccessTime = $problemset_user->access_time;

        // Call API again, access time should not change
        $response = ContestController::apiDetails($r);

        $problemset_user = ProblemsetUsersDAO::getByPK($contestant->user_id, $contest->problemset_id);
        $this->assertEquals($firstAccessTime, $problemset_user->access_time);
    }

    /**
     * First access time should not change
     */
    public function testAccessTimeIsAlwaysFirstAccessForPrivate() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Call api
        $response = ContestController::apiDetails($r);

        // We need to grab the access time from the ContestUsers table
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $problemset_user = ProblemsetUsersDAO::getByPK($contestant->user_id, $contest->problemset_id);
        $firstAccessTime = $problemset_user->access_time;

        // Call API again, access time should not change
        $response = ContestController::apiDetails($r);

        $problemset_user = ProblemsetUsersDAO::getByPK($contestant->user_id, $contest->problemset_id);
        $this->assertEquals($firstAccessTime, $problemset_user->access_time);
    }

    /**
     * Try to view a contest before it has started
     *
     * @expectedException PreconditionFailedException
     */
    public function testContestNotStartedYet() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Set contest to not started yet
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->start_time = Utils::GetTimeFromUnixTimestamp(Utils::GetPhpUnixTimestamp() + 30);
        ContestsDAO::save($contest);

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Call api
        $response = ContestController::apiDetails($r);
    }

    /**
     * Tests that user can get contest details with the scoreboard token
     */
    public function testDetailsUsingToken() {
        // Get a private contest
        $contestData = ContestsFactory::createContest(null, 0);

        // Create our user not added to the contest
        $externalUser = UserFactory::createUser();

        $originalContestAccessLog = ProblemsetAccessLogDAO::getAll();

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiMyList($r);
        unset($login);

        // Look for our contest from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['contests'] as $c) {
            if ($c['alias'] === $contestData['request']['alias']) {
                $scoreboard_url = $c['scoreboard_url'];
                $scoreboard_admin_url = $c['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call details using token
        $login = self::login($externalUser);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url,
        ));
        $detailsResponse = ContestController::apiDetails($r);
        unset($login);

        $this->assertContestDetails($contestData, array(), $detailsResponse);

        // Call details using admin token
        $login = self::login($externalUser);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url,
        ));
        $detailsResponse = ContestController::apiDetails($r);
        unset($login);

        $this->assertContestDetails($contestData, array(), $detailsResponse);

        // All requests were done using tokens, so the log must be identical.
        $contestAccessLog = ProblemsetAccessLogDAO::getAll();
        $this->assertEquals($originalContestAccessLog, $contestAccessLog);
    }

    /**
     * Tests admin details. For /contest/.../edit/.
     */
    public function testContestAdminDetails() {
        // Get a contest
        $contestData = ContestsFactory::createContest();
        $contestDirector = $contestData['director'];

        $login = self::login($contestDirector);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ));

        // Call api. This should fail.
        try {
            ContestController::apiDetails($r);
            $this->assertTrue(false, 'User that has not opened contest was able to see its details');
        } catch (ForbiddenAccessException $e) {
            // Pass
        }

        // Call admin api. This should succeed.
        $detailsResponse = ContestController::apiAdminDetails($r);
        $this->assertContestDetails($contestData, array(), $detailsResponse);
    }

    /**
     * Test accesing api with invalid scoreboard token. Should fail.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testDetailsUsingInvalidToken() {
        // Get a private contest
        $contestData = ContestsFactory::createContest(null, 0);

        // Create our user not added to the contest
        $externalUser = UserFactory::createUser();

        // Call details using token
        $login = self::login($externalUser);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => 'invalid token',
        ));
        $detailsResponse = ContestController::apiDetails($r);
    }

    /**
     * Tests that user can get contest details with the scoreboard token
     */
    public function testDetailsNoLoginUsingToken() {
        // Get a private contest
        $contestData = ContestsFactory::createContest(null, 0);

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiMyList($r);

        // Look for our contest from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['contests'] as $c) {
            if ($c['alias'] === $contestData['request']['alias']) {
                $scoreboard_url = $c['scoreboard_url'];
                $scoreboard_admin_url = $c['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call details using token
        $detailsResponse = ContestController::apiDetails(new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url
        )));

        $this->assertContestDetails($contestData, array(), $detailsResponse);

        // Call details using admin token
        $detailsResponse = ContestController::apiDetails(new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url
        )));

        $this->assertContestDetails($contestData, array(), $detailsResponse);
    }

    /**
     * Tests contest report used in OMI
     */
    public function testContestReport() {
        // Get a contest
        $contestData = ContestsFactory::createContest();
        $contestDirector = $contestData['director'];

        // Get a problem
        $problemData = ProblemsFactory::createProblemWithAuthor($contestDirector);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestants
        $contestants = array();
        array_push($contestants, UserFactory::createUser());
        array_push($contestants, UserFactory::createUser());
        array_push($contestants, UserFactory::createUser());

        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        // Create runs
        $runsData = array();
        $runsData[0] = RunsFactory::createRun($problemData, $contestData, $contestants[0]);
        $runsData[1] = RunsFactory::createRun($problemData, $contestData, $contestants[0]);
        $runsData[2] = RunsFactory::createRun($problemData, $contestData, $contestants[1]);
        $runsData[3] = RunsFactory::createRun($problemData, $contestData, $contestants[2]);
        $runDataDirector = RunsFactory::createRun($problemData, $contestData, $contestDirector);
        $runDataAdmin = RunsFactory::createRun($problemData, $contestData, $contestAdmin);

        // Grade the runs
        RunsFactory::gradeRun($runsData[0], 0, 'CE');
        RunsFactory::gradeRun($runsData[1]);
        RunsFactory::gradeRun($runsData[2], .9, 'PA');
        RunsFactory::gradeRun($runsData[3], 1, 'AC', 180);
        RunsFactory::gradeRun($runDataDirector, 1, 'AC', 120);
        RunsFactory::gradeRun($runDataAdmin, 1, 'AC', 110);

        // Create API
        $login = self::login($contestDirector);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ));
        $response = ContestController::apiReport($r);
        unset($login);

        $this->assertEquals($problemData['request']['alias'], $response['problems'][0]['alias']);

        foreach ($contestants as $contestant) {
            $found = false;
            foreach ($response['ranking'] as $rank) {
                if ($rank['username'] == $contestant->username) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * Check that user in private list can view private contest
     */
    public function testNoPrivilegeEscalationOccurs() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get some problems into the contest
        $numOfProblems = 3;
        $problems = $this->insertProblemsInContest($contestData, $numOfProblems);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api. This should fail.
        try {
            ContestController::apiDetails($r);
            $this->assertTrue(false, 'User with no access could see the contest');
        } catch (ForbiddenAccessException $e) {
            // Pass
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        // Get details from a problem in that contest. This should also fail.
        try {
            $problem_request = new Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problems[0]['request']['alias'],
            ]);

            ProblemController::apiDetails($problem_request);
            $this->assertTrue(false, 'User with no access could see the problem');
        } catch (ForbiddenAccessException $e) {
            // Pass
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        // Call api again. This should (still) fail.
        try {
            ContestController::apiDetails($r);
            $this->assertTrue(false, 'User with no access could see the contest');
        } catch (ForbiddenAccessException $e) {
            // Pass
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
