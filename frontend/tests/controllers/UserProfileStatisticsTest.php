<?php

/**
 * Test the apiProfileStatistics endpoint
 */
class UserProfileStatisticsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test basic profile statistics for a user with solved problems
     */
    public function testProfileStatisticsBasic() {
        // Create a test user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create problems with different difficulties
        $easyProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'title' => 'Easy Problem',
                'difficulty' => 0.3, // Easy
            ])
        );

        $mediumProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'title' => 'Medium Problem',
                'difficulty' => 1.5, // Medium
            ])
        );

        $hardProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'title' => 'Hard Problem',
                'difficulty' => 3.0, // Hard
            ])
        );

        $login = self::login($identity);

        // Submit AC runs for all problems
        $runEasy = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $easyProblem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runEasy);

        $runMedium = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $mediumProblem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runMedium);

        $runHard = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $hardProblem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runHard);

        // Call the API
        $response = \OmegaUp\Controllers\User::apiProfileStatistics(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        // Validate response structure
        $this->assertArrayHasKey('solved', $response);
        $this->assertArrayHasKey('attempting', $response);
        $this->assertArrayHasKey('difficulty', $response);
        $this->assertArrayHasKey('tags', $response);

        // Validate difficulty breakdown
        $this->assertArrayHasKey('easy', $response['difficulty']);
        $this->assertArrayHasKey('medium', $response['difficulty']);
        $this->assertArrayHasKey('hard', $response['difficulty']);
        $this->assertArrayHasKey('unlabelled', $response['difficulty']);

        // Verify per-difficulty counts are correct
        $this->assertSame(1, $response['difficulty']['easy']);
        $this->assertSame(1, $response['difficulty']['medium']);
        $this->assertSame(1, $response['difficulty']['hard']);
        $this->assertSame(0, $response['difficulty']['unlabelled']);

        // Should have 3 solved problems total
        $this->assertSame(3, $response['solved']);
    }

    /**
     * Test profile statistics with attempting (partial) problems
     */
    public function testProfileStatisticsWithAttempting() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);

        // Submit a partial run (not AC)
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 0.5, 'PA');

        $response = \OmegaUp\Controllers\User::apiProfileStatistics(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame(0, $response['solved']);
        $this->assertSame(1, $response['attempting']);
    }

    /**
     * Test profile statistics for user with no solved problems
     */
    public function testProfileStatisticsEmpty() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiProfileStatistics(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame(0, $response['solved']);
        $this->assertSame(0, $response['attempting']);
        $this->assertSame(0, $response['difficulty']['easy']);
        $this->assertSame(0, $response['difficulty']['medium']);
        $this->assertSame(0, $response['difficulty']['hard']);
        $this->assertSame(0, $response['difficulty']['unlabelled']);
        $this->assertEmpty($response['tags']);
    }

    /**
     * Test profile statistics for another user's profile
     */
    public function testProfileStatisticsOtherUser() {
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $loginUser2 = self::login($identity2);
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $identity2,
            $loginUser2
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);

        // User 1 views User 2's stats
        $loginUser1 = self::login($identity1);
        $response = \OmegaUp\Controllers\User::apiProfileStatistics(
            new \OmegaUp\Request([
                'auth_token' => $loginUser1->auth_token,
                'username' => $identity2->username,
            ])
        );

        $this->assertSame(1, $response['solved']);
    }

    /**
     * Test profile statistics respects private profile
     */
    public function testProfileStatisticsPrivateProfile() {
        // Create a private user
        ['identity' => $privateUser] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['isPrivate' => true])
        );
        ['identity' => $otherUser] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($otherUser);

        try {
            \OmegaUp\Controllers\User::apiProfileStatistics(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $privateUser->username,
                ])
            );
            $this->fail('Expected ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userProfileIsPrivate', $e->getMessage());
        }
    }

    /**
     * Test that tags distribution is returned correctly
     */
    public function testProfileStatisticsWithTags() {
        // Create a solver user
        ['identity' => $solver] = \OmegaUp\Test\Factories\User::createUser();

        // Create a problem - it will have default public tags
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $solverLogin = self::login($solver);

        // Submit AC run as the solver
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $solver,
            $solverLogin
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);

        $response = \OmegaUp\Controllers\User::apiProfileStatistics(
            new \OmegaUp\Request([
                'auth_token' => $solverLogin->auth_token,
            ])
        );

        // Verify tags structure is correct (array with name and count)
        $this->assertIsArray($response['tags']);
        // Note: Default problem tags like problemLevel* and problemRestricted*
        // are filtered out by the DAO, so we just verify the structure works
        foreach ($response['tags'] as $tag) {
            $this->assertArrayHasKey('name', $tag);
            $this->assertArrayHasKey('count', $tag);
            $this->assertIsString($tag['name']);
            $this->assertIsInt($tag['count']);
        }
    }

    /**
     * Test profile statistics throws NotFoundException for non-existent user
     */
    public function testProfileStatisticsUserNotFound() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiProfileStatistics(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => 'nonexistent_user_12345',
                ])
            );
            $this->fail('Expected NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('userNotExist', $e->getMessage());
        }
    }
}
