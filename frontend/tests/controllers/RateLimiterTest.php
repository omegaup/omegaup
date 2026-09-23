<?php

/**
 * Tests for rate limiting on content creation endpoints.
 *
 * @see https://github.com/omegaup/omegaup/issues/8975
 */
class RateLimiterTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        // Re-enable rate limiting for these tests (disabled by default
        // in ControllerTestCase::setUp to avoid breaking other tests).
        \OmegaUp\RateLimiter::setForTesting(true);

        // Problem tests need the file uploader mock
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    /**
     * Test that Group::apiCreate is rate limited to 5 per hour.
     */
    public function testGroupCreateRateLimit(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create 5 groups — all should succeed
        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\Group::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => "test-group-rl-{$i}",
                    'name' => "Test Group RL {$i}",
                    'description' => "Rate limit test group {$i}",
                ])
            );
        }

        // The 6th should fail with RateLimitExceededException
        try {
            \OmegaUp\Controllers\Group::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => 'test-group-rl-excess',
                    'name' => 'Test Group RL Excess',
                    'description' => 'This should fail',
                ])
            );
            $this->fail('Expected RateLimitExceededException');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test that admin users are exempt from rate limiting.
     */
    public function testGroupCreateRateLimitAdminExempt(): void {
        ['identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($adminIdentity);

        // Admin should be able to create more than 5 groups
        for ($i = 0; $i < 7; $i++) {
            \OmegaUp\Controllers\Group::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => "admin-group-rl-{$i}",
                    'name' => "Admin Group RL {$i}",
                    'description' => "Admin rate limit test {$i}",
                ])
            );
        }
        // If we get here, no exception was thrown — test passes
        $this->assertTrue(true);
    }

    /**
     * Test that the rate limit resets after the time window.
     */
    public function testGroupCreateRateLimitResetsAfterWindow(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Use 5/5 of the limit
        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\Group::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => "group-window-a-{$i}",
                    'name' => "Group Window A {$i}",
                    'description' => "Window test {$i}",
                ])
            );
        }

        // Fast-forward time by 1 hour + 1 second
        $futureTime = \OmegaUp\Time::get() + 3601;
        \OmegaUp\Time::setTimeForTesting($futureTime);

        // Should succeed again after window reset
        \OmegaUp\Controllers\Group::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'alias' => 'group-window-b-0',
                'name' => 'Group Window B 0',
                'description' => 'After window reset',
            ])
        );
        // If we get here, no exception was thrown — test passes
        $this->assertTrue(true);

        // Clean up time mock
        \OmegaUp\Time::setTimeForTesting(null);
    }

    /**
     * Test that TeamsGroup::apiCreate is rate limited.
     */
    public function testTeamsGroupCreateRateLimit(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\TeamsGroup::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => "test-tg-rl-{$i}",
                    'name' => "Test TeamsGroup RL {$i}",
                    'description' => "Rate limit test {$i}",
                ])
            );
        }

        try {
            \OmegaUp\Controllers\TeamsGroup::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => 'test-tg-rl-excess',
                    'name' => 'Test TeamsGroup RL Excess',
                    'description' => 'This should fail',
                ])
            );
            $this->fail('Expected RateLimitExceededException');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test that School::apiCreate is rate limited.
     */
    public function testSchoolCreateRateLimit(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => "Rate Limit School {$i}",
                ])
            );
        }

        try {
            \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => 'Rate Limit School Excess',
                ])
            );
            $this->fail('Expected RateLimitExceededException');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test that different users have independent rate limits.
     */
    public function testRateLimitIsPerUser(): void {
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $login1 = self::login($identity1);
        $login2 = self::login($identity2);

        // User 1 uses up their limit
        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\Group::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login1->auth_token,
                    'alias' => "user1-group-{$i}",
                    'name' => "User1 Group {$i}",
                    'description' => "Test {$i}",
                ])
            );
        }

        // User 2 should still be able to create groups
        \OmegaUp\Controllers\Group::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login2->auth_token,
                'alias' => 'user2-group-0',
                'name' => 'User2 Group 0',
                'description' => 'Independent limit test',
            ])
        );
        $this->assertTrue(true);
    }

    /**
     * Test that Course::apiCreate is rate limited to 5 per hour.
     */
    public function testCourseCreateRateLimit(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create 5 courses — all should succeed
        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\Course::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => "test-course-rl-{$i}",
                    'name' => "Test Course RL {$i}",
                    'description' => "Rate limit test course {$i}",
                    'start_time' => \OmegaUp\Time::get(),
                ])
            );
        }

        // The 6th should fail
        try {
            \OmegaUp\Controllers\Course::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'alias' => 'test-course-rl-excess',
                    'name' => 'Test Course RL Excess',
                    'description' => 'This should fail',
                    'start_time' => \OmegaUp\Time::get(),
                ])
            );
            $this->fail('Expected RateLimitExceededException');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test that Contest::apiCreate is rate limited to 10 per hour.
     */
    public function testContestCreateRateLimit(): void {
        ['identity' => $identity, 'user' => $user] = \OmegaUp\Test\Factories\User::createUser();

        // Create 10 contests with the same director — all should succeed
        for ($i = 0; $i < 10; $i++) {
            \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'alias' => "test-contest-rl-{$i}",
                    'title' => "Test Contest RL {$i}",
                    'contestDirector' => $identity,
                    'contestDirectorUser' => $user,
                ])
            );
        }

        // The 11th should fail
        $contestData = \OmegaUp\Test\Factories\Contest::getRequest(
            new \OmegaUp\Test\Factories\ContestParams([
                'alias' => 'test-contest-rl-excess',
                'title' => 'Test Contest RL Excess',
                'contestDirector' => $identity,
                'contestDirectorUser' => $user,
            ])
        );
        $login = self::login($identity);
        $contestData['request']['auth_token'] = $login->auth_token;

        try {
            \OmegaUp\Controllers\Contest::apiCreate(
                $contestData['request']
            );
            $this->fail('Expected RateLimitExceededException');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test that Problem::apiCreate is rate limited to 20 per hour.
     */
    public function testProblemCreateRateLimit(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create 20 problems — all should succeed
        for ($i = 0; $i < 20; $i++) {
            $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
            $r = $problemData['request'];
            $r['auth_token'] = $login->auth_token;
            $r['problem_alias'] = "test-prob-rl-{$i}";
            $r['title'] = "Test Problem RL {$i}";
            \OmegaUp\Controllers\Problem::apiCreate(
                new \OmegaUp\Request($r)
            );
        }

        // The 21st should fail
        try {
            $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
            $r = $problemData['request'];
            $r['auth_token'] = $login->auth_token;
            $r['problem_alias'] = 'test-prob-rl-excess';
            $r['title'] = 'Test Problem RL Excess';
            \OmegaUp\Controllers\Problem::apiCreate(
                new \OmegaUp\Request($r)
            );
            $this->fail('Expected RateLimitExceededException');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }
}
