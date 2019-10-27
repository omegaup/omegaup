<?php

/**
 * Tests the /api/user/validateFilter/ API.
 */
class UserFilterTest extends OmegaupTestCase {
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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
    }

    public function testAllEventsWithAdmin() {
        ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['admin'], true);
    }

    public function testMyEvents() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/user/' . $user->username,
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['status'], 'ok');
        $this->assertEquals($response['user'], $user->username);
        $this->assertEquals($response['admin'], false);
        $this->assertEmpty($response['problem_admin']);
        $this->assertEmpty($response['contest_admin']);
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testOtherUsersEvents() {
        ['user' => $user1, 'identity' => $identity1] = UserFactory::createUser();
        ['user' => $user2, 'identity' => $identity2] = UserFactory::createUser();

        $login = self::login($identity1);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/user/' . $user2->username,
            'auth_token' => $login->auth_token,
        ]));
    }

    public function testOtherUsersEventsWithAdmin() {
        ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/user/' . $user->username,
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['admin'], true);
    }

    public function testPublicProblemsetAccess() {
        $contest = ContestsFactory::createContest()['contest'];
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    public function testPublicContestAccess() {
        $contest = ContestsFactory::createContest()['contest'];
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

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
        $contest = ContestsFactory::createContest()['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousPublicContestAccess() {
        $contest = ContestsFactory::createContest()['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousProblemsetAccess() {
        $contest = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
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
        $contest = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        )['contest'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    public function testAnonymousProblemsetWithToken() {
        $contest = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
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
        $contest = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
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
        $contest = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
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
        $contest = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
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
        $problem = ProblemsFactory::createProblem()['problem'];
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['user'], $user->username);
    }

    public function testAnonymousPublicProblemAccess() {
        $problem = ProblemsFactory::createProblem()['problem'];

        $response = \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
        ]));
        $this->assertNull($response['user']);
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testAnonymousProblemAccess() {
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]))['problem'];

        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
        ]));
    }
}
