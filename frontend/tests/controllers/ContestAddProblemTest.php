<?php

/**
 * Description of AddProblemToContestTest
 *
 * @author joemmanuel
 */

class AddProblemToContestTest extends OmegaupTestCase {
    /**
     * Check in DB for problem added to contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param Request $r
     */
    public static function assertProblemAddedToContest($problemData, $contestData, $r) {
        // Get problem and contest from DB
        $problem = ProblemsDAO::getByAlias($problemData['request']['alias']);
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        // Get problem-contest and verify it
        $problemset_problems = ProblemsetProblemsDAO::getByPK($contest->problemset_id, $problem->problem_id);
        self::assertNotNull($problemset_problems);
        self::assertEquals($r['points'], $problemset_problems->points);
        self::assertEquals($r['order_in_contest'], $problemset_problems->order);
    }

    /**
     * Add a problem to contest with valid params
     */
    public function testAddProblemToContestPositive() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Build request
        $directorLogin = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]);

        // Call API
        $response = ContestController::apiAddProblem($r);

        // Validate
        $this->assertEquals('ok', $response['status']);

        self::assertProblemAddedToContest($problemData, $contestData, $r);
    }

    /**
     * Add a problem to contest with invalid params
     *
     * @expectedException InvalidParameterException
     */
    public function testAddProblemToContestInvalidProblem() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);
        // Build request
        $directorLogin = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => 'this problem doesnt exists',
            'points' => 100,
            'order_in_contest' => 1,
        ]);

        // Call API
        $response = ContestController::apiAddProblem($r);
    }

    /**
     * Add a problem to contest with invalid params
     *
     * @expectedException InvalidParameterException
     */
    public function testAddProblemToContestInvalidContest() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Create an empty request
        $directorLogin = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => 'invalid problem',
            'problem_alias' => $problemData['request']['alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]);

        // Call API
        $response = ContestController::apiAddProblem($r);
    }

    /**
     * Add a problem to contest with unauthorized user
     *
     * @expectedException ForbiddenAccessException
     */
    public function testAddProblemToContestWithUnauthorizedUser() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Log in as another random user
        $user = UserFactory::createUser();

        // Build request
        $userLogin = self::login($user);
        $r = new Request([
            'auth_token' => $userLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]);

        // Call API
        $response = ContestController::apiAddProblem($r);
    }

    /**
     * Add too many problems to a contest.
     */
    public function testAddTooManyProblemsToContest() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);
        $login = self::login($contestData['director']);

        for ($i = 0; $i < MAX_PROBLEMS_IN_CONTEST + 1; $i++) {
            // Get a problem
            $problemData = ProblemsFactory::createProblemWithAuthor($contestData['director'], $login);

            // Build request
            $r = new Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'problem_alias' => $problemData['request']['alias'],
                'points' => 100,
                'order_in_contest' => $i + 1
            ]);

            try {
                // Call API
                $response = ContestController::apiAddProblem($r);

                $this->assertLessThan(MAX_PROBLEMS_IN_CONTEST, $i);

                // Validate
                $this->assertEquals('ok', $response['status']);

                self::assertProblemAddedToContest($problemData, $contestData, $r);
            } catch (ApiException $e) {
                $this->assertEquals($e->getMessage(), 'contestAddproblemTooManyProblems');
                $this->assertEquals($i, MAX_PROBLEMS_IN_CONTEST);
            }
        }
    }

    /**
     * Attempt to add banned problems to a contest.
     */
    public function testAddBannedProblemToContest() {
        $contestData = ContestsFactory::createContest([]);
        $problemData = ProblemsFactory::createProblem(null, null, 1, $contestData['director']);
        $problem = $problemData['problem'];

        // Ban the problem.
        $problem->visibility = ProblemController::VISIBILITY_BANNED;
        ProblemsDAO::save($problem);

        $directorLogin = self::login($contestData['director']);
        try {
            ContestController::apiAddProblem(new Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['alias'],
                'points' => 100,
                'order_in_contest' => 1,
            ]));
            $this->fail('Banned problems should not be able to be added to a contest');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'problemIsBanned');
        }

        // Make it private. Now it should be possible to add it.
        $problem->visibility = ProblemController::VISIBILITY_PRIVATE;
        ProblemsDAO::save($problem);

        $r = new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]);
        $response = ContestController::apiAddProblem($r);
        $this->assertEquals('ok', $response['status']);
        self::assertProblemAddedToContest($problemData, $contestData, $r);
    }
}
