<?php
/**
 * Tests for ProblemForfeitedController
 */

class ProblemsForfeitedTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetCounts() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $problemForfeited = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\DAO\ProblemsForfeited::create(new \OmegaUp\DAO\VO\ProblemsForfeited([
            'user_id' => $user->user_id,
            'problem_id' => $problemForfeited['problem']->problem_id,
        ]));

        $results = \OmegaUp\Controllers\ProblemForfeited::apiGetCounts(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertSame(5, $results['allowed']);
        $this->assertSame(1, $results['seen']);
    }

    public function testGetSolution() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $extraProblem = \OmegaUp\Test\Factories\Problem::createProblem();

        try {
            \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $extraProblem['problem']->alias,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'problemSolutionNotVisible');
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
        $this->assertSame(5, $results['allowed']);
        $this->assertSame(1, $results['seen']);
    }

    public function testGetSolutionForbiddenAccessException() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problems = [];

        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLUTIONS_ALLOWED_TO_SEE_PER_DAY; $i++
        ) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
            \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problems[$i]->alias,
                'forfeit_problem' => true,
            ]));
        }

        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
        try {
            \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'forfeit_problem' => true,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                $e->getMessage(),
                'allowedSolutionsLimitReached'
            );
        }

        $results = \OmegaUp\Controllers\ProblemForfeited::apiGetCounts(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertSame(5, $results['allowed']);
        $this->assertSame(5, $results['seen']);
    }

    public function testGetNonexistentSolution() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

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
            $this->assertSame($e->getMessage(), 'problemSolutionNotVisible');
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
        $this->assertSame(5, $results['allowed']);
        $this->assertSame(0, $results['seen']);
    }
}
