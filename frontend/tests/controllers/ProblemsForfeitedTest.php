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

        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problem = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem($problem, $user, $login);
            RunsFactory::gradeRun($run);
        }

        $problemForfeited = ProblemsFactory::createProblem();
        \OmegaUp\DAO\ProblemsForfeited::create(new \OmegaUp\DAO\VO\ProblemsForfeited([
            'user_id' => $user->user_id,
            'problem_id' => $problemForfeited['problem']->problem_id,
        ]));

        $results = \OmegaUp\Controllers\ProblemForfeited::apiGetCounts(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals(1, $results['allowed']);
        $this->assertEquals(1, $results['seen']);
    }

    public function testGetSolution() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $problems = [];
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problems[] = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem(
                $problems[$i],
                $user,
                $login
            );
            RunsFactory::gradeRun($run);
        }

        $extraProblem = ProblemsFactory::createProblem();

        try {
            \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $extraProblem['problem']->alias,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'problemSolutionNotVisible');
        }

        $response = \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $extraProblem['problem']->alias,
            'forfeit_problem' => true,
        ]));
        $this->assertContains('`long long`', $response['solution']['markdown']);
        $this->assertTrue(
            \OmegaUp\DAO\ProblemsForfeited::isProblemForfeited(
                $extraProblem['problem'],
                \OmegaUp\DAO\Identities::findByUsername($user->username)
            )
        );
    }

    public function testGetSolutionForbiddenAccessException() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $problem = ProblemsFactory::createProblem()['problem'];
        try {
            \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'forfeit_problem' => true,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals(
                $e->getMessage(),
                'allowedSolutionsLimitReached'
            );
        }
    }
}
