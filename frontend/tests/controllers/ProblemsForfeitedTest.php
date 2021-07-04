<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for ProblemForfeitedController
 */

class ProblemsForfeitedTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetCounts() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }

        $problemForfeited = \OmegaUp\Test\Factories\Problem::createProblem();
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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problems = [];
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problems[$i],
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }

        $extraProblem = \OmegaUp\Test\Factories\Problem::createProblem();

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
        $this->assertStringContainsString(
            '`long long`',
            $response['solution']['markdown']
        );
        $this->assertTrue(
            \OmegaUp\DAO\ProblemsForfeited::isProblemForfeited(
                $extraProblem['problem'],
                \OmegaUp\DAO\Identities::findByUsername($identity->username)
            )
        );

        $results = \OmegaUp\Controllers\ProblemForfeited::apiGetCounts(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(1, $results['allowed']);
        $this->assertEquals(1, $results['seen']);
    }

    public function testGetSolutionForbiddenAccessException() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
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

        $results = \OmegaUp\Controllers\ProblemForfeited::apiGetCounts(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(0, $results['allowed']);
        $this->assertEquals(0, $results['seen']);
    }

    public function testGetNonexistentSolution() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problems = [];
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problems[$i],
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }

        $extraProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'imagetest.zip',
            ])
        );

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
        $this->assertNull($response['solution']);
        $this->assertFalse(
            \OmegaUp\DAO\ProblemsForfeited::isProblemForfeited(
                $extraProblem['problem'],
                \OmegaUp\DAO\Identities::findByUsername($identity->username)
            )
        );

        $results = \OmegaUp\Controllers\ProblemForfeited::apiGetCounts(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(1, $results['allowed']);
        $this->assertEquals(0, $results['seen']);
    }
}
