<?php

/**
 * Tests the /api/user/validateFilter/ API.
 */
class UserFilterTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testInvalidFilter() {
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => 'invalid',
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testUnauthorizedAccess() {
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testInsufficientPrivileges() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
    }

    public function testAllEventsWithAdmin() {
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['admin'], true);
    }

    public function testMyEvents() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/user/{$identity->username}",
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['status'], 'ok');
        $this->assertEquals($response['user'], $identity->username);
        $this->assertEquals($response['admin'], false);
        $this->assertEmpty($response['problem_admin']);
        $this->assertEmpty($response['contest_admin']);
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testOtherUsersEvents() {
        ['user' => $user1, 'identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity1);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/user/{$identity2->username}",
            'auth_token' => $login->auth_token,
        ]));
    }

    public function testOtherUsersEventsWithAdmin() {
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => "/user/{$identity->username}",
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['admin'], true);
    }

    public function testPublicProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    public function testPublicContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousPublicProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousPublicContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
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
            'filter' => '/problemset/' . $contest->problemset_id . '/' .
                        $problemset->scoreboard_url,
        ]));
        $this->assertEmpty($response['contest_admin']);
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
            'filter' => '/problemset/' . $problemset->problemset_id . '/' .
                        $problemset->scoreboard_url,
        ]));
        $this->assertEmpty($response['contest_admin']);
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
            'filter' => '/problemset/' . $problemset->problemset_id . '/' .
                        $problemset->scoreboard_url_admin,
        ]));
        $this->assertContains($contest->alias, $response['contest_admin']);
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
            'filter' => '/problemset/' . $problemset->problemset_id . '/' .
                        $problemset->scoreboard_url_admin,
        ]));
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertNull($response['user']);
    }

    public function testPublicProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['user'], $identity->username);
    }

    public function testAnonymousPublicProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
        ]));
        $this->assertNull($response['user']);
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testAnonymousProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 0
        ]))['problem'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
        ]));
    }
}
