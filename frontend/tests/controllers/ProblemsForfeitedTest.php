<?php

/**
 * Tests for ProblemForfeitedController
 *
 * @author carlosabcs
 */

class ProblemsForfeitedTest extends OmegaupTestCase {
    const SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION = 10;

    public function testGetCounts() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        for ($i = 0; $i < static::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++) {
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
}
