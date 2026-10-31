<?php

/**
 * Tests for the follow/unfollow endpoints, the followers/following lists and
 * the follow status shown on a profile.
 */
class UserFollowTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A user can follow another user, and the relationship shows up on both
     * sides of the graph.
     */
    public function testFollowAndUnfollowUser() {
        ['identity' => $follower] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $followed] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($follower);

        $response = \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $followed->username,
        ]));
        $this->assertSame('ok', $response['status']);

        $following = \OmegaUp\Controllers\User::apiFollowing(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $follower->username,
            ])
        );
        $this->assertSame(1, $following['totalRows']);
        $this->assertCount(1, $following['following']);
        $this->assertSame(
            $followed->username,
            $following['following'][0]['username']
        );

        $followers = \OmegaUp\Controllers\User::apiFollowers(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ])
        );
        $this->assertSame(1, $followers['totalRows']);
        $this->assertSame(
            $follower->username,
            $followers['followers'][0]['username']
        );

        $response = \OmegaUp\Controllers\User::apiUnfollow(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ])
        );
        $this->assertSame('ok', $response['status']);

        $following = \OmegaUp\Controllers\User::apiFollowing(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $follower->username,
            ])
        );
        $this->assertSame(0, $following['totalRows']);
        $this->assertEmpty($following['following']);
    }

    /**
     * Following the same user twice is rejected by the unique key.
     */
    public function testFollowTwiceFails() {
        ['identity' => $follower] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $followed] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($follower);

        \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $followed->username,
        ]));

        try {
            \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ]));
            $this->fail('Should not have allowed following twice');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('userAlreadyFollowed', $e->getMessage());
        }

        $followers = \OmegaUp\Controllers\User::apiFollowers(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ])
        );
        $this->assertSame(1, $followers['totalRows']);
    }

    /**
     * A user cannot follow themselves.
     */
    public function testCannotFollowYourself() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
            ]));
            $this->fail('Should not have allowed following yourself');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('cannotFollowYourself', $e->getMessage());
        }
    }

    /**
     * Following an account that does not exist fails.
     */
    public function testFollowNonExistentUser() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => 'nonexistentuser',
            ]));
            $this->fail('Should not have allowed following a missing account');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('userNotExist', $e->getMessage());
        }
    }

    /**
     * Listing the followers of an account that does not exist fails.
     */
    public function testFollowersOfNonExistentUser() {
        try {
            \OmegaUp\Controllers\User::apiFollowers(new \OmegaUp\Request([
                'username' => 'nonexistentuser',
            ]));
            $this->fail('Should not have listed a missing account');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('userNotExist', $e->getMessage());
        }
    }

    /**
     * Following requires being logged in.
     */
    public function testFollowRequiresLogin() {
        ['identity' => $followed] = \OmegaUp\Test\Factories\User::createUser();

        try {
            \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
                'username' => $followed->username,
            ]));
            $this->fail('Should not have allowed an anonymous follow');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    /**
     * Unfollowing someone who is not followed succeeds and changes nothing.
     */
    public function testUnfollowUserThatIsNotFollowed() {
        ['identity' => $follower] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $followed] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($follower);

        $response = \OmegaUp\Controllers\User::apiUnfollow(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ])
        );

        $this->assertSame('ok', $response['status']);
        $followers = \OmegaUp\Controllers\User::apiFollowers(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ])
        );
        $this->assertSame(0, $followers['totalRows']);
    }

    /**
     * The follow status reports both counters and whether the current user
     * follows the profile being viewed.
     */
    public function testFollowStatusCountsAndFlag() {
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $profile] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $other] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($viewer);

        $status = \OmegaUp\Controllers\User::apiFollowStatus(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $profile->username,
            ])
        );
        $this->assertFalse($status['isFollowing']);
        $this->assertSame(0, $status['followersCount']);
        $this->assertSame(0, $status['followingCount']);

        \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $profile->username,
        ]));
        $otherLogin = self::login($other);
        \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
            'auth_token' => $otherLogin->auth_token,
            'username' => $profile->username,
        ]));
        \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $other->username,
        ]));

        $status = \OmegaUp\Controllers\User::apiFollowStatus(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $profile->username,
            ])
        );
        $this->assertTrue($status['isFollowing']);
        $this->assertSame(2, $status['followersCount']);
        $this->assertSame(0, $status['followingCount']);

        // The viewer follows two people and is followed by nobody.
        $status = \OmegaUp\Controllers\User::apiFollowStatus(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $viewer->username,
            ])
        );
        $this->assertFalse($status['isFollowing']);
        $this->assertSame(0, $status['followersCount']);
        $this->assertSame(2, $status['followingCount']);
    }

    /**
     * An unauthenticated request gets the counters, and isFollowing is false
     * rather than an error.
     */
    public function testFollowStatusForUnauthenticatedRequest() {
        ['identity' => $follower] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $profile] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($follower);
        \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $profile->username,
        ]));

        $status = \OmegaUp\Controllers\User::apiFollowStatus(
            new \OmegaUp\Request([
                'username' => $profile->username,
            ])
        );

        $this->assertFalse($status['isFollowing']);
        $this->assertSame(1, $status['followersCount']);
        $this->assertSame(0, $status['followingCount']);

        $status = \OmegaUp\Controllers\User::apiFollowStatus(
            new \OmegaUp\Request([
                'username' => $follower->username,
            ])
        );
        $this->assertFalse($status['isFollowing']);
        $this->assertSame(0, $status['followersCount']);
        $this->assertSame(1, $status['followingCount']);
    }

    /**
     * The followers list is paginated, and the total ignores pagination.
     */
    public function testFollowersListIsPaginated() {
        ['identity' => $followed] = \OmegaUp\Test\Factories\User::createUser();

        for ($i = 0; $i < 5; $i++) {
            [
                'identity' => $follower,
            ] = \OmegaUp\Test\Factories\User::createUser();
            $login = self::login($follower);
            \OmegaUp\Controllers\User::apiFollow(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $followed->username,
            ]));
        }

        $firstPage = \OmegaUp\Controllers\User::apiFollowers(
            new \OmegaUp\Request([
                'username' => $followed->username,
                'page' => 1,
                'rowcount' => 2,
            ])
        );
        $this->assertSame(5, $firstPage['totalRows']);
        $this->assertCount(2, $firstPage['followers']);

        $lastPage = \OmegaUp\Controllers\User::apiFollowers(
            new \OmegaUp\Request([
                'username' => $followed->username,
                'page' => 3,
                'rowcount' => 2,
            ])
        );
        $this->assertSame(5, $lastPage['totalRows']);
        $this->assertCount(1, $lastPage['followers']);

        // Pages must not overlap.
        $this->assertNotSame(
            $firstPage['followers'][0]['username'],
            $lastPage['followers'][0]['username']
        );

        $pastTheEnd = \OmegaUp\Controllers\User::apiFollowers(
            new \OmegaUp\Request([
                'username' => $followed->username,
                'page' => 10,
                'rowcount' => 2,
            ])
        );
        $this->assertSame(5, $pastTheEnd['totalRows']);
        $this->assertEmpty($pastTheEnd['followers']);
    }

    /**
     * A page size above the maximum is rejected.
     */
    public function testFollowersRejectsTooLargePageSize() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        try {
            \OmegaUp\Controllers\User::apiFollowers(new \OmegaUp\Request([
                'username' => $identity->username,
                'rowcount' => \OmegaUp\Controllers\User::MAX_FOLLOW_LIST_PAGE_SIZE + 1,
            ]));
            $this->fail('Should not have allowed an oversized page');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNumberTooLarge', $e->getMessage());
        }
    }
}
