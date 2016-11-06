<?php

/**
 * Tests the /api/user/validateFilter/ API.
 */
class UserFilterTest extends OmegaupTestCase {
    /**
     * @expectedException InvalidParameterException
     */
    public function testInvalidFilter() {
        $r = new Request(array(
            'filter' => 'invalid',
        ));
        UserController::apiValidateFilter($r);
    }

    /**
     * @expectedException ForbiddenAccessException
     */
    public function testUnauthorizedAccess() {
        $r = new Request(array(
            'filter' => '/all-events',
        ));
        UserController::apiValidateFilter($r);
    }

    /**
     * @expectedException ForbiddenAccessException
     */
    public function testInsufficientPrivileges() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ));
        UserController::apiValidateFilter($r);
    }

    public function testAllEventsWithAdmin() {
        $admin = UserFactory::createAdminUser();

        $login = self::login($admin);
        $r = new Request(array(
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertEquals($response['admin'], true);
    }

    public function testMyEvents() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'filter' => '/user/' . $user->username,
            'auth_token' => $login->auth_token,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertEquals($response['status'], 'ok');
        $this->assertEquals($response['user'], $user->username);
        $this->assertEquals($response['admin'], false);
        $this->assertEmpty($response['problem_admin']);
        $this->assertEmpty($response['contest_admin']);
    }

    /**
     * @expectedException ForbiddenAccessException
     */
    public function testOtherUsersEvents() {
        $user1 = UserFactory::createUser();
        $user2 = UserFactory::createUser();

        $login = self::login($user1);
        $r = new Request(array(
            'filter' => '/user/' . $user2->username,
            'auth_token' => $login->auth_token,
        ));
        UserController::apiValidateFilter($r);
    }

    public function testOtherUsersEventsWithAdmin() {
        $admin = UserFactory::createAdminUser();
        $user = UserFactory::createUser();

        $login = self::login($admin);
        $r = new Request(array(
            'filter' => '/user/' . $user->username,
            'auth_token' => $login->auth_token,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertEquals($response['admin'], true);
    }

    public function testPublicContestAccess() {
        $contest = ContestsFactory::createContest()['contest'];
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'filter' => '/contest/' . $contest->alias,
        ));
        UserController::apiValidateFilter($r);
    }

    /**
     * @expectedException UnauthorizedException
     */
    public function testAnonymousPublicContestAccess() {
        $contest = ContestsFactory::createContest()['contest'];

        $r = new Request(array(
            'filter' => '/contest/' . $contest->alias,
        ));
        UserController::apiValidateFilter($r);
    }

    /**
     * @expectedException UnauthorizedException
     */
    public function testAnonymousContestAccess() {
        $contest = ContestsFactory::createContest(null, 0)['contest'];

        $r = new Request(array(
            'filter' => '/contest/' . $contest->alias,
        ));
        UserController::apiValidateFilter($r);
    }

    public function testAnonymousContestWithToken() {
        $contest = ContestsFactory::createContest(null, 0)['contest'];

        $r = new Request(array(
            'filter' => '/contest/' . $contest->alias . '/' .
                        $contest->scoreboard_url,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertEmpty($response['contest_admin']);
    }

    public function testAnonymousContestWithAdminToken() {
        $contest = ContestsFactory::createContest(null, 0)['contest'];

        $r = new Request(array(
            'filter' => '/contest/' . $contest->alias . '/' .
                        $contest->scoreboard_url_admin,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertNull($response['user']);
    }

    public function testPublicProblemAccess() {
        $problem = ProblemsFactory::createProblem()['problem'];
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'filter' => '/problem/' . $problem->alias,
            'auth_token' => $login->auth_token,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertEquals($response['user'], $user->username);
    }

    public function testAnonymousPublicProblemAccess() {
        $problem = ProblemsFactory::createProblem()['problem'];

        $r = new Request(array(
            'filter' => '/problem/' . $problem->alias,
        ));
        $response = UserController::apiValidateFilter($r);
        $this->assertNull($response['user']);
    }

    /**
     * @expectedException ForbiddenAccessException
     */
    public function testAnonymousProblemAccess() {
        $problem = ProblemsFactory::createProblem(null, null, 0)['problem'];

        $r = new Request(array(
            'filter' => '/problem/' . $problem->alias,
        ));
        UserController::apiValidateFilter($r);
    }
}
