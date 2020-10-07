<?php

/**
 * Tests the /api/user/validateFilter/ API.
 */
class UserFilterTest extends \OmegaUp\Test\ControllerTestCase {
    public function testInvalidFilter() {
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => 'invalid',
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    public function testUnauthorizedAccess() {
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => '/all-events',
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testInsufficientPrivileges() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => '/all-events',
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testAllEventsWithAdmin() {
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(true, $response['admin']);
    }

    public function testMyEvents() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/user/{$identity->username}",
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($identity->username, $response['user']);
        $this->assertEquals(false, $response['admin']);
        $this->assertEmpty($response['problem_admin']);
        $this->assertEmpty($response['contest_admin']);
        $this->assertEmpty($response['problemset_admin']);
    }

    public function testOtherUsersEvents() {
        ['user' => $user1, 'identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity1);
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/user/{$identity2->username}",
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testOtherUsersEventsWithAdmin() {
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/user/{$identity->username}",
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(true, $response['admin']);
    }

    public function testPublicProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => "/problemset/{$contest->problemset_id}",
        ]));
    }

    public function testPublicContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => "/contest/{$contest->alias}",
        ]));
    }

    public function testAnonymousPublicProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/problemset/{$contest->problemset_id}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertEquals('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousPublicContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/contest/{$contest->alias}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertEquals('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/problemset/{$contest->problemset_id}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertEquals('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/contest/{$contest->alias}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertEquals('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousProblemsetWithToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/problemset/{$problemset->problemset_id}/{$problemset->scoreboard_url}",
        ]));
        $this->assertEmpty($response['contest_admin']);
        $this->assertEmpty($response['problemset_admin']);
    }

    public function testAnonymousContestWithToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/contest/{$contest->alias}/{$problemset->scoreboard_url}",
        ]));
        $this->assertEmpty($response['contest_admin']);
        $this->assertEmpty($response['problemset_admin']);
    }

    public function testAnonymousProblemsetWithAdminToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/problemset/{$problemset->problemset_id}/{$problemset->scoreboard_url_admin}",
        ]));
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertContains(
            $problemset->problemset_id,
            $response['problemset_admin']
        );
        $this->assertNull($response['user']);
    }

    public function testAnonymousContestWithAdminToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/contest/{$contest->alias}/{$problemset->scoreboard_url_admin}",
        ]));
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertContains(
            $problemset->problemset_id,
            $response['problemset_admin']
        );
        $this->assertNull($response['user']);
    }

    public function testPublicProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/problem/{$problem->alias}",
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($identity->username, $response['user']);
    }

    public function testAnonymousPublicProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/problem/{$problem->alias}",
        ]));
        $this->assertNull($response['user']);
    }

    public function testAnonymousProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
        ]))['problem'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/problem/{$problem->alias}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('problemIsPrivate', $e->getMessage());
        }
    }
}
