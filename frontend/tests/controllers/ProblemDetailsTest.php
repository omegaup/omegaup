<?php

/**
 * Description of DetailsProblem
 *
 * @author joemmanuel
 */

class ProblemDetailsTest extends OmegaupTestCase {
    /**
     *
     */
    public function testViewProblemInAContestDetailsValid() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user to be the author
        $authorUser = UserFactory::createUser();
        $authorIdentity = IdentitiesDAO::getByPK($authorUser->main_identity_id);

        // Get a problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $authorUser
        ]));

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Explicitly join contest
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        ContestController::apiOpen($r);

        // Call api
        $response = ProblemController::apiDetails($r);

        // Get problem and contest from DB to check it
        $problemDAO = ProblemsDAO::getByAlias($problemData['request']['problem_alias']);
        $contestDAO = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contestantDAO = UsersDAO::FindByUsername($contestant->username);

        // Assert data
        $this->assertEquals($response['title'], $problemDAO->title);
        $this->assertEquals($response['alias'], $problemDAO->alias);
        $this->assertEquals($response['points'], 100);
        $this->assertEquals(
            $response['problemsetter']['username'],
            $authorUser->username
        );
        $this->assertEquals(
            $response['problemsetter']['name'],
            $authorIdentity->name
        );
        $this->assertEquals($response['source'], $problemDAO->source);
        $this->assertContains('# Entrada', $response['statement']['markdown']);
        $this->assertEquals($response['order'], $problemDAO->order);
        $this->assertEquals($response['score'], 0);

        // Default data
        $this->assertEquals(0, $problemDAO->visits);
        $this->assertEquals(0, $problemDAO->submissions);
        $this->assertEquals(0, $problemDAO->accepted);
        $this->assertEquals(0, $problemDAO->difficulty);

        // Verify that we have an empty array of runs
        $this->assertEquals(0, count($response['runs']));

        // Verify that problem was marked as Opened
        $problem_opened = ProblemsetProblemOpenedDAO::getByPK(
            $contestDAO->problemset_id,
            $problemDAO->problem_id,
            $contestantDAO->main_identity_id
        );
        $this->assertNotNull($problem_opened);

        // Verify open time
        $this->assertEquals(Utils::GetPhpUnixTimestamp(), Utils::GetPhpUnixTimestamp($problem_opened->open_time));
    }

    /**
     * Common code for testing the statement's source.
     */
    public function internalViewProblemStatement($type, $expected_text) {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Call api
        $login = self::login($contestant);
        $response = ProblemController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'statement_type' => $type,
        ]));

        // Assert data
        $this->assertContains($expected_text, $response['statement']['markdown']);
    }

    /**
     * Problem statement is returned in Markdown.
     */
    public function testViewProblemStatementMarkdown() {
        $this->internalViewProblemStatement('markdown', '# Entrada');
    }

    /**
     * @expectedException NotFoundException
     */
    public function testViewProblemStatementInvalidType() {
        $this->internalViewProblemStatement('not_html_or_markdown', '');
    }

    public function testProblemDetailsNotInContest() {
        // Get 1 problem public
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1
        ]));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Call api
        $login = self::login($contestant);
        $response = ProblemController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertEquals($response['alias'], $problemData['request']['problem_alias']);
        $this->assertEquals($response['commit'], $problemData['problem']->commit);
        $this->assertEquals($response['version'], $problemData['problem']->current_version);
    }

    /**
     * User can't see problem details
     *
     * @expectedException ForbiddenAccessException
     */
    public function testPrivateProblemDetailsOutsideOfContest() {
        // Get 1 problem public
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Call api
        $login = self::login($contestant);
        $response = ProblemController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
    }

    /**
     * Non-user can't see problem details
     *
     * @expectedException ForbiddenAccessException
     */
    public function testPrivateProblemDetailsAnonymousOutsideOfContest() {
        // Get 1 problem public
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]));

        // Call api
        $response = ProblemController::apiDetails(new Request([
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
    }

    /**
     * Best score is returned
     */
    public function testScoreInDetailsOutsideContest() {
        // Create problem
        $problemData = ProblemsFactory::createProblem();

        // Create contestant
        $contestant = UserFactory::createUser();

        // Create 2 runs, 100 and 50.
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        $runDataPA = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);
        RunsFactory::gradeRun($runDataPA, 0.5, 'PA');

        // Call API
        $login = self::login($contestant);
        $response = ProblemController::apiDetails(new Request([
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
        $problemData = ProblemsFactory::createProblem();
        $contestData = ContestsFactory::createContest();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create contestant
        $contestant = UserFactory::createUser();

        // Create 2 runs, 100 and 50.
        $runDataOutsideContest = RunsFactory::createRunToProblem($problemData, $contestant);
        $runDataInsideContest = RunsFactory::createRun($problemData, $contestData, $contestant);
        RunsFactory::gradeRun($runDataOutsideContest);
        RunsFactory::gradeRun($runDataInsideContest, 0.5, 'PA');

        // Call API
        $login = self::login($contestant);
        $response = ProblemController::apiDetails(new Request([
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
        $contestData = ContestsFactory::createContest();

        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        $runDataOutOfContest = RunsFactory::createRunToProblem(
            $problemData,
            $contestant
        );
        $runDataInContest = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestant
        );
        RunsFactory::gradeRun($runDataOutOfContest);
        RunsFactory::gradeRun($runDataInContest);

        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        $response = ProblemController::apiDetails($r);

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
        $problemData = ProblemsFactory::createProblem();
        $contestData = ContestsFactory::createContest();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create contestant
        $contestant = UserFactory::createUser();

        // Create an accepted run.
        $runDataInsideContest = RunsFactory::createRun($problemData, $contestData, $contestant);
        RunsFactory::gradeRun($runDataInsideContest);

        // Call API
        $login = self::login($contestant);
        {
            $response = ProblemController::apiDetails(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'contest_alias' => $contestData['request']['alias'],
                'show_solvers' => true,
            ]));
            $this->assertArrayNotHasKey('solvers', $response);
        }
        {
            $response = ProblemController::apiDetails(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'show_solvers' => true,
            ]));
            $this->assertCount(1, $response['solvers']);
            $this->assertEquals($contestant->username, $response['solvers'][0]['username']);
        }
    }

    /**
     * Solutions that don't exist don't cause an exception.
     */
    public function testShowSolutionInexistent() {
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'imagetest.zip',
        ]));
        $login = self::login($problemData['author']);
        {
            $response = ProblemController::apiSolution(new Request([
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
        $problemData = ProblemsFactory::createProblem();
        $login = self::login($problemData['author']);
        {
            $response = ProblemController::apiSolution(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->assertContains('`long long`', $response['solution']['markdown']);
        }
    }

    /**
     * Solutions can be viewed by a user that has solved the problem.
     */
    public function testShowSolutionBySolver() {
        $problemData = ProblemsFactory::createProblem();

        $contestant = UserFactory::createUser();

        try {
            $login = self::login($contestant);
            ProblemController::apiSolution(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->fail('User should not have been able to view solution');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('problemSolutionNotVisible', $e->getMessage());
        }

        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        {
            $login = self::login($contestant);
            $response = ProblemController::apiSolution(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->assertContains('`long long`', $response['solution']['markdown']);
        }
    }

    public function testAuthorizationController() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        $result = AuthorizationController::apiProblem(new Request([
            'token' => OMEGAUP_GRADER_SECRET,
            'username' => $contestant->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertTrue($result['has_solved']);
        $this->assertFalse($result['is_admin']);
        $this->assertTrue($result['can_view']);
        $this->assertFalse($result['can_edit']);

        $result = AuthorizationController::apiProblem(new Request([
            'token' => OMEGAUP_GRADER_SECRET,
            'username' => $problemData['author']->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertFalse($result['has_solved']);
        $this->assertTrue($result['is_admin']);
        $this->assertTrue($result['can_view']);
        $this->assertTrue($result['can_edit']);
    }
}
