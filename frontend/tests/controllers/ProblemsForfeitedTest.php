<?php

/**
 * Tests for ProblemForfeitedController
 *
 * @author carlosabcs
 */

class ProblemsForfeitedTest extends OmegaupTestCase {
    public function testGetCounts() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        for ($i = 0;
             $i < ProblemForfeitedController::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION;
             $i++) {
            $problem = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem($problem, $user, $login);
            RunsFactory::gradeRun($run);
        }

        $problemForfeited = ProblemsFactory::createProblem();
        ProblemsForfeitedDAO::create(new ProblemsForfeited([
            'user_id' => $user->user_id,
            'problem_id' => $problemForfeited['problem']->problem_id,
        ]));

        $results = ProblemForfeitedController::apiGetCounts(new Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals(1, $results['allowed']);
        $this->assertEquals(1, $results['seen']);
    }

    public function testGetSolution() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $problems = [];
        for ($i = 0;
             $i < ProblemForfeitedController::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION;
             $i++) {
            $problems[] = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem($problems[$i], $user, $login);
            RunsFactory::gradeRun($run);
        }

        $extraProblem = ProblemsFactory::createProblem();
        $response = ProblemController::apiSolution(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $extraProblem['problem']->alias,
            'forfeit_problem' => true,
        ]));
        $this->assertContains('`long long`', $response['solution']['markdown']);
        $this->assertTrue(
            ProblemsForfeitedDAO::isProblemForfeited(
                $extraProblem['problem'],
                IdentitiesDAO::findByUsername($user->username)
            )
        );
    }

    public function testGetSolutionForbiddenAccessException() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $problem = ProblemsFactory::createProblem()['problem'];
        try {
            ProblemController::apiSolution(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'forfeit_problem' => true,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'allowedSolutionsLimitReached');
        }
    }
}
