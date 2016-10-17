<?php

/**
 * Tests of the ContestController::apiRemoveProblem
 *
 * @author edhzsz
 */

class ContestRemoveProblemTest extends OmegaupTestCase {
    /**
     * Check in DB that a problem does not exist on a contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param Request $r
     */
    private function assertProblemRemovedFromContest($problemData, $contestData) {
        $problem = ProblemsDAO::getByAlias($problemData['request']['alias']);
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        // Get problem-contest and verify it does not exist
        $contest_problems = ContestProblemsDAO::getByPK(
            $contest->contest_id,
            $problem->problem_id
        );

        self::assertNull($contest_problems);
    }

    /**
     * Check in DB for problem added to contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param Request $r
     */
    private function assertProblemExistsInContest($problemData, $contestData) {
        $problem = ProblemsDAO::getByAlias($problemData['request']['alias']);
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        $contest_problems = ContestProblemsDAO::getByPK(
            $contest->contest_id,
            $problem->problem_id
        );

        self::assertNotNull($contest_problems);
    }

    /**
     * Removes a problem without runs from a private contest.
     * Should not fail and problem should have been removed.
     */
    public function testRemoveProblemFromPrivateContest() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );

        // Validate
        $this->assertEquals('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes an inexistent problem from a private contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveInvalidProblemFromPrivateContest() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        // Create a new request
        $r = new Request(
            array(
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => 'this problem doesnt exists'
            )
        );

        $response = ContestController::apiRemoveProblem($r);
    }

    /**
     * Removes a problem from an inexistent contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveProblemFromInvalidContest() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new Request(
            array(
                'auth_token' => $login->auth_token,
                'contest_alias' => 'this contest doesnt exists',
                'problem_alias' => $problemData['request']['alias']
            )
        );

        $response = ContestController::apiRemoveProblem($r);
    }

    /**
     * Removes a problem from contest while loged in with a user that
     * is not a contest admin.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testRemoveProblemPrivateContestNotBeingContestAdmin() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        $contestant = UserFactory::createUser();

        $login = OmegaupTestCase::login($contestant);

        $r = new Request(
            array(
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['alias']
            )
        );

        $response = ContestController::apiRemoveProblem($r);
    }

    /**
     * Converts a private contest into a public contest
     */
    private function makeContestPublic($contestData) {
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new Request(
            array(
                'auth_token' =>  $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'public' => 1 // Update public
            )
        );

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Removes the oldest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveOldestProblemFromPublicContestWithTwoProblems() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        $problemData1 = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemData1, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        $this->makeContestPublic($contestData);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData1,
            $contestData
        );

        // Validate
        $this->assertEquals('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData1, $contestData);
        $this->assertProblemExistsInContest($problemData2, $contestData);
    }

    /**
     * Removes the newest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveNewestProblemFromPublicContestWithTwoProblems() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        $problemData1 = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemData1, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        $this->makeContestPublic($contestData);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData2,
            $contestData
        );

        // Validate
        $this->assertEquals('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData2, $contestData);
        $this->assertProblemExistsInContest($problemData1, $contestData);
    }

    /**
     * Removes a single problem from a public contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveProblemsFromPublicContestWithASingleProblem() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $this->makeContestPublic($contestData);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }

    /**
     * Removes all problems from a public contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveAllProblemsFromPublicContestWithTwoProblems() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        $problemData1 = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemData1, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        $this->makeContestPublic($contestData);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData1,
            $contestData
        );

        // Validate
        $this->assertEquals('ok', $response['status']);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData2,
            $contestData
        );
    }

    /**
     * Removes a problem with runs from a private contest while loged in
     * with a user that is sysadmin.
     *
     */
    public function testRemoveProblemWithRunsFromPrivateContestBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRun($problemData, $contestData, $contestant);

        // Add the sysadmin role to the contest director
        $userRoles = new UserRoles(array(
            'user_id' => $contestData['director']->user_id,
            'role_id' => ADMIN_ROLE,
            'contest_id' => 0,
        ));
        UserRolesDAO::save($userRoles);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );

        // Validate
        $this->assertEquals('ok', $response['status']);
        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes a problem with runs only from admins from a private contest
     * while loged in with a user that is sysadmin.
     */
    public function testRemoveProblemWithAdminRunsFromContestBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $secondaryAdmin = UserFactory::createUser();

        // Prepare request
        $login = OmegaupTestCase::login($contestData['director']);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $secondaryAdmin->username,
            'contest_alias' => $contestData['request']['alias'],
        ));

        // Add secondary admin
        $response = ContestController::apiAddAdmin($r);

        // Add runs to the problem created by the contest admins
        RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        RunsFactory::createRun($problemData, $contestData, $secondaryAdmin);

        // remove the problem from the contest
        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );

        // Validate
        $this->assertEquals('ok', $response['status']);
        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes a problem with runs from a private contest while loged in
     * with a user that is not sysadmin.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testRemoveProblemWithRunsFromPrivateContest() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRun($problemData, $contestData, $contestant);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }

    /**
     * Removes a problem with runs only from admins from a private contest while
     * loged in with a user that is not sysadmin.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testRemoveProblemWithMixedRunsFromContestNotBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        RunsFactory::createRun($problemData, $contestData, $contestant);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }

    /**
     * Removes a problem with runs only from admins from a private contest
     * while loged in with a user that is not sysadmin.
     */
    public function testRemoveProblemWithMixedRunsFromContestBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(null, 0 /* private */);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        RunsFactory::createRun($problemData, $contestData, $contestant);

        $userRoles = new UserRoles(array(
            'user_id' => $contestData['director']->user_id,
            'role_id' => ADMIN_ROLE,
            'contest_id' => 0,
        ));
        UserRolesDAO::save($userRoles);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }
}
