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
}
