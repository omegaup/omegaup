<?php
/**
 * Test for ProblemBookmarkController
 */
class ProblemBookmarkTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests toggle bookmark functionality
     */
    public function testToggleBookmark() {
        // Create a user and a problem
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Login and toggle bookmark (should create bookmark)
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertTrue($response['bookmarked']);

        // Verify bookmark was created by checking exists API
        $existsResponse = \OmegaUp\Controllers\ProblemBookmark::apiExists(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertTrue($existsResponse['bookmarked']);
    }

    /**
     * Tests toggle bookmark twice to remove bookmark
     */
    public function testToggleBookmarkTwice() {
        // Create a user and a problem
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Login and toggle bookmark (should create bookmark)
        $login = self::login($identity);
        $response1 = \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertTrue($response1['bookmarked']);

        // Toggle again - should remove bookmark
        $response2 = \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertFalse($response2['bookmarked']);

        // Verify bookmark was actually removed via apiExists
        $existsResponse = \OmegaUp\Controllers\ProblemBookmark::apiExists(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertFalse($existsResponse['bookmarked']);
    }

    /**
     * Tests list bookmarked problems
     */
    public function testListBookmarkedProblems() {
        // Create a user and multiple problems
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData1 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData3 = \OmegaUp\Test\Factories\Problem::createProblem();

        // Login and bookmark two problems
        $login = self::login($identity);
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData1['problem']->alias,
            ])
        );
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData2['problem']->alias,
            ])
        );

        // List bookmarked problems
        $response = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame(2, $response['total']);

        // Verify the problems are in the list
        $aliases = array_map(
            function ($problem) {
                return $problem['alias'];
            },
            $response['problems']
        );
        $this->assertContains($problemData1['problem']->alias, $aliases);
        $this->assertContains($problemData2['problem']->alias, $aliases);
        $this->assertNotContains($problemData3['problem']->alias, $aliases);
    }

    /**
     * Tests solved and attempted status in the bookmark list
     */
    public function testListBookmarkedProblemsSolveStatus() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $solvedProblem = \OmegaUp\Test\Factories\Problem::createProblem();
        $attemptedProblem = \OmegaUp\Test\Factories\Problem::createProblem();
        $pendingProblem = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);
        foreach (
            [
                $solvedProblem,
                $attemptedProblem,
                $pendingProblem,
            ] as $problemData
        ) {
            \OmegaUp\Controllers\ProblemBookmark::apiToggle(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => $problemData['problem']->alias,
                ])
            );
        }

        $acceptedRun = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $solvedProblem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acceptedRun);

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $rejectedRun = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $attemptedProblem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($rejectedRun, 0, 'WA');

        $response = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame(3, $response['total']);

        $problemsByAlias = [];
        foreach ($response['problems'] as $problem) {
            $problemsByAlias[$problem['alias']] = $problem;
        }

        $solvedAlias = strval($solvedProblem['problem']->alias);
        $this->assertTrue($problemsByAlias[$solvedAlias]['solved']);
        $this->assertTrue($problemsByAlias[$solvedAlias]['attempted']);

        $attemptedAlias = strval($attemptedProblem['problem']->alias);
        $this->assertFalse($problemsByAlias[$attemptedAlias]['solved']);
        $this->assertTrue($problemsByAlias[$attemptedAlias]['attempted']);

        $pendingAlias = strval($pendingProblem['problem']->alias);
        $this->assertFalse($problemsByAlias[$pendingAlias]['solved']);
        $this->assertFalse($problemsByAlias[$pendingAlias]['attempted']);
    }

    /**
     * Tests that bookmarks are user-specific
     */
    public function testBookmarkIsUserSpecific() {
        // Create two users and a problem
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // User1 bookmarks the problem
        $login1 = self::login($identity1);
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login1->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        // User2 should not see the bookmark
        $login2 = self::login($identity2);
        $existsResponse = \OmegaUp\Controllers\ProblemBookmark::apiExists(
            new \OmegaUp\Request([
                'auth_token' => $login2->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertFalse($existsResponse['bookmarked']);

        // User2's list should be empty
        $listResponse = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login2->auth_token,
            ])
        );

        $this->assertSame(0, $listResponse['total']);
    }

    /**
     * Tests toggle bookmark without login
     */
    public function testToggleBookmarkWithoutLogin() {
        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        try {
            \OmegaUp\Controllers\ProblemBookmark::apiToggle(
                new \OmegaUp\Request([
                    'problem_alias' => $problemData['problem']->alias,
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    /**
     * Tests exists bookmark without login
     */
    public function testExistsBookmarkWithoutLogin() {
        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        try {
            \OmegaUp\Controllers\ProblemBookmark::apiExists(
                new \OmegaUp\Request([
                    'problem_alias' => $problemData['problem']->alias,
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    /**
     * Tests list bookmarks without login
     */
    public function testListBookmarksWithoutLogin() {
        try {
            \OmegaUp\Controllers\ProblemBookmark::apiList(
                new \OmegaUp\Request([])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    /**
     * Tests toggle bookmark with invalid problem
     */
    public function testToggleBookmarkInvalidProblem() {
        // Create a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Login and try to bookmark non-existent problem
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\ProblemBookmark::apiToggle(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => 'nonexistent-problem-alias',
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('problemNotFound', $e->getMessage());
        }
    }

    /**
     * Tests exists bookmark with invalid problem
     */
    public function testExistsBookmarkInvalidProblem() {
        // Create a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Login and try to check bookmark for non-existent problem
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\ProblemBookmark::apiExists(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => 'nonexistent-problem-alias',
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('problemNotFound', $e->getMessage());
        }
    }

    /**
     * Tests bookmark cache invalidation in profile
     */
    public function testBookmarkCacheInvalidation() {
        // Create a user and a problem
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Login
        $login = self::login($identity);

        // Populate cache by getting profile details
        $profileResponse = \OmegaUp\Controllers\User::getProfileDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
            ])
        )['templateProperties']['payload'];

        $extraProfileDetails = $profileResponse['extraProfileDetails'];

        // Verify bookmark count is 0 initially
        $this->assertNotNull($extraProfileDetails);
        $this->assertCount(
            0,
            $extraProfileDetails['bookmarkedProblems']
        );

        // Create bookmark
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        // Get profile again - bookmark should be in the list
        $profileResponse2 = \OmegaUp\Controllers\User::getProfileDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
            ])
        )['templateProperties']['payload'];

        $extraProfileDetails2 = $profileResponse2['extraProfileDetails'];
        $this->assertNotNull($extraProfileDetails2);
        $this->assertCount(
            1,
            $extraProfileDetails2['bookmarkedProblems']
        );
        $this->assertSame(
            $problemData['problem']->alias,
            $extraProfileDetails2['bookmarkedProblems'][0]['alias']
        );

        // Delete bookmark
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        // Get profile again - bookmark should be removed
        $profileResponse3 = \OmegaUp\Controllers\User::getProfileDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
            ])
        )['templateProperties']['payload'];

        $extraProfileDetails3 = $profileResponse3['extraProfileDetails'];
        $this->assertNotNull($extraProfileDetails3);
        $this->assertCount(
            0,
            $extraProfileDetails3['bookmarkedProblems']
        );
    }

    /**
     * Tests that a bookmarked contest problem exposes practice mode URL once
     * the contest ends.
     */
    public function testBookmarkedContestProblemShowsPracticeLinkWhenContestEnds() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $startTime = \OmegaUp\Time::get() - 3600;
        $finishTime = \OmegaUp\Time::get() + 3600;
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
                'startTime' => new \OmegaUp\Timestamp($startTime),
                'finishTime' => new \OmegaUp\Timestamp($finishTime),
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        $login = self::login($identity);
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        // While contest is still running, practice URL should not be available
        $ongoingList = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertCount(1, $ongoingList['problems']);
        $this->assertNull($ongoingList['problems'][0]['practice_url']);

        // Advance time past contest finish_time
        $currentTime = \OmegaUp\Time::get();
        try {
            \OmegaUp\Time::setTimeForTesting($finishTime + 60);
            $endedList = \OmegaUp\Controllers\ProblemBookmark::apiList(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->assertCount(1, $endedList['problems']);
            $expectedPracticeUrl = (
                '/arena/' .
                strval($contestData['contest']->alias) .
                '/practice/#problems/' .
                strval($problemData['problem']->alias)
            );
            $this->assertSame(
                $expectedPracticeUrl,
                $endedList['problems'][0]['practice_url']
            );
            $this->assertSame(
                $contestData['contest']->alias,
                $endedList['problems'][0]['contest_alias']
            );
            $this->assertSame(
                $contestData['contest']->title,
                $endedList['problems'][0]['contest_title']
            );
        } finally {
            \OmegaUp\Time::setTimeForTesting($currentTime);
        }
    }

    /**
     * Tests that a private contest practice mode is not exposed to
     * unauthorized users.
     */
    public function testPrivateContestPracticeNotExposedToUnauthorizedUsers() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $startTime = \OmegaUp\Time::get() - 7200;
        $finishTime = \OmegaUp\Time::get() - 3600;
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'startTime' => new \OmegaUp\Timestamp($startTime),
                'finishTime' => new \OmegaUp\Timestamp($finishTime),
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        $login = self::login($identity);
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        // Contest is private and ended, but user is not registered/enrolled
        $unauthorizedList = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertCount(1, $unauthorizedList['problems']);
        $this->assertNull($unauthorizedList['problems'][0]['practice_url']);

        // Enroll user in contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $authorizedList = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertCount(1, $authorizedList['problems']);
        $expectedPracticeUrl = (
            '/arena/' .
            strval($contestData['contest']->alias) .
            '/practice/#problems/' .
            strval($problemData['problem']->alias)
        );
        $this->assertSame(
            $expectedPracticeUrl,
            $authorizedList['problems'][0]['practice_url']
        );
    }

    /**
     * Tests that private problems are completely hidden unless user has
     * practice access.
     */
    public function testPrivateProblemAccessRouting() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            ])
        );

        $startTime = \OmegaUp\Time::get() - 3600;
        $finishTime = \OmegaUp\Time::get() + 3600;
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'startTime' => new \OmegaUp\Timestamp($startTime),
                'finishTime' => new \OmegaUp\Timestamp($finishTime),
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // Bookmark problem directly via DAO (simulating contest bookmark)
        \OmegaUp\DAO\ProblemBookmarks::create(
            new \OmegaUp\DAO\VO\ProblemBookmarks([
                'identity_id' => $identity->identity_id,
                'problem_id' => $problemData['problem']->problem_id,
            ])
        );

        $login = self::login($identity);

        // While contest is ongoing, private problem is not visible in bookmarks
        $ongoingList = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertCount(0, $ongoingList['problems']);

        // Advance time past finish_time: practice mode becomes accessible
        $currentTime = \OmegaUp\Time::get();
        try {
            \OmegaUp\Time::setTimeForTesting($finishTime + 60);
            $endedList = \OmegaUp\Controllers\ProblemBookmark::apiList(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->assertCount(1, $endedList['problems']);
            $this->assertTrue($endedList['problems'][0]['is_private']);
            $expectedPracticeUrl = (
                '/arena/' .
                strval($contestData['contest']->alias) .
                '/practice/#problems/' .
                strval($problemData['problem']->alias)
            );
            $this->assertSame(
                $expectedPracticeUrl,
                $endedList['problems'][0]['practice_url']
            );
        } finally {
            \OmegaUp\Time::setTimeForTesting($currentTime);
        }
    }

    /**
     * Tests tie-breaking when a problem belongs to multiple ended contests,
     * prioritizing user participation.
     */
    public function testMultipleContestsPrioritizesUserContestAndEndedPractice() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Contest 1 ended 1 hour ago (more recent)
        $contest1 = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
                'startTime' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() - 7200
                ),
                'finishTime' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() - 3600
                ),
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contest1
        );

        // Contest 2 ended 3 hours ago (older), but user participated / enrolled
        $contest2 = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
                'startTime' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() - 14400
                ),
                'finishTime' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() - 10800
                ),
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contest2
        );
        \OmegaUp\Test\Factories\Contest::addUser($contest2, $identity);

        $login = self::login($identity);
        \OmegaUp\Controllers\ProblemBookmark::apiToggle(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $list = \OmegaUp\Controllers\ProblemBookmark::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertCount(1, $list['problems']);
        // Contest 2 should be chosen because user participated in it
        $this->assertSame(
            $contest2['contest']->alias,
            $list['problems'][0]['contest_alias']
        );
        $expectedPracticeUrl = (
            '/arena/' .
            strval($contest2['contest']->alias) .
            '/practice/#problems/' .
            strval($problemData['problem']->alias)
        );
        $this->assertSame(
            $expectedPracticeUrl,
            $list['problems'][0]['practice_url']
        );
    }
}
