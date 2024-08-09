<?php
/**
 * Tests of the \OmegaUp\Controllers\Contest::apiRemoveProblem
 */

class ContestRemoveProblemTest extends \OmegaUp\Test\ControllerTestCase {
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
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
        $details = \OmegaUp\Controllers\Contest::apiAdminDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]),
        );
        $this->assertSame(false, $details['problems'][0]['has_submissions']);

        \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData,
            $contestData
        );
        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes an inexistent problem from a private contest.
     */
    public function testRemoveInvalidProblemFromPrivateContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Log in as contest director
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        try {
            \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => 'this_problem_does_not_exist'
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotFound', $e->getMessage());
            $this->assertSame('problem_alias', $e->parameter);
        }
    }

    /**
     * Removes a problem from an inexistent contest.
     */
    public function testRemoveProblemFromInvalidContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Log in as contest director
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        try {
            \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => 'this_contest_does_not_exist',
                'problem_alias' => $problemData['problem']->alias,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotFound', $e->getMessage());
            $this->assertSame('contest_alias', $e->parameter);
        }
    }

    /**
     * Removes a problem from contest while loged in with a user that
     * is not a contest admin.
     */
    public function testRemoveProblemPrivateContestNotBeingContestAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = \OmegaUp\Test\ControllerTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['problem']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('cannotRemoveProblem', $e->getMessage());
        }
    }

    /**
     * Converts a private contest into a public contest
     */
    private function makeContestPublic($contestData) {
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        $r = new \OmegaUp\Request(
            [
                'auth_token' =>  $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'admission_mode' => 'public', // Update public
                'languages' => 'c11-gcc',
            ]
        );

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);
    }

    /**
     * Removes the oldest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveOldestProblemFromPublicContestWithTwoProblems() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                [
                    'admissionMode' => 'private',
                    'languages' => ['c11-gcc'],
                ]
            )
        );

        $problemData1 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData1,
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData2,
            $contestData
        );

        $this->makeContestPublic($contestData);

        $response = \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData1,
            $contestData
        );

        // Validate
        $this->assertSame('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData1, $contestData);
        $this->assertProblemExistsInContest($problemData2, $contestData);
    }

    /**
     * Removes the newest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveNewestProblemFromPublicContestWithTwoProblems() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        $problemData1 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData1,
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData2,
            $contestData
        );

        $this->makeContestPublic($contestData);

        $response = \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData2,
            $contestData
        );

        // Validate
        $this->assertSame('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData2, $contestData);
        $this->assertProblemExistsInContest($problemData1, $contestData);
    }

    /**
     * Removes a single problem from a public contest.
     */
    public function testRemoveProblemsFromPublicContestWithASingleProblem() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                [
                    'admissionMode' => 'private',
                    'languages' => ['c11-gcc'],
                ]
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        $this->makeContestPublic($contestData);

        try {
            \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
                $problemData,
                $contestData
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'contestPublicRequiresProblem',
                $e->getMessage()
            );
        }
    }

    /**
     * Removes all problems from a public contest.
     */
    public function testRemoveAllProblemsFromPublicContestWithTwoProblems() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        $problemData1 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData1,
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData2,
            $contestData
        );

        $this->makeContestPublic($contestData);

        $response = \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData1,
            $contestData
        );
        $this->assertSame('ok', $response['status']);

        try {
            \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
                $problemData2,
                $contestData
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'contestPublicRequiresProblem',
                $e->getMessage()
            );
        }
    }

    /**
     * Removes a problem with runs from a private contest while logged in
     * with a user that is sysadmin.
     */
    public function testRemoveProblemWithRunsFromPrivateContestBeingSysAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        // Add the sysadmin role to the contest director
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $contestData['director']->user_id,
            'role_id' => \OmegaUp\Authorization::ADMIN_ROLE,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        $response = \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData,
            $contestData
        );

        // Validate
        $this->assertSame('ok', $response['status']);
        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes a problem with runs only from admins from a private contest
     * while logged in with a user that is sysadmin.
     */
    public function testRemoveProblemWithAdminRunsFromContestBeingSysAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        ['identity' => $secondaryIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare request
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $secondaryIdentityAdmin->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Add secondary admin
        $response = \OmegaUp\Controllers\Contest::apiAddAdmin($r);

        // Add runs to the problem created by the contest admins
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $secondaryIdentityAdmin
        );

        // remove the problem from the contest
        $response = \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData,
            $contestData
        );

        // Validate
        $this->assertSame('ok', $response['status']);
        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes a problem with runs from a private contest while loged in
     * with a user that is not sysadmin.
     */
    public function testRemoveProblemWithRunsFromPrivateContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
        $details = \OmegaUp\Controllers\Contest::apiAdminDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]),
        );
        $this->assertSame(true, $details['problems'][0]['has_submissions']);

        try {
            \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
                $problemData,
                $contestData
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'cannotRemoveProblemWithSubmissions',
                $e->getMessage()
            );
        }
    }

    /**
     * Removes a problem with runs only from admins from a private contest while
     * loged in with a user that is not sysadmin.
     */
    public function testRemoveProblemWithMixedRunsFromContestNotBeingSysAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        try {
            \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
                $problemData,
                $contestData
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'cannotRemoveProblemWithSubmissions',
                $e->getMessage()
            );
        }
    }

    /**
     * Removes a problem with runs made outside the contest from a private contest
     * while logged in as Contest Admin
     *
     */
    public function testRemoveProblemWithRunsOutsideContestFromPrivateContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // Create a run not related to the contest
        \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );

        // Remove problem, should succeed.
        $response = \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData,
            $contestData
        );

        $this->assertSame('ok', $response['status']);
    }

    /**
     * Removes a problem with runs made outside and inside the contest from a private contest
     * while logged in as Contest Admin. Should fail.
     */
    public function testRemoveProblemWithRunsOutsideAndInsideContestFromPrivateContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        try {
            \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
                $problemData,
                $contestData
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'cannotRemoveProblemWithSubmissions',
                $e->getMessage()
            );
        }
    }

    /**
     * Removes a problem with runs only from admins from a private contest
     * while logged in with a user that is not sysadmin.
     */
    public function testRemoveProblemWithMixedRunsFromContestBeingSysAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $contestData['director']
        );
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $contestData['director']->user_id,
            'role_id' => \OmegaUp\Authorization::ADMIN_ROLE,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData,
            $contestData
        );
    }
}
