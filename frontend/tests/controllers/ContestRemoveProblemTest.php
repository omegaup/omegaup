<?php

/**
 * Tests of the \OmegaUp\Controllers\Contest::apiRemoveProblem
 *
 * @author edhzsz
 */

class ContestRemoveProblemTest extends OmegaupTestCase {
    /**
     * Check in DB that a problem does not exist on a contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param \OmegaUp\Request $r
     */
    private function assertProblemRemovedFromContest(
        $problemData,
        $contestData
    ) {
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        // Get problem-contest and verify it does not exist
        $problemset_problems = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            $contest->problemset_id,
            $problem->problem_id
        );

        self::assertNull($problemset_problems);
    }

    /**
     * Check in DB for problem added to contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param \OmegaUp\Request $r
     */
    private function assertProblemExistsInContest($problemData, $contestData) {
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $problemset_problems = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            $contest->problemset_id,
            $problem->problem_id
        );

        self::assertNotNull($problemset_problems);
    }

    /**
     * Removes a problem without runs from a private contest.
     * Should not fail and problem should have been removed.
     */
    public function testRemoveProblemFromPrivateContest() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
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
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testRemoveInvalidProblemFromPrivateContest() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        // Create a new request
        $r = new \OmegaUp\Request(
            [
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => 'this problem doesnt exists'
            ]
        );

        $response = \OmegaUp\Controllers\Contest::apiRemoveProblem($r);
    }

    /**
     * Removes a problem from an inexistent contest.
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testRemoveProblemFromInvalidContest() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new \OmegaUp\Request(
            [
                'auth_token' => $login->auth_token,
                'contest_alias' => 'this contest doesnt exists',
                'problem_alias' => $problemData['request']['alias']
            ]
        );

        $response = \OmegaUp\Controllers\Contest::apiRemoveProblem($r);
    }

    /**
     * Removes a problem from contest while loged in with a user that
     * is not a contest admin.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testRemoveProblemPrivateContestNotBeingContestAdmin() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $login = OmegaupTestCase::login($contestant);

        $r = new \OmegaUp\Request(
            [
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['alias']
            ]
        );

        $response = \OmegaUp\Controllers\Contest::apiRemoveProblem($r);
    }

    /**
     * Converts a private contest into a public contest
     */
    private function makeContestPublic($contestData) {
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new \OmegaUp\Request(
            [
                'auth_token' =>  $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'admission_mode' => 'public' // Update public
            ]
        );

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);
    }

    /**
     * Removes the oldest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveOldestProblemFromPublicContestWithTwoProblems() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

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
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

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
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testRemoveProblemsFromPublicContestWithASingleProblem() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
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
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testRemoveAllProblemsFromPublicContestWithTwoProblems() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

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
     * Removes a problem with runs from a private contest while logged in
     * with a user that is sysadmin.
     */
    public function testRemoveProblemWithRunsFromPrivateContestBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRun($problemData, $contestData, $contestant);

        // Add the sysadmin role to the contest director
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $contestData['director']->user_id,
            'role_id' => \OmegaUp\Authorization::ADMIN_ROLE,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

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
     * while logged in with a user that is sysadmin.
     */
    public function testRemoveProblemWithAdminRunsFromContestBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        ['user' => $secondaryAdmin, 'identity' => $identity] = UserFactory::createUser();

        // Prepare request
        $login = OmegaupTestCase::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $secondaryAdmin->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Add secondary admin
        $response = \OmegaUp\Controllers\Contest::apiAddAdmin($r);

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
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testRemoveProblemWithRunsFromPrivateContest() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

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
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testRemoveProblemWithMixedRunsFromContestNotBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

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
     * Removes a problem with runs made outside the contest from a private contest
     * while logged in as Contest Admin
     *
     */
    public function testRemoveProblemWithRunsOutsideContestFromPrivateContest() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        // Create a run not related to the contest
        RunsFactory::createRunToProblem($problemData, $contestant);

        // Remove problem, should succeed.
        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );

        $this->assertEquals('ok', $response['status']);
    }

    /**
     * Removes a problem with runs made outside and inside the contest from a private contest
     * while logged in as Contest Admin. Should fail.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testRemoveProblemWithRunsOutsideAndInsideContestFromPrivateContest() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::createRun($problemData, $contestData, $contestant);

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }

    /**
     * Removes a problem with runs only from admins from a private contest
     * while logged in with a user that is not sysadmin.
     */
    public function testRemoveProblemWithMixedRunsFromContestBeingSysAdmin() {
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);

        RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        RunsFactory::createRun($problemData, $contestData, $contestant);

        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $contestData['director']->user_id,
            'role_id' => \OmegaUp\Authorization::ADMIN_ROLE,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        $response = ContestsFactory::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }
}
