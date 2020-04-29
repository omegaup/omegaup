<?php

/**
 * Description of DetailsProblem
 *
 * @author joemmanuel
 */

class ProblemDetailsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testViewProblemInAContestDetailsValid() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user to be the author
        ['user' => $authorUser, 'identity' => $authorIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 2,
            'author' => $authorIdentity
        ]));

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Explicitly join contest
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        $response = \OmegaUp\Controllers\Problem::apiDetails($r);

        // Get problem and contest from DB to check it
        $problemDAO = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );
        $contestDAO = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $contestantDAO = \OmegaUp\DAO\Users::FindByUsername(
            $identity->username
        );

        // Assert data
        $this->assertEquals($problemDAO->title, $response['title']);
        $this->assertEquals($problemDAO->alias, $response['alias']);
        $this->assertEquals(100, $response['points']);
        $this->assertEquals(
            $response['problemsetter']['username'],
            $authorIdentity->username
        );
        $this->assertEquals(
            $response['problemsetter']['name'],
            $authorIdentity->name
        );
        $this->assertEquals($problemDAO->source, $response['source']);
        $this->assertStringContainsString(
            '# Entrada',
            $response['statement']['markdown']
        );
        $this->assertEquals($problemDAO->order, $response['order']);
        $this->assertEquals(0, $response['score']);

        // Default data
        $this->assertEquals(0, $problemDAO->visits);
        $this->assertEquals(0, $problemDAO->submissions);
        $this->assertEquals(0, $problemDAO->accepted);
        $this->assertEquals(0, $problemDAO->difficulty);

        // Verify that we have an empty array of runs
        $this->assertEquals(0, count($response['runs']));

        // Verify that problem was marked as Opened
        $problemOpened = \OmegaUp\DAO\ProblemsetProblemOpened::getByPK(
            $contestDAO->problemset_id,
            $problemDAO->problem_id,
            $contestantDAO->main_identity_id
        );
        $this->assertNotNull($problemOpened);

        // Verify open time
        $this->assertEquals(
            \OmegaUp\Time::get(),
            $problemOpened->open_time->time
        );
    }

    /**
     * Common code for testing the statement's source.
     */
    public function internalViewProblemStatement($type, $expected_text) {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Call api
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'statement_type' => $type,
        ]));

        // Assert data
        $this->assertStringContainsString(
            $expected_text,
            $response['statement']['markdown']
        );
    }

    /**
     * Problem statement is returned in Markdown.
     */
    public function testViewProblemStatementMarkdown() {
        $this->internalViewProblemStatement('markdown', '# Entrada');
    }

    public function testViewProblemStatementInvalidType() {
        try {
            $this->internalViewProblemStatement('not_html_or_markdown', '');
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals('invalidStatementType', $e->getMessage());
        }
    }

    public function testProblemDetailsNotInContest() {
        // Get 1 problem public
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 2
        ]));

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Call api
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertEquals(
            $response['alias'],
            $problemData['request']['problem_alias']
        );
        $this->assertEquals(
            $response['commit'],
            $problemData['problem']->commit
        );
        $this->assertEquals(
            $response['version'],
            $problemData['problem']->current_version
        );
    }

    /**
     * User can't see problem details
     */
    public function testPrivateProblemDetailsOutsideOfContest() {
        // Get 1 problem public
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 0
        ]));

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('problemIsPrivate', $e->getMessage());
        }
    }

    /**
     * Non-user can't see problem details
     */
    public function testPrivateProblemDetailsAnonymousOutsideOfContest() {
        // Get 1 problem public
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 0
        ]));

        try {
            \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('problemIsPrivate', $e->getMessage());
        }
    }

    /**
     * Best score is returned
     */
    public function testScoreInDetailsOutsideContest() {
        // Create problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create 2 runs, 100 and 50.
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $runDataPA = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPA, 0.5, 'PA');

        // Call API
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias']
        ]));

        $this->assertEquals(100.00, $response['score']);
    }

    /**
     * Best score is returned, problem inside a contest
     */
    public function testScoreInDetailsInsideContest() {
        // Create problem and contest
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create 2 runs, 100 and 50.
        $runDataOutsideContest = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $runDataInsideContest = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataOutsideContest);
        \OmegaUp\Test\Factories\Run::gradeRun($runDataInsideContest, 0.5, 'PA');

        // Call API
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'contest_alias' => $contestData['request']['alias']
        ]));

        $this->assertEquals(50.00, $response['score']);
    }

    /**
     * Problem details in a contest should only show runs sent in the contest.
     */
    public function testViewProblemHasCorrectRuns() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $runDataOutOfContest = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $runDataInContest = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataOutOfContest);
        \OmegaUp\Test\Factories\Run::gradeRun($runDataInContest);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        $response = \OmegaUp\Controllers\Problem::apiDetails($r);

        // Verify that the only run returned is the one that was sent in the
        // contest.
        $this->assertEquals(1, count($response['runs']));
        $this->assertEquals(
            $runDataInContest['response']['guid'],
            $response['runs'][0]['guid']
        );
    }

    /**
     * Solvers are returned only outside of a contests.
     */
    public function testShowSolvers() {
        // Create problem and contest
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create an accepted run.
        $runDataInsideContest = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataInsideContest);

        // Call API
        $login = self::login($identity);
        {
            $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'contest_alias' => $contestData['request']['alias'],
                'show_solvers' => true,
            ]));
            $this->assertArrayNotHasKey('solvers', $response);
        }
        {
            $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'show_solvers' => true,
            ]));
            $this->assertCount(1, $response['solvers']);
            $this->assertEquals(
                $identity->username,
                $response['solvers'][0]['username']
            );
        }
    }

    /**
     * Solutions that don't exist don't cause an exception.
     */
    public function testShowSolutionInexistent() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'imagetest.zip',
        ]));
        $login = self::login($problemData['author']);
        {
            $response = \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->assertEmpty($response['solution']['markdown']);
        }
    }

    /**
     * Solutions can be viewed by a problem admin.
     */
    public function testShowSolutionByAdmin() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($problemData['author']);
        {
            $response = \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->assertStringContainsString(
                '`long long`',
                $response['solution']['markdown']
            );
        }
    }

    /**
     * Solutions can be viewed by a user that has solved the problem.
     */
    public function testShowSolutionBySolver() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        try {
            $login = self::login($identity);
            \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'forfeit_problem' => true,
            ]));
            $this->fail('User should not have been able to view solution');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals(
                'allowedSolutionsLimitReached',
                $e->getMessage()
            );
        }

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        {
            $login = self::login($identity);
            $response = \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->assertStringContainsString(
                '`long long`',
                $response['solution']['markdown']
            );
        }
    }

    public function testAuthorizationController() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $result = \OmegaUp\Controllers\Authorization::apiProblem(new \OmegaUp\Request([
            'token' => OMEGAUP_GITSERVER_SECRET_TOKEN,
            'username' => $identity->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertTrue($result['has_solved']);
        $this->assertFalse($result['is_admin']);
        $this->assertTrue($result['can_view']);
        $this->assertFalse($result['can_edit']);

        $result = \OmegaUp\Controllers\Authorization::apiProblem(new \OmegaUp\Request([
            'token' => OMEGAUP_GITSERVER_SECRET_TOKEN,
            'username' => $problemData['author']->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertFalse($result['has_solved']);
        $this->assertTrue($result['is_admin']);
        $this->assertTrue($result['can_view']);
        $this->assertTrue($result['can_edit']);
    }
}
