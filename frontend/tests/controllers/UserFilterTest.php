<?php

/**
 * Tests the /api/user/validateFilter/ API.
 */
class UserFilterTest extends OmegaupTestCase {
    /**
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testInvalidFilter() {
        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => 'invalid',
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testUnauthorizedAccess() {
        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testInsufficientPrivileges() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
    }

    public function testAllEventsWithAdmin() {
        $admin = UserFactory::createAdminUser();

        $login = self::login($admin);
        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/all-events',
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['admin'], true);
    }

    public function testMyEvents() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
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
        $user1 = UserFactory::createUser();
        $user2 = UserFactory::createUser();

        $login = self::login($user1);
        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/user/' . $user2->username,
            'auth_token' => $login->auth_token,
        ]));
    }

    public function testOtherUsersEventsWithAdmin() {
        $admin = UserFactory::createAdminUser();
        $user = UserFactory::createUser();

        $login = self::login($admin);
        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/user/' . $user->username,
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['admin'], true);
    }

    public function testPublicProblemsetAccess() {
        $contest = ContestsFactory::createContest()['contest'];
        $user = UserFactory::createUser();

        $login = self::login($user);
        UserController::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    public function testPublicContestAccess() {
        $contest = ContestsFactory::createContest()['contest'];
        $user = UserFactory::createUser();

        $login = self::login($user);
        UserController::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousPublicProblemsetAccess() {
        $contest = ContestsFactory::createContest()['contest'];

        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousPublicContestAccess() {
        $contest = ContestsFactory::createContest()['contest'];

        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousProblemsetAccess() {
        $contest = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']))['contest'];

        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    /**
     * @expectedException \OmegaUp\Exceptions\UnauthorizedException
     */
    public function testAnonymousContestAccess() {
        $contest = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']))['contest'];

        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id,
        ]));
    }

    public function testAnonymousProblemsetWithToken() {
        $contest = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']))['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK($contest->problemset_id);

        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $contest->problemset_id . '/' .
                        $problemset->scoreboard_url,
        ]));
        $this->assertEmpty($response['contest_admin']);
    }

    public function testAnonymousContestWithToken() {
        $contest = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']))['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK($contest->problemset_id);

        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $problemset->problemset_id . '/' .
                        $problemset->scoreboard_url,
        ]));
        $this->assertEmpty($response['contest_admin']);
    }

    public function testAnonymousProblemsetWithAdminToken() {
        $contest = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']))['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK($contest->problemset_id);

        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $problemset->problemset_id . '/' .
                        $problemset->scoreboard_url_admin,
        ]));
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertNull($response['user']);
    }

    public function testAnonymousContestWithAdminToken() {
        $contest = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']))['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK($contest->problemset_id);

        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problemset/' . $problemset->problemset_id . '/' .
                        $problemset->scoreboard_url_admin,
        ]));
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertNull($response['user']);
    }

    public function testPublicProblemAccess() {
        $problem = ProblemsFactory::createProblem()['problem'];
        $user = UserFactory::createUser();

        $login = self::login($user);
        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['user'], $user->username);
    }

    public function testAnonymousPublicProblemAccess() {
        $problem = ProblemsFactory::createProblem()['problem'];

        $response = UserController::apiValidateFilter(new \OmegaUp\Request([
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

        UserController::apiValidateFilter(new \OmegaUp\Request([
            'filter' => '/problem/' . $problem->alias,
        ]));
    }
}
