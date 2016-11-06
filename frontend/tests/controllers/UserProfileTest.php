<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class UserProfileTest extends OmegaupTestCase {
    /*
	 * Test for the function which returns the general user info
	 */
    public function testUserData() {
        $user = UserFactory::createUser('testuser1');

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = UserController::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response['userinfo']);
        $this->assertEquals($user->username, $response['userinfo']['username']);
    }

    /*
	 * Test for the function which returns the general user info
	 */
    public function testUserDataAnotherUser() {
        $user = UserFactory::createUser('testuser3');
        $user2 = UserFactory::createUser('testuser4');

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'username' => $user2->username
        ));
        $response = UserController::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response['userinfo']);
        $this->assertArrayNotHasKey('email', $response['userinfo']);
        $this->assertEquals($user2->username, $response['userinfo']['username']);
    }

    /*
     * Test admin can see emails for all profiles
     */
    public function testAdminCanSeeEmails() {
        $user = UserFactory::createUser();
        $admin = UserFactory::createAdminUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'username' => $user->username
        ));
        $response = UserController::apiProfile($r);

        $this->assertArrayHasKey('email', $response['userinfo']);
    }

    /*
     * User can see his own email
     */
    public function testUserCanSeeSelfEmail() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'username' => $user->username
        ));
        $response = UserController::apiProfile($r);

        $this->assertArrayHasKey('email', $response['userinfo']);
    }

    /*
	 * Test the contest which a certain user has participated
	 */
    public function testUserContests() {
        $contestant = UserFactory::createUser();

        $contests = array();
        $contests[0] = ContestsFactory::createContest();
        $contests[1] = ContestsFactory::createContest();

        ContestsFactory::addUser($contests[0], $contestant);
        ContestsFactory::addUser($contests[1], $contestant);

        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contests[0]);

        $runData = RunsFactory::createRun($problemData, $contests[0], $contestant);
        RunsFactory::gradeRun($runData);

        // Get ContestStats
        $login = self::login($contestant);
        $response = UserController::apiContestStats(new Request(
            array(
                    'auth_token' => $login->auth_token,
                )
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
    }

    /*
	 * Test the contest which a certain user has participated.
	 * API can be accessed by a user who cannot see the contest (contest is private)
	 */
    public function testUserContestsPrivateContestOutsider() {
        $contestant = UserFactory::createUser();

        $contests = array();
        $contests[0] = ContestsFactory::createContest(null /*title*/, 0 /*public*/);
        $contests[1] = ContestsFactory::createContest();

        ContestsFactory::addUser($contests[0], $contestant);
        ContestsFactory::addUser($contests[1], $contestant);

        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contests[0]);

        $runData = RunsFactory::createRun($problemData, $contests[0], $contestant);
        RunsFactory::gradeRun($runData);

        $externalUser = UserFactory::createUser();

        $login = self::login($externalUser);
        // Get ContestStats
        $response = UserController::apiContestStats(new Request(
            array(
                    'auth_token' => $login->auth_token,
                    'username' => $contestant->username
                )
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
    }

    /*
	 * Test the problems solved by user
	 */
    public function testProblemsSolved() {
        $user = UserFactory::createUser();

        $contest = ContestsFactory::createContest();

        $problemOne = ProblemsFactory::createProblem();
        $problemTwo = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemOne, $contest);
        ContestsFactory::addProblemToContest($problemTwo, $contest);

        ContestsFactory::addUser($contest, $user);

        $runs = array();
        $runs[0] = RunsFactory::createRun($problemOne, $contest, $user);
        $runs[1] = RunsFactory::createRun($problemTwo, $contest, $user);
        $runs[2] = RunsFactory::createRun($problemOne, $contest, $user);

        RunsFactory::gradeRun($runs[0]);
        RunsFactory::gradeRun($runs[1]);
        RunsFactory::gradeRun($runs[2]);

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));

        $response = UserController::apiProblemsSolved($r);

        $this->assertEquals(2, count($response['problems']));
    }

    /**
     * Test update main email api
     */
    public function testUpdateMainEmail() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'email' => 'new@email.com'
        ));
        $response = UserController::apiUpdateMainEmail($r);

        // Check email in db
        $user_in_db = UsersDAO::FindByEmail('new@email.com');
        $this->assertEquals($user->user_id, $user_in_db->user_id);
    }
}
